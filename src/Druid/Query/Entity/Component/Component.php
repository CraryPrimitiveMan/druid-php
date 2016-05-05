<?php
/**
 * @author    jhrncar
 * @copyright PIXEL FEDERATION
 * @license   Internal use only
 */

namespace Druid\Query\Entity\Component;

use Druid\Query\Entity\Component\DataSource\DataSource;
use Druid\Query\Entity\Component\Factory\AggregatorComponentFactory;
use Druid\Query\Entity\Component\Factory\ComponentFactoryManager;
use Druid\Query\Entity\Component\Factory\PostAggregatorComponentFactory;
use Druid\Query\Entity\Component\Granularity\PeriodGranularity;
use Druid\Query\Entity\Component\PostAggregation;
use Druid\Query\Entity\Component\Filter;
use Druid\Query\Entity\Component\Aggregation;

/**
 * Class Component
 * @package Druid\Query\Entity\Component
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Component
{

    /**
     * @var ComponentFactoryManager
     */
    private $factories;

    /**
     * Component constructor.
     * @param ComponentFactoryManager $factories
     */
    public function __construct(ComponentFactoryManager $factories)
    {
        $this->factories = $factories;
    }


    /**
     * @param array $filter
     * @param array $aggregator
     * @return Aggregation\FilteredAggregation
     */
    public function filteredAggregator(array $filter, array $aggregator)
    {
        $factory = $this->factories
            ->getFactory(AggregatorComponentFactory::TYPE_AGG);

        return $factory
            ->create(
                'filtered',
                [
                    $this->selectorFilter($filter[0], $filter[1]),
                    $this->standardAggregator($aggregator[0], $aggregator[1], $aggregator[2])
                ]
            );
    }

    /**
     * @param string $dimension
     * @param string $value
     * @return Filter\SelectorFilter
     */
    public function selectorFilter($dimension, $value)
    {
        return new Filter\SelectorFilter($dimension, $value);
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $fieldName
     * @return Aggregation\StandardAggregator
     */
    public function standardAggregator($type, $name, $fieldName)
    {
        return $this->factories
            ->getFactory(AggregatorComponentFactory::TYPE_AGG)
            ->create('standard', [$type, $name, $fieldName]);
    }

    /**
     * @param string $name
     * @param string $function
     * @param array $fields
     * @return PostAggregation\ArithmeticPostAggregator
     */
    public function arithmeticPostAggregator($name, $function, array $fields)
    {
        return $this->factories
            ->getFactory(PostAggregatorComponentFactory::TYPE_POST_AGG)
            ->create('arithmetic', [$name, $function, new PostAggregation\PostAggregatorCollection($fields)]);
    }

    /**
     * @param string $name
     * @param string $fieldName
     * @return PostAggregation\FieldAccessPostAggregator
     */
    public function fieldAccessPostAggregator($name, $fieldName)
    {
        return $this->factories
            ->getFactory(PostAggregatorComponentFactory::TYPE_POST_AGG)
            ->create('fieldAccess', [$name, $fieldName]);
    }

    /**
     * @param string $name
     * @param float|int $value
     * @return PostAggregation\ConstantPostAggregator
     */
    public function constantPostAggregator($name, $value)
    {
        return $this->factories
            ->getFactory(PostAggregatorComponentFactory::TYPE_POST_AGG)
            ->create('constant', [$name, $value]);
    }

    /**
     * @param string $dataSource
     * @return DataSource
     */
    public function dataSource($dataSource)
    {
        return new DataSource($dataSource);
    }

    /**
     * @return PeriodGranularity
     */
    public function dayGranularity()
    {
        return $this->periodGranularity('P1D');
    }

    /**
     * @param $period
     * @param string $timeZone
     * @param string $origin
     * @return PeriodGranularity
     */
    public function periodGranularity($period, $timeZone = null, $origin = null)
    {
        return new PeriodGranularity($period, $timeZone, $origin);
    }
}
