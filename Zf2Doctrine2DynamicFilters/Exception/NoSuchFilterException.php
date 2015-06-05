<?php namespace Zf2Doctrine2DynamicFilters\Exception;

use Exception;

class NoSuchFilterException extends Exception{

    public function __construct($filterName)
    {
        parent::__construct(vprintf('Filter [%s] is does not exist', [$filterName]));
    }
}