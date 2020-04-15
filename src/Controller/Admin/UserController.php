<?php

namespace App\Controller\Admin;

use App\Entity\User\AdminUser;
use App\Form\Admin\AdminUserType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class UserController extends AbstractController
{
    const PAGINATOR_LIMIT = 20;

    const ORDER_ABC = 'abc';

    const ORDER_RECENT = 'recent';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * UserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param PaginatorInterface $paginator
     * @param LoggerInterface $logger
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManagerInterface $entityManager, PaginatorInterface $paginator, LoggerInterface $logger, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
        $this->logger = $logger;
        $this->translator = $translator;
    }


    /**
     *
     * @Route("/user", name="users_index", options={"expose"="true"})
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AdminUser::ROLE_ADMIN);

        $page = $request->get('page', 1);
        $filter = $request->get('filter');
        $roleFilter = $request->get('role');
        $orderFilter = $request->get('order');

        $queryBuilder = $this->entityManager->getRepository('App:User\AdminUser')
            ->createQueryBuilder('a');

        /**
         * Search filter...
         */
        if (!empty($filter)) {
            $queryBuilder
                ->andWhere('a.firstName LIKE :filter OR a.lastName LIKE :filter OR a.username LIKE :filter OR a.email LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        /**
         * Filter by roles...
         */
        if (!empty($roleFilter)) {
            $queryBuilder
                ->andWhere('a.roles LIKE :role')
                ->setParameter('role', '%'. $roleFilter .'%');
        }

        /**
         * Order...
         */
        if (!empty($orderFilter)) {
            switch ($orderFilter) {
                case self::ORDER_RECENT:
                    $queryBuilder
                        ->orderBy('a.createdAt', 'DESC');

                    break;
                case self::ORDER_ABC:
                    $queryBuilder
                        ->orderBy('a.firstName', 'ASC');
                    break;
            }
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            self::PAGINATOR_LIMIT
        );

        return $this->render('/admin/user/index.html.twig', [
            'pagination' => $pagination,
            'total' => $this->countUsers()
        ]);
    }

    /**
     * New user.
     * @Route("/user/new", name="users_new")
     * @param Request $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AdminUser::ROLE_ADMIN);

        $user = new AdminUser();
        $user->setLocaleCode($this->getParameter('locale'));
        $user->setEnabled(true);
        $user->setLocked(false);

        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('temporalPassword')->getData();
            $role = $form->get('role')->getData();

            $user->setUsername($user->getEmail());
            $user->setPlainPassword($password);
            $user->setRoles([]);
            $user->addRole($role);

            $this->entityManager->persist($user);

            try {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.ui.user_new_success_message'));
            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.user_new_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('users_index');
        }

        return $this->render('/admin/user/new.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * New user.
     * @Route("/user/{id}/edit", name="users_edit")
     * @param Request $request
     * @return Response
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AdminUser::ROLE_ADMIN);

        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        /** @var AdminUser $user */
        $user = $manager->getRepository('App:User\AdminUser')->find($id);

        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->get('role')->getData();

            $user->setUsername($user->getEmail());
            $user->setRoles([]);
            $user->addRole($role);

            try {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('app.ui.user_edit_success_message'));
            } catch (\Exception $exception) {
                $this->addFlash('danger', $this->translator->trans('app.ui.user_edit_error_message'));
                $this->logger->error($exception->getMessage());
            }

            return $this->redirectToRoute('users_index');
        }

        return $this->render('/admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * Show user
     * @Route("/user/{id}", name="users_show", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function showAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AdminUser::ROLE_ADMIN);
        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        /** @var AdminUser $user */
        $user = $manager->getRepository('App:User\AdminUser')->find($id);

        return $this->render('/admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Delete user
     * @Route("/user/{id}", name="users_delete", methods={"DELETE"})
     * @param Request $request
     * @return Response
     */
    public function deleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AdminUser::ROLE_ADMIN);
        $id = $request->get('id');

        $manager = $this->get('doctrine')->getManager();
        /** @var AdminUser $user */
        $user = $manager->getRepository('App:User\AdminUser')->find($id);

        $manager->remove($user);

        try {
            $manager->flush();

            return new JsonResponse(['type' => 'info', 'message' => 'Ok'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return new JsonResponse(['type' => 'error', 'message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    private function countUsers()
    {
        try {
            $total = $this->entityManager->getRepository('App:User\AdminUser')
                ->createQueryBuilder('a')
                ->select('COUNT(a)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $total = null;
        }

        return $total;
    }
}
