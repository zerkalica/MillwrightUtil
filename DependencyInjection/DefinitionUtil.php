<?php
namespace Millwright\Util\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class DefinitionUtil
{
    protected $container;
    protected $sortAttr;

    public function __construct(ContainerBuilder $container, $sortAttr = 'priority')
    {
        $this->container = $container;
        $this->sortAttr  = $sortAttr;
    }

    /**
     * Get sorted defs, sort by prirorty
     *
     * @param string        $tag
     * @param callable|null $callback how to store definition to sortable container: definition, id
     *
     * @return array
     */
    public function getSortedDefs($tag, \Closure $callback = null)
    {
        $containers = new \SplPriorityQueue();

        foreach ($this->container->findTaggedServiceIds($tag) as $id => $tags) {
            $definition = $this->container->getDefinition($id);
            $attributes = $definition->getTag($tag);
            $priority   = isset($attributes[0][$this->sortAttr])
                ? $attributes[0][$this->sortAttr]
                : 0;

            $data = $callback ? $callback($definition, $id) : $definition;

            $containers->insert($data, $priority);
        }

        $containers = iterator_to_array($containers);
        ksort($containers);

        return $containers;
    }
}
