<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;


interface ArrayValueInterface
{
    /**
     * @param null|array $values
     *
     * @return mixed
     */
    public function setValues($values);

    /**
     * @return null|array
     */
    public function getValues();

    /**
     * @param null|array $values
     *
     * @return mixed
     */
    public function setDefaultValues($values);

    /**
     * @return null|array
     */
    public function getDefaultValues();

    /**
     * @return null|array
     */
    public function getValuesOrDefault();
}