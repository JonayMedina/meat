<?php

namespace App\Service;

use App\Entity\Favorite;
use Psr\Log\LoggerInterface;
use App\Entity\User\ShopUser;
use App\Entity\Product\Product;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class FavoriteService
 * @package App\Service
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class FavoriteService
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var FavoriteRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * DashboardService constructor.
     * @param LoggerInterface $logger
     * @param FavoriteRepository $repository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(LoggerInterface $logger, FavoriteRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    /**
     * Return if given product has been marked as favorite for given user.
     * @param Product $product
     * @param ShopUser $user
     * @return bool
     */
    public function isFavorite(Product $product, ShopUser $user = null): bool
    {
        if (!$user instanceof ShopUser) {
            $this->logger->warning('Do not check if a product is favorite when user is not logged.');

            return false;
        }

        $favorite = $this->getFavorite($product, $user);

        return ($favorite instanceof Favorite);
    }

    /**
     * Add given product into given user's favorites.
     * @param Product $product
     * @param ShopUser $user
     * @return bool
     */
    public function addFavorite(Product $product, ShopUser $user): bool
    {
        if (!$this->isFavorite($product, $user)) {
            $favorite = new Favorite();
            $favorite->setProduct($product);
            $favorite->setShopUser($user);

            $this->entityManager->persist($favorite);

            try {
                $this->entityManager->flush();
                return true;
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * @param Product $product
     * @param ShopUser $user
     * @return bool
     */
    public function removeFavorite(Product $product, ShopUser $user): bool
    {
        if ($this->isFavorite($product, $user)) {
            $favorite = $this->getFavorite($product, $user);
            $this->entityManager->remove($favorite);

            try {
                $this->entityManager->flush();
                return true;
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                return false;
            }
        }

        return false;
    }

    /**
     * Return favorite entity if exists.
     * @param Product $product
     * @param ShopUser $user
     * @return Favorite|null
     */
    private function getFavorite(Product $product, ShopUser $user): ?Favorite
    {
        return $this->repository->findOneBy(['product' => $product, 'shopUser' => $user]);
    }
}
