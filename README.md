LazyantsToolkitBundle
=====================

This bundle provides some of functionality used by a part of our projects

Installation
------------

First you need to add `lazyants/ToolkitBundle` to `composer.json`:

    {
        require: {
            ...
            "lazyants/toolkit-bundle": "dev-master"
        }
    }

or direct via composer.phar:

    ./composer.phar require lazyants/ToolkitBundle:dev-master

You also have to add `LazyantsToolkitBundle` to your `AppKernel.php`:

    // app/AppKernel.php
    ...
    class AppKernel extends Kernel
    {
        ...
        public function registerBundles()
        {
            $bundles = array(
                ...
                new Lazyants\ToolkitBundle\LazyantsToolkitBundle()
            );
            ...

            return $bundles;
        }
        ...
    }

Base template
-------------

LazyantsToolkitBundle::base.html.twig provides some abstract blocks, so you can you use it in your main layout,
otherwise you will create probably the same structure anyway.

    {% extends 'LazyantsToolkitBundle::base.html.twig' %}

Commands
--------

### Translation update command

To extract translations automatically for given bundles and locales (and delete duplicated fos user translations):

#### Sample configuration:

    # app/config/config.yml
    lazyants_toolkit:
        translation:
            bundles: [ 'FrontendBundle', 'BackendBundle' ]
            locales: [ 'de', 'en' ]

#### Command:

    ./app/console lazyants:translation:update

### Generate manager

To generate manager (controller, view and form):

#### Command:

    ./app/console lazyants:generate:manager --entity=ENTITY_NAME

#### Arguments:

--entity: Mandatory value. The entity class name to initialize (shortcut notation)

--overwrite: Optional value. Do not stop the generation if crud controller already exist, thus overwriting all generated files

--actions: Optional value. List of actions in controller.
You will need this option on order to add new methods to controller or remove some from it.
Multiple value are possible, f.e.: --actions=index --actions=add --actions=manage --actions=delete

#### Customization:

In order to customize generated content, copy ToolkitBundle/Resources/skeleton
into app/Resources/LazyantsDoctrineManager/skeleton or YOUR_BUNDLE/Resources/LazyantsDoctrineManager/skeleton.
You can now customize controller, views or form. For actions without own view (by default there ist two - new and delete)
simply don't create view files. Actions will be still created, if they are present in controller template and
provided with --actions. In case of adding new actions or removing some of existing, you should always provide this argument.