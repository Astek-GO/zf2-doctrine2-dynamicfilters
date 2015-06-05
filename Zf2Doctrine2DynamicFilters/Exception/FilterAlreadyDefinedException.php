<?php namespace Zf2Doctrine2DynamicFilters\Exception;

use Exception;

class FilterAlreadyDefinedException extends Exception
{
    public function __construct($filterName)
    {
        parent::__construct(vsprintf("A filter with the name [%s] is already defined.", [$filterName]));
    }
}