<?php

namespace App\Pagination;

use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class PaginationFactory
 * @package App\Pagination
 */
class PaginationFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * PaginationFactory constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $filter
     * @param int $page
     * @param int $limit
     * @param string $route
     * @param array $routeParams
     * @param string $message
     * @param int $code
     * @param string $type
     * @return PaginatedCollection
     */
    public function createCollection(QueryBuilder $qb = null, $filter = '', $page = 1, $limit = 10, $route = '', array $routeParams = [], $message = 'Ok', $code = Response::HTTP_OK, $type = 'info')
    {
        $items = [];
        $numberOfResults = 0;
        $numberOfPages = 0;
        $hasNextPage = false;
        $hasPreviousPage = false;
        $nextPage = null;
        $previousPage = null;

        if (!empty($qb)) {
            $adapter = new DoctrineORMAdapter($qb, true, false);
            $pager = new Pagerfanta($adapter);
            $pager->setMaxPerPage($limit);
            $pager->setCurrentPage($page);

            foreach ($pager->getCurrentPageResults() as $result) {
                $items[] = $result;
            }

            $numberOfResults = $pager->getNbResults();
            $numberOfPages = $pager->getNbPages();
            $hasNextPage = $pager->hasNextPage();
            $hasPreviousPage = $pager->hasPreviousPage();

            if ($hasNextPage) {
                $nextPage = $pager->getNextPage();
            }

            if ($hasPreviousPage) {
                $previousPage = $pager->getPreviousPage();
            }
        }

        $paginatedCollection = new PaginatedCollection($items, $numberOfResults, $limit, $message, $code, $type);

        $tmpParams = $routeParams;
        $routeParams['page'] = $page;

        if (isset($routeParams['sort_field'])) {
            unset($routeParams['sort_field']);
        }

        if (isset($routeParams['sort'])) {
            unset($routeParams['sort']);
        }

        if (!empty($filter)) {
            $routeParams['filter'] = $filter;
        }

        if (!empty($limit)) {
            $routeParams['limit'] = $limit;
        }

        $routeParams = $routeParams + $tmpParams;

        $createLinkUrl = function ($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                ['page' => $targetPage]
            ));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($numberOfPages));

        if ($hasNextPage) {
            $paginatedCollection->addLink('next', $createLinkUrl($nextPage));
        }

        if ($hasPreviousPage) {
            $paginatedCollection->addLink('prev', $createLinkUrl($previousPage));
        }

        return $paginatedCollection;
    }
}
