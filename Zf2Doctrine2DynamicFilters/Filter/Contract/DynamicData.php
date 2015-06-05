<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;


use Closure;

trait DynamicData
{

    /**
     * @var Closure
     */
    protected $sortFunction = 'asort';

    /**
     * @var mixed
     */
    protected $sortOptions = SORT_REGULAR;


    /** @var null|array */
    protected $possibleValues = [];

    /**
     * @return null|array
     */
    public function getPossibleValues()
    {
        return $this->possibleValues;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    public function sortPossiblesValues($values)
    {
        $sortFunction = $this->getSortFunction();

        $sortFunction($values, $this->getSortOptions());

        return $values;
    }


    /**
     * @return callable
     */
    public function getSortFunction()
    {
        return $this->sortFunction;
    }

    /**
     * @param null|callable $sortFunction
     *
     * @return $this
     */
    public function setSortFunction($sortFunction)
    {
        $this->sortFunction = $sortFunction;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortOptions()
    {
        return $this->sortOptions;
    }

    /**
     * @param mixed $sortOptions
     *
     * @return $this
     */
    public function setSortOptions($sortOptions)
    {
        $this->sortOptions = $sortOptions;

        return $this;
    }

    protected function mergePossibleValuesAndSelectedValues($possibleValues, $selectedValues)
    {
        if (! $selectedValues) {
            return $possibleValues;
        }

        if (! is_array($selectedValues)) {
            $selectedValues = [$selectedValues];
        }

        $possibleValues = array_unique(array_merge($selectedValues, $possibleValues));

        return $possibleValues;
    }
}