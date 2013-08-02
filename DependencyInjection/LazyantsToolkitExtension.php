<?php

namespace Lazyants\ToolkitBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LazyantsToolkitExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lazyants_toolkit.translation.bundles', $config['translation']['bundles']);
        $container->setParameter('lazyants_toolkit.translation.locales', $config['translation']['locales']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        return new Configuration(array_keys($bundles));
    }
}
