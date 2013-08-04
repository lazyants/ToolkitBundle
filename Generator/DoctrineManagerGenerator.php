<?php

namespace Lazyants\ToolkitBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Generates a CRUD controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineManagerGenerator extends Generator
{
    /**
     * @var array
     */
    private $skeletonDirs = array();

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * @var string
     */
    protected $routeNamePrefix;

    /**
     * @var \Symfony\Component\HttpKernel\Bundle\BundleInterface
     */
    protected $bundle;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadataInfo
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $format;

    /**
     * @var array
     */
    protected $actions = array();

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string|array $skeletonDirs
     */
    public function setSkeletonDirs($skeletonDirs)
    {
        $this->skeletonDirs = is_array($skeletonDirs) ? $skeletonDirs : array($skeletonDirs);

        parent::setSkeletonDirs($skeletonDirs);
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface $bundle A bundle object
     * @param string $entity The entity relative class name
     * @param ClassMetadataInfo $metadata The entity class metadata
     * @param string $format The configuration format (xml, yaml, annotation)
     * @param string $routePrefix The route name prefix
     *
     * @throws \RuntimeException
     */
    public function generate(
        BundleInterface $bundle,
        $entity,
        ClassMetadataInfo $metadata,
        $routePrefix,
        $forceOverwrite,
        array $actions = array('index', 'new', 'manage', 'delete')
    ) {
        $this->routePrefix = $routePrefix;
        $this->routeNamePrefix = $this->getRouteNamePrefix($bundle, $entity);
        $this->actions = $actions;

        if (count($metadata->identifier) > 1) {
            throw new \RuntimeException('The manager generator does not support entity classes with multiple primary keys.');
        }

        if (!in_array('id', $metadata->identifier)) {
            throw new \RuntimeException('The manager generator expects the entity object has a primary key field named "id" with a getId() method.');
        }

        $this->entity = $entity;
        $this->bundle = $bundle;
        $this->metadata = $metadata;
        $this->format = 'annotation';

        $this->generateControllerClass($forceOverwrite);

        $dir = sprintf('%s/Resources/views/%s', $this->bundle->getPath(), str_replace('\\', '/', $this->entity));

        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }

        foreach ($this->actions as $action) {
            $this->generateActionView(
                sprintf('manager/views/%s.html.twig.twig', $action),
                $dir . sprintf('/%s.html.twig', $action)
            );
        }
    }

    /**
     * @param BundleInterface $bundle
     * @param string $entity
     * @return string
     */
    protected function getRouteNamePrefix(BundleInterface $bundle, $entity)
    {
        $bundleNameClear = str_replace('Bundle', '', $bundle->getName());

        $prefix = str_replace($bundle->getName(), $bundleNameClear, $bundle->getNamespace());
        $prefix = str_replace('\\', '_', $prefix);
        $prefix .= '_' . $entity . '_';

        return strtolower($prefix);
    }

    /**
     * Generates the controller class only.
     *
     * @param bool $forceOverwrite
     * @throws \RuntimeException
     */
    protected function generateControllerClass($forceOverwrite)
    {
        $dir = $this->bundle->getPath();
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $target = sprintf(
            '%s/Controller/%s/%sController.php',
            $dir,
            str_replace('\\', '/', $entityNamespace),
            $entityClass
        );

        if (!$forceOverwrite && file_exists($target)) {
            throw new \RuntimeException('Unable to generate the controller as it already exists.');
        }

        $this->renderFile(
            'manager/controller.php.twig',
            $target,
            array(
                'actions' => $this->actions,
                'route_prefix' => $this->routePrefix,
                'route_name_prefix' => $this->routeNamePrefix,
                'bundle' => $this->bundle->getName(),
                'entity' => $this->entity,
                'entity_class' => $entityClass,
                'namespace' => $this->bundle->getNamespace(),
                'entity_namespace' => $entityNamespace,
                'format' => $this->format,
            )
        );
    }

    /**
     * Generates the %action%.html.twig template in the final bundle.
     *
     * @param string $template
     * @param string $destination
     */
    protected function generateActionView($template, $destination)
    {
        $templateExists = false;

        foreach ($this->skeletonDirs as $skeletionDir) {
            if ($this->filesystem->exists($skeletionDir . DIRECTORY_SEPARATOR . $template)) {
                $templateExists = true;
                break;
            }
        }

        if ($templateExists) {
            $this->renderFile(
                $template,
                $destination,
                array(
                    'actions' => $this->actions,
                    'route_prefix' => $this->routePrefix,
                    'route_name_prefix' => $this->routeNamePrefix,
                    'bundle' => $this->bundle->getName(),
                    'entity' => $this->entity,
                    'fields' => $this->metadata->fieldMappings,
                )
            );
        }
    }
}
