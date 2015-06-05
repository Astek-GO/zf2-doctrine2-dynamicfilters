<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;


interface ScalarValueInterface
{
    /**
     * @return null|mixed
     */
    public function getValue();

    /**
     * @param null|mixed $value
     *
     * @return mixed
     */
    public function setValue($value);

    /**
     * @return mixed
     */
    public function getDefaultValue();

    /**
     * @param null|mixed $value
     *
     * @return mixed
     */
    public function setDefaultValue($value);

    /**
     * @return null|mixed
     */
    public function getValueOrDefault();
} 