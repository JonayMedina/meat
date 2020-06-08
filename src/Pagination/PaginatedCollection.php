<?php

namespace App\Pagination;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaginatedCollection
 * @package App\Pagination
 */
class PaginatedCollection
{
    /**
     * @var int $code
     */
    private $code;

    /**
     * @var string $type
     */
    private $type;

    /**
     * @var string $message
     */
    private $message;

    /**
     * @var array $items
     */
    public $recordset;

    /**
     * @var array $metadata
     */
    private $metadata;

    /**
     * PaginatedCollection constructor.
     * @param array $recordset
     * @param int $totalItems
     * @param int $limit
     * @param string $message
     * @param int $code
     * @param string $type
     */
    public function __construct(array $recordset = [], $totalItems = 0, $limit = 0, $message = 'Ok', $code = Response::HTTP_OK, $type = 'info')
    {
        $pages = 0;

        if ($limit > 0) {
            $pages = (int)ceil($totalItems / $limit);
        }

        $this->code = $code;
        $this->message = $message;
        $this->type = $type;
        $this->recordset = $recordset;
        $this->metadata = ['total' => $totalItems, 'count' => count($recordset), 'pages' => $pages];
    }

    /**
     * @param string $ref
     * @param string $url
     */
    public function addLink($ref, $url)
    {
        $this->metadata['links'][$ref] = $url;
    }
}
