<?php

namespace App\Controller\Shop;

use DateTime;
use App\Entity\AboutStore;
use App\Entity\Order\Order;
use Webmozart\Assert\Assert;
use App\Entity\User\ShopUser;
use FOS\RestBundle\View\View;
use App\Entity\Customer\Customer;
use App\Entity\Addressing\Address;
use App\Entity\Shipping\ShippingMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Resource\ResourceActions;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sylius\Bundle\OrderBundle\Controller\OrderController;
use Sylius\Component\Resource\Exception\UpdateHandlingException;

class OrderExtendedController extends OrderController
{
    public function updateAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        /** @var AboutStore $aboutStore */
        $aboutStore = $this->getDoctrine()->getRepository('App:AboutStore')->findLatest();
        $deliveryHours = $aboutStore->getDeliveryHours();

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);

        /** @var Order $resource */
        $resource = $this->findOr404($configuration);

        /** @var ShopUser $user */
        $user = $this->getUser();

        /** @var Customer $customer */
        $customer = $user->getCustomer();

        /* Add estimated delivery date to the order */
        if ($resource->getPreferredDeliveryTime()) {
            $preferredTime = $resource->getPreferredDeliveryTime();
        } else {
            $preferredTime = $this->get('translator')->trans('app.ui.checkout.order.preferred_time.none');
        }

        if ($resource->getScheduledDeliveryDate()) {
            $scheduled = $resource->getScheduledDeliveryDate()->format('Y-m-d');
        } else {
            $scheduled = '';
        }

        $estimated = $this->get('app.service.order')->getNextAvailableDay($preferredTime, $scheduled);
        $resource->setEstimatedDeliveryDate(New DateTime($estimated));

        /* Add customer to order if this doesn't have */
        if (!$resource->getCustomer()) {
            $resource->setCustomer($customer);
        }

        $em->flush();
        $form = $this->resourceFormFactory->create($configuration, $resource);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $addressId = $request->request->get('sylius_checkout_address')['addressId'];
            if ($addressId) {
                $address = $this->getDoctrine()->getRepository('App:Addressing\Address')->find($addressId);
            } else {
                $address = null;
            }

            if ($request->request->get('sylius_checkout_address')) {
                $scheduledDate = $request->request->get('sylius_checkout_address')['scheduledDate'];
                $preferredTime = $request->request->get('sylius_checkout_address')['preferredTime'];

                if ($scheduledDate) {
                    $formatted = DateTime::createFromFormat('d/m/Y', $scheduledDate);
                    $date = $formatted->format('Y-m-d');

                    $resource->setScheduledDeliveryDate(New DateTime($date));
                } else {
                    $resource->setScheduledDeliveryDate(null);
                }

                if ($preferredTime) {
                    if ($preferredTime >= 1) {
                        if ($preferredTime == 3) {
                            $text = $deliveryHours[2]['name'] ? $deliveryHours[2]['name'] : $this->get('translator')->trans('app.ui.checkout.order.preferred_time.third.short');
                        } else if ($preferredTime == 2) {
                            $text = $deliveryHours[1]['name'] ? $deliveryHours[1]['name'] : $this->get('translator')->trans('app.ui.checkout.order.preferred_time.second.short');
                        } else {
                            $text = $deliveryHours[0]['name'] ? $deliveryHours[0]['name'] : $this->get('translator')->trans('app.ui.checkout.order.preferred_time.first.short');
                        }

                        $resource->setPreferredDeliveryTime($text);
                    } else {
                        $resource->setPreferredDeliveryTime($this->get('translator')->trans('app.ui.checkout.order.preferred_time.none'));
                    }
                } else {
                    $resource->setPreferredDeliveryTime($this->get('translator')->trans('app.ui.checkout.order.preferred_time.none'));
                }
            }

            /** @var Address $shippingAddress */
            $shippingAddress = $resource->getShippingAddress();
            $shippingAddress->setType(Address::TYPE_SHIPPING);
            $shippingAddress->setTaxId('CF');
            $shippingAddress->setFirstName($shippingAddress->getAnnotations());

            /** @var Address $billingAddress */
            $billingAddress = $resource->getBillingAddress();
            $billingAddress->setType(Address::TYPE_BILLING);
            $billingAddress->setFullAddress(null);

            /* Clone shipping address to customer if the limit is not reached yet */
            if (!$addressId && count($this->getShippingAddresses($customer)) < ShopUser::SHIPPING_ADDRESS_LIMIT) {
                $customerShippingAddress = new Address();
                $customerShippingAddress->setAnnotations($shippingAddress->getAnnotations());
                $customerShippingAddress->setFullAddress($shippingAddress->getFullAddress());
                $customerShippingAddress->setPhoneNumber($shippingAddress->getPhoneNumber());
                $customerShippingAddress->setCustomer($customer);
                $customerShippingAddress->setType(Address::TYPE_SHIPPING);
                $customerShippingAddress->setStatus(Address::STATUS_PENDING);

                $em->persist($customerShippingAddress);
                $address = $customerShippingAddress;

                /* If customer doesn't have default shipping address add the cloned address */
                if (!$customer->getDefaultAddress()) {
                    $customer->setDefaultAddress($customerShippingAddress);
                }
            }

            /* Clone billing address to customer if this doesn't have */
            if (!$customer->getDefaultBillingAddress()) {
                $customerBillingAddress = new Address();
                $customerBillingAddress->setFirstName($billingAddress->getFirstName());
                $customerBillingAddress->setFullAddress($billingAddress->getFullAddress());
                $customerBillingAddress->setTaxId($billingAddress->getTaxId());
                $customerBillingAddress->setCustomer($customer);
                $customerBillingAddress->setType(Address::TYPE_BILLING);
                $customerBillingAddress->setStatus(Address::STATUS_VALIDATED);

                $em->persist($customerBillingAddress);

                $customer->setDefaultBillingAddress($customerBillingAddress);
            }

            /* Set parent to cloned address */
            if ($address instanceof Address) {
                $shippingAddress->setParent($address);
            }

            $em->flush();

            $resource = $form->getData();
            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                $eventResponse = $event->getResponse();
                if (null !== $eventResponse) {
                    return $eventResponse;
                }

                return $this->redirectHandler->redirectToResource($configuration, $resource);
            }

            try {
                $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
            } catch (UpdateHandlingException $exception) {
                if (!$configuration->isHtmlRequest()) {
                    return $this->viewHandler->handle(
                        $configuration,
                        View::create($form, $exception->getApiResponseCode())
                    );
                }

                $this->flashHelper->addErrorFlash($configuration, $exception->getFlash());

                return $this->redirectHandler->redirectToReferer($configuration);
            }

            if ($configuration->isHtmlRequest()) {
                $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);
            }

            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            if (!$configuration->isHtmlRequest()) {
                $view = $configuration->getParameters()->get('return_content', false) ? View::create($resource, Response::HTTP_OK) : View::create(null, Response::HTTP_NO_CONTENT);

                return $this->viewHandler->handle($configuration, $view);
            }

            $postEventResponse = $postEvent->getResponse();

            if (null !== $postEventResponse) {
                return $postEventResponse;
            }

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);
        $initializeEventResponse = $initializeEvent->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::UPDATE . '.html'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    public function billingAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);

        /** @var Order $resource */
        $resource = $this->findOr404($configuration);
        $form = $this->resourceFormFactory->create($configuration, $resource);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $resource = $form->getData();
            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

            /* Add shipment to order */
            if (count($resource->getShipments()) <= 0) {
                /** @var ShipmentInterface $shipment */
                $shipment = $this->container->get('sylius.factory.shipment')->createNew();
                $shipment->setMethod($this->container->get('sylius.repository.shipping_method')->findOneBy(['code' => ShippingMethod::DEFAULT_SHIPPING_METHOD]));
                $resource->addShipment($shipment);
                $this->container->get('sylius.order_processing.order_processor')->process($resource);
                $this->container->get('sylius.manager.order')->flush();
            }

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                $eventResponse = $event->getResponse();
                if (null !== $eventResponse) {
                    return $eventResponse;
                }

                return $this->redirectHandler->redirectToResource($configuration, $resource);
            }

            try {
                $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
            } catch (UpdateHandlingException $exception) {
                if (!$configuration->isHtmlRequest()) {
                    return $this->viewHandler->handle(
                        $configuration,
                        View::create($form, $exception->getApiResponseCode())
                    );
                }

                $this->flashHelper->addErrorFlash($configuration, $exception->getFlash());

                return $this->redirectHandler->redirectToReferer($configuration);
            }

            if ($configuration->isHtmlRequest()) {
                $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);
            }

            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            if (!$configuration->isHtmlRequest()) {
                $view = $configuration->getParameters()->get('return_content', false) ? View::create($resource, Response::HTTP_OK) : View::create(null, Response::HTTP_NO_CONTENT);

                return $this->viewHandler->handle($configuration, $view);
            }

            $postEventResponse = $postEvent->getResponse();

            if (null !== $postEventResponse) {
                return $postEventResponse;
            }

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);
        $initializeEventResponse = $initializeEvent->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::UPDATE . '.html'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    public function paymentAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);

        /** @var Order $resource */
        $resource = $this->findOr404($configuration);
        $form = $this->resourceFormFactory->create($configuration, $resource);

        $paymentType = $request->request->get('payment_type');
        $cardType = $paymentType == 'card';
        $this->get('session')->set('payment', $paymentType);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            if ($paymentType) {
                if ($cardType) {
                    if ($form->handleRequest($request)->isValid()) {
                        $resource = $form->getData();
                        $card = $request->request->get('payment_card_checkout');

                        $this->get('session')->set('card', $card);
                    }
                }

                $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

                if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                    throw new HttpException($event->getErrorCode(), $event->getMessage());
                }

                if ($event->isStopped()) {
                    $this->flashHelper->addFlashFromEvent($configuration, $event);

                    $eventResponse = $event->getResponse();
                    if (null !== $eventResponse) {
                        return $eventResponse;
                    }

                    return $this->redirectHandler->redirectToResource($configuration, $resource);
                }

                try {
                    $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
                } catch (UpdateHandlingException $exception) {
                    if (!$configuration->isHtmlRequest()) {
                        return $this->viewHandler->handle(
                            $configuration,
                            View::create($form, $exception->getApiResponseCode())
                        );
                    }

                    $this->flashHelper->addErrorFlash($configuration, $exception->getFlash());

                    return $this->redirectHandler->redirectToReferer($configuration);
                }

                if ($configuration->isHtmlRequest()) {
                    $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);
                }

                $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

                if (!$configuration->isHtmlRequest()) {
                    $view = $configuration->getParameters()->get('return_content', false) ? View::create($resource, Response::HTTP_OK) : View::create(null, Response::HTTP_NO_CONTENT);

                    return $this->viewHandler->handle($configuration, $view);
                }

                $postEventResponse = $postEvent->getResponse();

                if (null !== $postEventResponse) {
                    return $postEventResponse;
                }
            }

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);
        $initializeEventResponse = $initializeEvent->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::UPDATE . '.html'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    public function completeAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);

        $this->isGrantedOr403($configuration, ResourceActions::UPDATE);

        /** @var Order $resource */
        $resource = $this->findOr404($configuration);

        $form = $this->resourceFormFactory->create($configuration, $resource);

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true) && $form->handleRequest($request)->isValid()) {
            $resource = $form->getData();
            $event = $this->eventDispatcher->dispatchPreEvent(ResourceActions::UPDATE, $configuration, $resource);

            if ($event->isStopped() && !$configuration->isHtmlRequest()) {
                throw new HttpException($event->getErrorCode(), $event->getMessage());
            }
            if ($event->isStopped()) {
                $this->flashHelper->addFlashFromEvent($configuration, $event);

                $eventResponse = $event->getResponse();
                if (null !== $eventResponse) {
                    return $eventResponse;
                }

                return $this->redirectHandler->redirectToResource($configuration, $resource);
            }

            try {
                $this->resourceUpdateHandler->handle($resource, $configuration, $this->manager);
            } catch (UpdateHandlingException $exception) {
                if (!$configuration->isHtmlRequest()) {
                    return $this->viewHandler->handle(
                        $configuration,
                        View::create($form, $exception->getApiResponseCode())
                    );
                }

                $this->flashHelper->addErrorFlash($configuration, $exception->getFlash());

                return $this->redirectHandler->redirectToReferer($configuration);
            }

            if ($configuration->isHtmlRequest()) {
                $this->flashHelper->addSuccessFlash($configuration, ResourceActions::UPDATE, $resource);
            }

            $postEvent = $this->eventDispatcher->dispatchPostEvent(ResourceActions::UPDATE, $configuration, $resource);

            if (!$configuration->isHtmlRequest()) {
                $view = $configuration->getParameters()->get('return_content', false) ? View::create($resource, Response::HTTP_OK) : View::create(null, Response::HTTP_NO_CONTENT);

                return $this->viewHandler->handle($configuration, $view);
            }

            $postEventResponse = $postEvent->getResponse();

            if (null !== $postEventResponse) {
                return $postEventResponse;
            }

            return $this->redirectHandler->redirectToResource($configuration, $resource);
        }

        if (!$configuration->isHtmlRequest()) {
            return $this->viewHandler->handle($configuration, View::create($form, Response::HTTP_BAD_REQUEST));
        }

        $initializeEvent = $this->eventDispatcher->dispatchInitializeEvent(ResourceActions::UPDATE, $configuration, $resource);
        $initializeEventResponse = $initializeEvent->getResponse();
        if (null !== $initializeEventResponse) {
            return $initializeEventResponse;
        }

        $view = View::create()
            ->setData([
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $resource,
                $this->metadata->getName() => $resource,
                'form' => $form->createView(),
            ])
            ->setTemplate($configuration->getTemplate(ResourceActions::UPDATE . '.html'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    public function thankYouAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $orderId = $request->getSession()->get('sylius_order_id', null);

        if (null === $orderId) {
            $options = $configuration->getParameters()->get('after_failure');

            return $this->redirectHandler->redirectToRoute(
                $configuration,
                $options['route'] ?? 'sylius_shop_homepage',
                $options['parameters'] ?? []
            );
        }

        $request->getSession()->remove('sylius_order_id');
        $order = $this->repository->find($orderId);
        Assert::notNull($order);

        $view = View::create()
            ->setData([
                'order' => $order,
            ])
            ->setTemplate($configuration->getParameters()->get('template'))
        ;

        return $this->viewHandler->handle($configuration, $view);
    }

    /**
     * @param Customer $customer
     * @return array
     */
    private function getShippingAddresses($customer) {
        $addresses = $customer->getAddresses();
        $default = $customer->getDefaultAddress();
        $new = [];

        if ($default) {
            $new[] = $default;
        }

        foreach ($addresses as $address ) {
            if ($default) {
                if ($default->getId() != $address->getId()) {
                    if ($address->getType() == Address::TYPE_SHIPPING) {
                        $new[] = $address;
                    }
                }
            } else {
                if ($address->getType() == Address::TYPE_SHIPPING) {
                    $new[] = $address;
                }
            }
        }

        return $new;
    }
}
