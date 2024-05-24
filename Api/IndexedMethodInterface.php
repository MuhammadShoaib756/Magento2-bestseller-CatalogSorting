<?php

namespace Shoaib\CatalogSorting\Api;

use Shoaib\CatalogSorting\Api\MethodInterface;

/**
 * Interface IndexedMethodInterface
 * @api
 */
interface IndexedMethodInterface extends MethodInterface
{
    /**
     * Get performance index Table name for sorting
     *
     * @return string
     */
    public function getIndexTableName(): string;

    /**
     * Full reindex Truncate index table, commit insert, revert on error
     *
     * @return void
     * @throws \Exception
     */
    public function reindex(): void;

    /**
     * Insert to index table
     *
     * @return void
     */
    public function doReindex(): void;

    /**
     * Returns Sorting method Table Column name which is using for order collection
     *
     * @return string
     */
    public function getSortingColumnName(): string;
}
