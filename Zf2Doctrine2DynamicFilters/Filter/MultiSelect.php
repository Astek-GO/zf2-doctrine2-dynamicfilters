<?php

namespace Zf2Doctrine2DynamicFilters\Filter;

use Closure;
use Zf2Doctrine2DynamicFilters\Filter\Contract\ArrayValue;
use Zf2Doctrine2DynamicFilters\Filter\Contract\ArrayValueInterface;
use Zf2Doctrine2DynamicFilters\Filter\Contract\DynamicData;
use Zf2Doctrine2DynamicFilters\Filter\Contract\DynamicDataInterface;
use Doctrine\ORM\QueryBuilder;
use Zend\Form\Element as Element;

class MultiSelect extends Filter implements DynamicDataInterface, ArrayValueInterface
{
    use ArrayValue, DynamicData;
    /**
     * @var callable
     */
    protected $labelGenerator;

    /**
     * @var string
     */
    protected $columnDefinition;

    /**
     * @param string   $name
     * @param string   $column
     * @param callable $labelGenerator
     * @param string   $formElementLabel
     */
    public function __construct($name, $column, Closure $labelGenerator = null, $formElementLabel = null)
    {
        parent::__construct($name, $formElementLabel);
        $this->columnDefinition = $column;
        $this->labelGenerator   = $labelGenerator;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function addColumnToQuery(QueryBuilder $qb)
    {
        $qb
            ->addSelect(vsprintf("%s AS %s", [$this->columnDefinition, $this->getDoctrineAlias()]))
            ->addGroupBy( $this->getDoctrineAlias() );

        return $this;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function addFilterToQuery(QueryBuilder $qb)
    {
        $values = $this->getValuesOrDefault();

        if (! empty($values)) {
            $qb->andWhere(vsprintf("%s IN (:%s)", [$this->columnDefinition,  $this->getDoctrineAlias()]))
                ->setParameter( $this->getDoctrineAlias(), $values);
        }

        return $this;
    }

    /**
     * @param array|null $values
     *
     * @return $this
     */
    public function setPossibleValues($values)
    {
        $values = $this->mergePossibleValuesAndSelectedValues($values, $this->getValuesOrDefault());

        $values = $this->preparePossibleValues($values);

        $values = $this->sortPossiblesValues($values);

        $this->possibleValues = $values;

        return $this;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    protected function preparePossibleValues($values)
    {
        $processedValues = [];

        $function = $this->labelGenerator;

        foreach ($values as $value) {
            $processedValues[$value] = $function ? $function($value) : $value;
        }

        return $processedValues;
    }

    /**
     * @return array
     */
    protected function getFormElementDefinition()
    {
        return [
            'type'       => Element\Select::class,
            'name'       => $this->getName(),
            'options'    => $this->getFormElementOptions(),
            'attributes' => $this->getFormElementAttributes()
        ];
    }

    /**
     * @return array
     */
    protected function getFormElementOptions()
    {
        return [
            'label'         => $this->getFormElementLabel(),
            'value_options' => $this->getPossibleValues(),
            'empty_option'  => 'SELECT_NONE'
        ];
    }

    /**
     * @return array
     */
    protected function getFormElementAttributes()
    {
        $values = $this->getValuesOrDefault();

        $attributes = [
            'required' => false,
            'id'       => 'filter' . $this->getName(),
            'multiple' => 'multiple',
            'value'    => ''
        ];

        if (! empty($values)) {
            $attributes['value'] = $values;
        }

        return $attributes;
    }
}