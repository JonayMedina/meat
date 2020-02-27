<?php

namespace App\Controller\Admin;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class CouponController
 * @package App\Controller\Admin
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class CouponController extends AbstractController
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * CouponController constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     *
     * @Route("/coupon", name="coupons_index")
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('/admin/coupon/index.html.twig', [
            'total' => $this->countActiveCoupons(),
            'coupons' => $this->getCoupons()
        ]);
    }

    /**
     * Return total of active coupons.
     * @return int|null
     */
    private function countActiveCoupons(): ?int
    {
        try {
            $sql = "SELECT COUNT(c.id) FROM sylius_promotion_coupon c WHERE expires_at IS NULL OR expires_at < CURDATE();";
            $connection = $this->container->get('doctrine')->getManager()->getConnection();
            $stmt = $connection->prepare($sql);

            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return null;
        }
    }

    /**
     * Return coupons.
     * @return array
     */
    private function getCoupons(): array
    {
        try {
            $sql = "SELECT * FROM sylius_promotion_coupon c WHERE expires_at IS NULL OR expires_at < CURDATE();";
            $connection = $this->container->get('doctrine')->getManager()->getConnection();
            $stmt = $connection->prepare($sql);

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());

            return [];
        }
    }
}
