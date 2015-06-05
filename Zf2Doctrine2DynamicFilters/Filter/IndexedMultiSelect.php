<?php

namespace Zf2Doctrine2DynamicFilters\Filter;


use Closure;
use Zf2Doctrine2DynamicFilters\Filter\Contract\EntityManagerAware;
use Zf2Doctrine2DynamicFilters\Filter\Contract\EntityManagerAwareInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\MappingException;

class IndexedMultiSelect extends MultiSelect implements EntityManagerAwareInterface
{
    use EntityManagerAware;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var EntityRepository
     */
    protected $entityRepository = null;


    /**
     * @param string   $name
     * @param string   $column
     * @param string   $entityName
     * @param callable $labelGenerator
     * @param string   $formElementLabel
     */
    public function __construct($name, $column, $entityName, Closure $labelGenerator, $formElementLabel = null)
    {
        parent::__construct($name, $column, $labelGenerator, $formElementLabel);
        $this->entityName     = $entityName;
        $this->labelGenerator = $labelGenerator;
    }

    /**
     * @param array $values
     *
     * @return array
     * @throws MappingException
     */
    protected function preparePossibleValues($values)
    {
        $processedValues = [];

        if (! empty($values)) {

            $singleIdentifierColumn = $this->getSingleIdentifier($this->entityName);

            $singleIdentificationProperty = $this->getSingleIdentifierProperty($this->entityName);

            $entities = $this->getEntityRepository()->findBy([
                $singleIdentifierColumn => $values
            ]);

            $function = $this->labelGenerator;

            foreach ($entities as $entity) {
                $processedValues[$singleIdentificationProperty->getValue($entity)] = $function($entity);
            }
        }

        return $processedValues;
    }

    /**
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        if (null === $this->entityRepository) {
            $this->entityRepository = $this->getEntityManager()->getRepository($this->entityName);
        }

        return $this->entityRepository;
    }
}