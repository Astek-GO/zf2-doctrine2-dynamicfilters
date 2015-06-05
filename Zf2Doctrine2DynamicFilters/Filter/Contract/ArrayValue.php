<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;

use Zf2Doctrine2DynamicFilters\QueryFilter;

trait ArrayValue
{
    /**
     * @var null|array
     */
    protected $values = QueryFilter::DEFAULT_VALUE;
    /**
     * @var null|array
     */
    protected $defaultValues = null;

    /**
     * @return array|null
     */
    public function getValuesOrDefault()
    {
        if (null !== $this->values) {
            return $this->getValues();
        } else {
            return $this->getDefaultValues();
        }
    }

    /**
     * @return null|array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param null|array $values
     *
     * @return $this
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getDefaultValues()
    {
        return $this->defaultValues;
    }

    /**
     * @param null|array $values
     *
     * @return $this
     */
    public function setDefaultValues($values)
    {
        $this->defaultValues = $values;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasValue()
    {
        return null != $this->getValues();
    }
} 