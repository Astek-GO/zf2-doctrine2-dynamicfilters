<?php

namespace Zf2Doctrine2DynamicFilters;

use Zf2Doctrine2DynamicFilters\Exception\FilterAlreadyDefinedException;
use Zf2Doctrine2DynamicFilters\Exception\NoSuchFilterException;
use Zf2Doctrine2DynamicFilters\Filter\Contract\ArrayValueInterface;
use Zf2Doctrine2DynamicFilters\Filter\Contract\DynamicDataInterface;
use Zf2Doctrine2DynamicFilters\Filter\Contract\EntityManagerAwareInterface;
use Zf2Doctrine2DynamicFilters\Filter\Contract\ScalarValueInterface;
use Zf2Doctrine2DynamicFilters\Filter\Filter;
use Zf2Doctrine2DynamicFilters\Form\FilterForm;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class QueryFilter
{

    const DEFAULT_VALUE = null;
    const NO_VALUE = '';

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * @var Filter[]
     */
    protected $filters = [];

    /**
     * @var FilterForm
     */
    protected $form = null;

    /**
     * @var bool
     */
    protected $computed = false;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * @var bool
     */
    protected $storeInSession = true;


    /**
     * @var bool
     */
    protected $resetFlag = false;

    /**
     * @var bool
     */
    protected $defaultFlag = false;

    /**
     * @var string
     */
    protected $sessionContainerName = 'QueryFilter';

    /**
     * @var Container
     */

    protected $sessionContainer = null;

    protected $request = null;


    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param QueryBuilder            $query
     * @param FilterForm              $form
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, QueryBuilder $query, FilterForm $form = null)
    {
        $this->serviceLocator = $serviceLocator;
        $this->queryBuilder   = $query;
        $this->form           = (null === $form ? new FilterForm('Filter') : $form);

        $this->setFlags();
    }

    /**
     * @return boolean
     */
    public function isStoreInSession()
    {
        return $this->storeInSession;
    }

    /**
     * @param boolean $storeInSession
     *
     * @return $this
     */
    public function setStoreInSession($storeInSession)
    {
        $this->storeInSession = $storeInSession;

        return $this;
    }

    /**
     * @return string
     */
    protected function getSessionContainerName()
    {
        return $this->sessionContainerName;
    }

    /**
     * @param string $sessionContainerName
     *
     * @return $this
     */
    public function setSessionContainerName($sessionContainerName)
    {
        $this->sessionContainerName = $sessionContainerName;

        return $this;
    }

    /**
     * @param Filter $filter
     *
     * @return $this
     * @throws FilterAlreadyDefinedException
     */
    public function addFilter(Filter $filter)
    {
        $filterName = $filter->getName();

        if (isset($this->filters[$filterName])) {
            throw new FilterAlreadyDefinedException($filterName);
        }

        $this->filters[$filterName] = $filter;

        $this->injectDependenciesIntoFilter($filter);

        $this->injectSelectedValuesIntoFilter($filter);


        return $this;
    }

    /**
     * @param Filter $filter
     *
     * @return $this
     */
    protected function injectDependenciesIntoFilter(Filter $filter)
    {
        if ($filter instanceof EntityManagerAwareInterface) {
            $filter->setEntityManager($this->queryBuilder->getEntityManager());
        }

        return $this;
    }

    /**
     * @param Filter $filter
     *
     * @return $this
     */
    protected function injectSelectedValuesIntoFilter(Filter $filter)
    {
        $filterName = $filter->getName();

        $values = $this->getFilterValuesFromPostOrSession($filterName);

        if ($filter instanceof ScalarValueInterface) {
            if (empty($values)) {
                $value = self::NO_VALUE;
            } else {
                $value = array_values($values)[0];
            }
            $filter->setValue($value);
        }

        if ($filter instanceof ArrayValueInterface) {
            $filter->setValues($values);
        }

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if (null == $this->request) {
            $this->request = $this->serviceLocator->get('Request');
        }

        return $this->request;
    }

    /**
     * @return Container
     */
    protected function getSessionContainer()
    {
        if (null == $this->sessionContainer) {
            $this->sessionContainer = new Container($this->getSessionContainerName());
        }

        return $this->sessionContainer;
    }

    /**
     * @param string $filterName
     *
     * @return array
     */
    protected function getFilterValuesFromPostOrSession($filterName)
    {
        $request = $this->getRequest();
        $values  = self::DEFAULT_VALUE;

        if ($this->isStoreInSession()) {
            $container = $this->getSessionContainer();

            /** @noinspection PhpUndefinedFieldInspection */
            if (isset($container->filter[$filterName]) && ! empty($container->filter[$filterName])) {
                /** @noinspection PhpUndefinedFieldInspection */
                $values = $container->filter[$filterName];
            }
        }

        if ($request->isPost()) {
            $values = $request->getPost($filterName, self::NO_VALUE);
        }

        $values = $this->prepareValue($values);

        if ($this->isStoreInSession()) {

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedFieldInspection */
            $filters = $container->filter;

            $filters[$filterName] = $values;

            /** @noinspection PhpUndefinedFieldInspection */
            $container->filter = $filters;
        }

        return $values;
    }


    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @return $this
     */
    public function prepare()
    {
        $this->computeAvailableValues();

        foreach ($this->filters as $filter) {
            $filter->addFilterToForm($this->getForm());
        }

        $this->getForm()->prepare();

        return $this;
    }

    /**
     * @return $this
     */
    protected function preCompute()
    {
        $this->applyFilters();

        return $this;
    }

    /**
     * @return array
     */
    protected function compute()
    {

        $qb = clone $this->queryBuilder;

        $qb->resetDQLParts(['select', 'groupBy', 'having']);

        foreach ($this->filters as $filter) {
            if ($filter instanceof DynamicDataInterface) {
                $filter->addColumnToQuery($qb);
            }
        }

        $results = $qb->getDQLPart('select')
            ? $qb->getQuery()->getArrayResult()
            : [];


        return $results;
    }

    /**
     * @return $this
     */
    protected function postCompute()
    {

        return $this;
    }


    /**
     * @return $this
     */
    protected function computeAvailableValues()
    {
        if (! $this->isComputed()) {

            $this->preCompute();

            $results = $this->compute();

            $this->injectPossibleValuesIntoFilters($results);

            $this->postCompute();

            $this->setComputed();
        }

        return $this;
    }

    /**
     * @return boolean
     */
    protected function isComputed()
    {
        return $this->computed;
    }

    /**
     * @param boolean $computed
     *
     * @return $this
     */
    protected function setComputed($computed = true)
    {
        $this->computed = $computed;

        return $this;
    }

    /**
     * Application des filtres
     */
    protected function applyFilters()
    {
        foreach ($this->filters as $filter) {
            $filter->addFilterToQuery($this->queryBuilder);
        }
    }

    /**
     * @return FilterForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return bool
     */
    public function hasUserValues()
    {
        foreach ($this->filters as $filter) {
            if ($filter->hasValue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $filterName
     *
     * @return Filter
     * @throws NoSuchFilterException
     */
    public function getFilter($filterName)
    {
        if (! isset($this->filters[$filterName])) {
            throw new NoSuchFilterException($filterName);
        }

        return $this->filters[$filterName];
    }

    /**
     * @param bool $useLabel
     *
     * @return array|null
     */
    public function getFilterLabelValues($useLabel = false)
    {
        $values = [];

        foreach ($this->filters as $filter) {
            if ($useLabel) {
                $name = $filter->getFormElementLabel();
            } else {
                $name = $filter->getName();
            }
            if ($filter instanceof ScalarValueInterface) {
                $value = $filter->getValueOrDefault();
                if ($value) {
                    if ($filter instanceof DynamicDataInterface) {
                        $possibleValues = $filter->getPossibleValues();
                        $values[$name]  = $possibleValues[$value];
                    } else {
                        $values[$name] = $value;
                    }
                }
            } elseif ($filter instanceof ArrayValueInterface) {
                $values = $filter->getValuesOrDefault();
                if ($values) {
                    if ($filter instanceof DynamicDataInterface) {
                        $possibleValues = $filter->getPossibleValues();
                        $values[$name]  = array_intersect_key($possibleValues, array_flip($values));
                    } else {
                        $values[$name] = $values;
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param bool $useLabel
     *
     * @return array|null
     */
    public function getFilterValues($useLabel = false)
    {
        $values = [];

        foreach ($this->filters as $filter) {
            if ($useLabel) {
                $name = $filter->getFormElementLabel();
            } else {
                $name = $filter->getName();
            }
            if ($filter instanceof ScalarValueInterface) {
                $value = $filter->getValueOrDefault();
                if ($value) {
                    $values[$name] = $value;
                }
            } elseif ($filter instanceof ArrayValueInterface) {
                $values = $filter->getValuesOrDefault();
                if ($values) {
                    $values[$name] = $values;
                }
            }
        }

        return $values;
    }

    /**
     * @return boolean
     */
    public function hasResetFlag()
    {
        return $this->resetFlag;
    }

    /**
     * @param boolean $resetFlag
     *
     * @return $this
     */
    public function setResetFlag($resetFlag = true)
    {
        $this->resetFlag = $resetFlag;

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasDefaultFlag()
    {
        return $this->defaultFlag;
    }

    /**
     * @param boolean $defaultFlag
     *
     * @return $this
     */
    public function setDefaultFlag($defaultFlag = true)
    {
        $this->defaultFlag = $defaultFlag;

        return $this;
    }

    /**
     * @return $this
     */
    protected function setFlags()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = $request->getPost();

            if (isset($postData[FilterForm::SUBMIT_RESET])) {
                $this->setResetFlag();
            }

            if (isset($postData[FilterForm::SUBMIT_DEFAULT])) {
                $this->setDefaultFlag();
            }
        }

        return $this;
    }

    /**
     * @param array|mixed $values
     *
     * @returns array
     */
    protected function prepareValue($values)
    {
        if ($this->hasResetFlag()) {
            $values = self::NO_VALUE;
        }

        if ($this->hasDefaultFlag()) {
            $values = self::DEFAULT_VALUE;
        }

        if (! is_array($values)) {
            $values = [$values];
        }

        return $values;
    }

    /**
     * @param array $results
     *
     * @return $this
     */
    private function injectPossibleValuesIntoFilters(array $results)
    {
        if (! empty($results)) {

            $linedResults = [];

            foreach ($results as $line) {
                foreach ($this->filters as $filter) {
                    $filterName = $filter->getDoctrineAlias();
                    if ($filter instanceof DynamicDataInterface) {
                        $linedResults[$filterName][] = $line[$filterName];
                    }
                }
            }

            foreach ($this->filters as $filter) {
                $filterName = $filter->getDoctrineAlias();
                if ($filter instanceof DynamicDataInterface) {
                    $filter->setPossibleValues((array_unique($linedResults[$filterName])));
                }
            }
        } else {
            foreach ($this->filters as $filter) {
                if ($filter instanceof DynamicDataInterface) {
                    $filter->setPossibleValues([]);
                }
            }
        }

        return $this;
    }
}