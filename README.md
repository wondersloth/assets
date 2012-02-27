# Assets


## TL;DR

A [Kohana](http://kohanaframework.org/) 3.1+ module to **package** and and **minify** CSS and JavaScript files.

It works by pre-processing **all** your CSS and JavaScript files and builds a map of these files and stores it in your cache (defaults to file cache). This would be best incorporated into a build / deploy step.

What Assets does:

 * Provides unique package urls based on the contents of the files. So you don't have to worry about cache busting on the client. 

 * Packages are constructed when the *first* request is made to it from the client and then saved on the server in a specified output directory.
 
 * After a package has been constructed, it is no longer recompiled and the compiled file is now served straight from Apache.

 * All PHP no external dependencies, like java for minification!

## Required

 * Kohana 3.1+
 * Cache module enabled and working in some fashion.
 * A single directory where all the CSS/JS file are sourced.
 * An output directory that exists that doesn't overlap with the existing CSS/JS directory.

## Usage

In your controller, add the files you want on the page.

    <?php defined('SYSPATH') or die('No direct script access.');

    class Controller_Home extends Controller {

        public function action_index()
        {
            Assets::instance()
                ->add_css('css/file1.css')
                ->add_css('css/file2.css')
                ->add_css('css/file3.css')
                ->add_css('css/a/b/c/file.css')
                ->add_js('js/a.js')
                ->add_js('js/b.js')
                ->add_js('js/c.js')
                ->add_js('js/x/y/z.js');
        
            $this->response->body(View::factory('index')->render());
        }
        
    }


By default all files are added to the `default` package. Though you can specify a unique package in the second parameter.

    Assets::instance()->add_css('path/to/yourfile.css, 'other-package');

Then in your template simply call.

    <!DOCTYPE html>

    <html>
    <head>
    	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    	<title>Test Homepage</title>
    	<? if (Assets::instance()->has_css_files()) : ?>
        <link rel="stylesheet" href="<?= Assets::instance()->get_css_package_url() ?>" type="text/css" media="screen" title="core" charset="utf-8">
        <? endif; ?>
    </head>

    <body>
        <h1>Hello World</h1>
        <p>
            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod 
            tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim 
            veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
            commodo consequat. Duis aute irure dolor in reprehenderit in voluptate 
            velit esse cillum dolore eu fugiat nulla pariatur.
        </p>
        <p>
            Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
            officia deserunt mollit anim id est laborum.
        </p>
        <? if (Assets::instance()->has_js_files()) : ?>
        <script type="text/javascript" src="<?= Assets::instance()->get_js_package_url() ?>"></script>
        <? endif; ?>
    </body>
    </html>


This snippet of code will check if there are any files in the package then grab the package url.

The package url would something like this:

    <link rel="stylesheet" href="/assets/970d7f-03d8fc-6748ad-1724e5.css" type="text/css" media="screen" title="core" charset="utf-8">

## Config

Place the module in your `modules` directory.


### application/bootstrap.php

Add the `assets` module `boostrap.php` file, if you haven't enabled and configured the `cache` module you should do that now.

    <?php
    
    // ...
    
    Kohana::modules(array(
        'assets'     => MODPATH.'assets',        // Asset package and minification
    	// 'auth'       => MODPATH.'auth',       // Basic authentication
        'cache'      => MODPATH.'cache',         // Caching with multiple backends
    	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    	// 'database'   => MODPATH.'database',   // Database access
    	// 'image'      => MODPATH.'image',      // Image manipulation
    	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
    	// 'unittest'   => MODPATH.'unittest',   // Unit testing
    	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
    	));

### modules/assets/config/assets.php

This is the modules config:

    <?php defined('SYSPATH') or die('No direct access allowed.');

    return array(
        'is_minify'      => TRUE,                // Enables minifcation
        'dir_source'     => DOCROOT . 'static/', // Source directory to build assets and manifest from
        'dir_build'      => DOCROOT . 'build/',  // Directory to write the packages or minified files to.
        'enable_logging' => TRUE,                // Enables logging
        'always_build_manifest'         => TRUE,                // If enabled it will rebuild the manifest every time on instantiation.
    );

## Appendix

### Cache
The cache is bi-directional map. This allows us to grab the hash for a given file at render time in the view **or** grab the file for a given hash when creating the package.

### Package name

A package name represents the files that are in it. The package is a series of alphanumeric characters separated by hyphens.

    970d7f-03d8fc-6748ad-1724e5.css

These hashes `970d7f`, `03d8fc`, `6748ad` and `1724e5` would then be mapped to files found in the cached map.













