<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;

use Zf2Doctrine2DynamicFilters\QueryFilter;

trait ScalarValue
{
    /**
     * @var null|mixed
     */
    protected $value = QueryFilter::DEFAULT_VALUE;

    /**
     * @var null|mixed
     */
    protected $defaultValue = null;

    /**
     * @return null|mixed
     */
    public function getValueOrDefault()
    {
        if (null !== $this->value) {
            return $this->getValue();
        } else {
            return $this->getDefaultValue();
        }
    }

    public function hasValue(){
        return null != $this->getValue();
    }

    /**
     * @return null|mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null|mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param null|mixed $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
} 