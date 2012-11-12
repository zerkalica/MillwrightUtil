<?php
namespace Millwright\Util\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Definition\Processor;

use Millwright\Util\PhpUtil;

/**
 * Configuration static util class
 */
final class ContainerUtil
{
    /**
     * Get definitions by tag, sort, associate with keys and injects to specified service argument
     *
     * @param string           $tag
     * @param string           $serviceName
     * @param integer          $arg 
     * @param ContainerBuilder $container
     * @param boolean          $aggregate if true - result is array of definitions arrays, aggregated by type key
     *
     * @return Definition
     */
    public static function addDefinitionsToService(
        $tag,
        $serviceName,
        $arg,
        ContainerBuilder $container,
        $aggregate = false
    ) {
        $definitions = self::getDefinitionsByTag($tag, $container, $aggregate);

        return $container->getDefinition($serviceName)->replaceArgument($arg, $definitions);
    }

    /**
     * Get definitions by tag, sort, associate with keys and set specified parameter
     *
     * @param string           $tag
     * @param string           $paramName
     * @param ContainerBuilder $container
     * @param boolean          $aggregate if true - result is array of definitions arrays, aggregated by type key
     *
     * @return Definition
     */
    public static function addDefinitionsToParameter($tag, $paramName, ContainerBuilder $container, $aggregate = false)
    {
        $definitions = self::getDefinitionsByTag($tag, $container, $aggregate);

        return $container->setParameter($paramName, $definitions);
    }

    /**
     * Get service definitions from container by tag
     *
     * Sort by priority and associate with keys
     *
     * @param string           $tag
     * @param ContainerBuilder $container
     * @param boolean          $aggregate if true - result is array of definitions arrays, aggregated by type key
     *
     * @return Definition[]
     *
     * Or Definition[<type>][] - if type property used in tag
     */
    public static function getDefinitionsByTag($tag, ContainerBuilder $container, $aggregate = false)
    {
        $containers = new \SplPriorityQueue();
        foreach ($container->findTaggedServiceIds($tag) as $id => $tags) {
            $definition = $container->getDefinition($id);
            $attributes = $definition->getTag($tag);
            $priority   = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;

            $containers->insert($definition, $priority);
        }

        $containers = iterator_to_array($containers);
        ksort($containers);

        $definitions = array();
        foreach ($containers as $key => $definition) {
            $attributes = $definition->getTag($tag);
            $type       = isset($attributes[0]['type']) ? $attributes[0]['type'] : $key;

            if ($aggregate) {
                if (!isset($definitions[$type])) {
                    $definitions[$type] = array();
                }

                $definitions[$type][] = $definition;
            } else {
                $definitions[$type] = $definition;
            }
        }

        return $definitions;
    }

    /**
     * Collect configuration from tagged service arguments and merge them together
     *
     * @param string           $tag services, that extends \Millwright\ConfigurationBundle\Builder\Options
     * @param ContainerBuilder $container
     * @param \Closure|null    $normalizer function(array &$config, Processor $processor, ContainerBuilder $container)
     *
     * @return array merged configuration
     */
    public static function collectConfiguration($tag, ContainerBuilder $container, \Closure $normalizer = null)
    {
        $definitions = self::getDefinitionsByTag($tag, $container);

        $config = array();
        foreach ($definitions as $definition) {
            $bundleConfig = $definition->getArgument(0);
            $attributes   = $definition->getTag($tag);
            $type         = isset($attributes[0]['type']) ? $attributes[0]['type'] : null;

            if ($type) {
                $bundleConfig = array($type => $bundleConfig);
            }

            $config = PhpUtil::merge($config, $bundleConfig);
        }

        if (null !== $normalizer) {
            $normalizer($config, new Processor, $container);
        }

        return $config;
    }
}
