<?php
namespace Millwright\Util\Request;

/**
 * Option registry
 */
class OptionRegistry implements OptionRegistryInterface
{
    protected $plainOptions = array();

    /**
     * {@inheritDoc}
     */
    public function addOption($key, $value)
    {
        $this->plainOptions[$key] = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions($key = null, array $overrides = null)
    {
        $options = ($key && array_key_exists($key, $this->plainOptions)) ? $this->plainOptions[$key] : $this->plainOptions;

        return array_filter(array_merge($options, (array) $overrides));
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery($namespace, array $overrides = null)
    {
        $options = $this->getOptions($namespace);

        return http_build_query(array_merge($options, (array) $overrides), null, '&');
    }
}
