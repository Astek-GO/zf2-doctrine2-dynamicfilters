<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;

use Doctrine\ORM\EntityManager;

trait EntityManagerAware
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     *
     * @return $this
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

    protected function getSingleIdentifier($className)
    {
        $classMetaData = $this->getEntityManager()->getClassMetadata($className);

        return $classMetaData->getSingleIdentifierFieldName();
    }

    protected function getSingleIdentifierProperty($className)
    {
        $classMetaData = $this->getEntityManager()->getClassMetadata($className);

        return $classMetaData->getSingleIdReflectionProperty();
    }
} 