<?php

namespace Zf2Doctrine2DynamicFilters\Filter\Contract;

use Doctrine\ORM\EntityManager;

interface EntityManagerAwareInterface
{

    /**
     * @return EntityManager
     */
    public function getEntityManager();

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager($entityManager);
} 