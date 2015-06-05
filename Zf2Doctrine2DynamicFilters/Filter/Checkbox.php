<?php

namespace Zf2Doctrine2DynamicFilters\Filter;

use Zf2Doctrine2DynamicFilters\Filter\Contract\ScalarValue;
use Zf2Doctrine2DynamicFilters\Filter\Contract\ScalarValueInterface;
use Zf2Doctrine2DynamicFilters\Filter\Contract\StaticData;
use Zf2Doctrine2DynamicFilters\Filter\Contract\StaticDataInterface;
use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Filter\Filter as DoctrineSpecificationFilter;
use Zend\Form\Element as Element;

class Checkbox extends Filter implements StaticDataInterface, ScalarValueInterface
{
    use StaticData, ScalarValue;
    /**
     * @var DoctrineSpecificationFilter
     */
    protected $filterOn = null;
    /**
     * @var DoctrineSpecificationFilter
     */
    protected $filterOff = null;
    /**
     * @var string
     */
    protected $tableAlias;

    /**
     * @param                             $name
     * @param                             $tableAlias
     * @param DoctrineSpecificationFilter $filterOn
     * @param DoctrineSpecificationFilter $filterOff
     * @param null                        $formElementLabel
     */
    public function __construct($name, $tableAlias, DoctrineSpecificationFilter $filterOn = null, DoctrineSpecificationFilter $filterOff = null, $formElementLabel = null)
    {
        parent::__construct($name, $formElementLabel);
        $this->filterOn   = $filterOn;
        $this->filterOff  = $filterOff;
        $this->tableAlias = $tableAlias;
    }

    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function addFilterToQuery(QueryBuilder $qb)
    {
        if (true == $this->getValueOrDefault()) {
            if (null !== $this->filterOn) {
                $qb->andWhere($this->filterOn->getFilter($qb, $this->tableAlias));
            }
        } else {
            if (null !== $this->filterOff) {
                $qb->andWhere($this->filterOff->getFilter($qb, $this->tableAlias));
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getFormElementDefinition()
    {
        return [
            'type'       => Element\Checkbox::class,
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
            'label' => $this->getFormElementLabel(),
        ];
    }

    /**
     * @return array
     */
    protected function getFormElementAttributes()
    {
        return [
            'value' => (int) $this->getValueOrDefault()
        ];
    }
}