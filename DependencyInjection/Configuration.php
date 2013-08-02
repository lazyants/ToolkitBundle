<?php

namespace Lazyants\ToolkitBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    private $bundles;

    /**
     * Constructor
     *
     * @param array $bundles An array of bundle names
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lazyants_toolkit');

        $rootNode
            ->children()
                ->arrayNode('translation')
                    ->children()
                        ->arrayNode('bundles')
                            ->defaultValue(array())
                            ->info('List bundle for translation extract')
                            ->example("['AcmeFrontendBundle', 'AcmeBackendBundle']")
                            ->prototype('scalar')
                                ->validate()
                                ->ifNotInArray($this->bundles)
                                    ->thenInvalid('%s is not a valid bundle.')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('locales')
                            ->defaultValue(array())
                            ->info('List of locales')
                            ->example("['en', 'de']")
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
