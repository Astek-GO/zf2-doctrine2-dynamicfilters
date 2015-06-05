<?php

namespace Zf2Doctrine2DynamicFilters\Filter;

use Zf2Doctrine2DynamicFilters\Filter\Contract\ScalarValue;
use Zf2Doctrine2DynamicFilters\Filter\Contract\ScalarValueInterface;
use Zf2Doctrine2DynamicFilters\Filter\Contract\StaticData;
use Zf2Doctrine2DynamicFilters\Filter\Contract\StaticDataInterface;
use Doctrine\ORM\QueryBuilder;
use Zend\Form\Element as Element;

class TextSearch extends Filter implements StaticDataInterface, ScalarValueInterface
{

    use StaticData, ScalarValue;
    /**
     * @var string[]
     */
    protected $fields;

    /**
     * @param string      $name
     * @param string[]    $fields
     * @param string|null $formElementLabel
     */
    function __construct($name, $fields, $formElementLabel = null)
    {
        parent::__construct($name, $formElementLabel);
        $this->fields = $fields;
    }


    /**
     * @param QueryBuilder $qb
     *
     * @return $this
     */
    public function addFilterToQuery(QueryBuilder $qb)
    {
        $value = $this->getValueOrDefault();

        $orExpr = $qb->expr()->orX();
        if (null != $value && ! empty($value)) {
            foreach ($this->fields as $field) {
                $orExpr->add($qb->expr()->like($qb->expr()->lower($field), sprintf(':%s',  $this->getDoctrineAlias() )));
            }
            $qb->andWhere($orExpr)->setParameter( $this->getDoctrineAlias() , '%'.strtolower($value).'%');
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getFormElementDefinition()
    {
        return [
            'type'       => Element\Text::class,
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
            'value' => $this->getValueOrDefault()
        ];
    }
}