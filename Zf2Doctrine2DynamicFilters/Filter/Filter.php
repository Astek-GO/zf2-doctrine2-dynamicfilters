<?php

namespace Zf2Doctrine2DynamicFilters\Filter;


use Zf2Doctrine2DynamicFilters\Form\FilterForm;
use Doctrine\ORM\QueryBuilder;

abstract class Filter
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $formElementLabel;

    /**
     * @param string      $name
     * @param string|null $formElementLabel
     */
    function __construct($name, $formElementLabel = null)
    {
        $this->name      = $name;
        $this->formElementLabel = null === $formElementLabel ? $name : $formElementLabel;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public abstract function addFilterToQuery(QueryBuilder $qb);



    /**
     * @return string
     */
    public function getFormElementLabel()
    {
        return $this->formElementLabel;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public abstract function hasValue();

    /**
     * @param FilterForm $form
     *
     * @return $this
     */
    public function addFilterToForm(FilterForm $form){
        $form->add($this->getFormElementDefinition());
        return $this;
    }

    /**
     * @return array
     */
    protected abstract function getFormElementDefinition();

    /**
     * @return array
     */
    protected abstract function getFormElementOptions();

    /**
     * @return array
     */
    protected abstract function getFormElementAttributes();

    /**
     * @param string|null $alias
     *
     * @return string
     */
    public static function generateDoctrineAlias($alias)
    {
        /**
         * TODO: add more invalid doctrine chars
         */
        return preg_replace('[-]','_', $alias);
    }

    /**
     * @return string
     */
    public function getDoctrineAlias(){
        return static::generateDoctrineAlias($this->getName());
    }
}