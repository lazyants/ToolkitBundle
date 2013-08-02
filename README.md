LazyantsToolkitBundle
=====================

Bundle description...

Installation
------------

With [composer](http://packagist.org), add:

    {
        require: {
            "lazyants/toolkit-bundle": "dev-master"
        }
    }

Then enable it in your kernel:

    // app/AppKernel.php
    public function registerBundles()
    {
        $bundles = array(
            ...
            new Lazyants\ToolkitBundle\LazyantsToolkitBundle(),
            ...

Configuration
-------------

Sample configuration.

    # app/config/config.yml
    lazyants_toolkit:
        translation:
            bundles: [ 'FrontendBundle', 'BackendBundle' ]
            locales: [ 'de', 'en' ]

