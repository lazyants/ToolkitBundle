<?php

namespace Lazyants\ToolkitBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class TranslationUpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('lazyants:translation:update')
            ->setDescription(
                'Update translations with common options using native translation:update command. Be careful, this command delete custom fos translation'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->getApplication()->find('translation:update');

        $bundles = $this->getContainer()->getParameter('lazyants_toolkit.translation.bundles');
        $locales = $this->getContainer()->getParameter('lazyants_toolkit.translation.locales');

        $existingBundles = $this->getApplication()->getKernel()->getBundles();

        $fs = new Filesystem();

        foreach ($bundles as $bundle) {
            foreach ($locales as $locale) {
                $arguments = array(
                    'command' => 'translation:update',
                    '--force' => true,
                    'locale' => $locale,
                    'bundle' => $bundle
                );
                $command->run(new ArrayInput($arguments), $output);

                # remove unnecessary fos translations
                $fosPath =
                    $existingBundles[$bundle]->getPath() .
                    '/Resources/translations/FOSUserBundle.' . $locale . '.yml';

                if ($fs->exists($fosPath)) {
                    try {
                        $fs->remove($fosPath);
                    } catch (IOException $e) {
                        echo "An error occurred while removing fos translation file";
                    }
                }
            }
        }
    }
}
