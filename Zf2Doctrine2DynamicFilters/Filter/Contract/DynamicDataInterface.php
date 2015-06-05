<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;


use Doctrine\ORM\QueryBuilder;

interface DynamicDataInterface
{

    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function addColumnToQuery(QueryBuilder $qb);

    /**
     * @param null|array $values
     *
     * @return $this
     */
    public function setPossibleValues($values);

    /**
     * @return null|array
     */
    public function getPossibleValues();

    /*
     * @param null|array $values
     *
     * @return null|array
     */
    public function sortPossiblesValues($values);

    /**
     * @return callable
     */
    public function getSortFunction();

    /**
     * @param null|callable $sortFunction
     *
     * @return $this
     */
    public function setSortFunction($sortFunction);

    /**
     * @return mixed
     */
    public function getSortOptions();

    /**
     * @param mixed $sortOptions
     *
     * @return $this
     */
    public function setSortOptions($sortOptions);
}