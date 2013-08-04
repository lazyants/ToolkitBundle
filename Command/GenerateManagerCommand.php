<?php

namespace Lazyants\ToolkitBundle\Command;

use Lazyants\ToolkitBundle\Generator\DoctrineManagerGenerator;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateManagerCommand extends GenerateDoctrineCommand
{
    private $formGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('lazyants:generate:manager')
            ->setDefinition(
                array(
                    new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                    new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
                    new InputOption(
                        'actions',
                        '',
                        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                        'List of actions in controller',
                        array('index', 'new', 'manage', 'delete')
                    ),
                )
            )
            ->setDescription(
                'Generate manager (controller, view and form)'
            );
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $format = Validators::validateFormat('annotation');
        $prefix = $this->getRoutePrefix($entity);
        $forceOverwrite = $input->getOption('overwrite');

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle) . '\\' . $entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        /** @var $generator \Lazyants\ToolkitBundle\Generator\DoctrineManagerGenerator */
        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $prefix, $forceOverwrite, $input->getOption('actions'));

        $this->generateForm($bundle, $entity, $metadata);

    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFormGenerator($bundle)->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e) {
            // form already exists
        }
    }

    /**
     * @param string $entity
     * @return string
     */
    protected function getRoutePrefix($entity)
    {
        $prefix = strtolower(str_replace(array('\\', '/'), '_', $entity));

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    /**
     * @return DoctrineManagerGenerator
     */
    protected function createGenerator()
    {
        return new DoctrineManagerGenerator($this->getContainer()->get('filesystem'));
    }

    /**
     * @param null|string $bundle
     * @return DoctrineFormGenerator
     */
    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    /**
     * @param DoctrineFormGenerator $formGenerator
     */
    public function setFormGenerator(DoctrineFormGenerator $formGenerator)
    {
        $this->formGenerator = $formGenerator;
    }

    /**
     * @param null|string $bundle
     * @return array
     */
    protected function getSkeletonDirs($bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath() . '/Resources/LazyantsDoctrineManager/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir(
            $dir = $this->getContainer()->get('kernel')->getRootdir() . '/Resources/LazyantsDoctrineManager/skeleton'
        )
        ) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
        $skeletonDirs[] = __DIR__ . '/../Resources';

        return $skeletonDirs;
    }
}

?>
