<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
    'is_minify'      => TRUE,                // Enables minifcation
    'dir_source'     => DOCROOT . 'static/', // Source directory to build assets and manifest from
    'dir_build'      => DOCROOT . 'build/',  // Directory to write the packages or minified files to.
    'enable_logging' => TRUE,                // Enables logging
    'is_dev'         => TRUE,                // If enabled it will rebuild the manifest every time on instantiation.
);
