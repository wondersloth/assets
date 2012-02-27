<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
    'is_minify'      => TRUE,                // Enables minifcation
    'dir_source'     => DOCROOT . 'static/', // Source directory to build assets and manifest from
    'dir_build'      => DOCROOT . 'assets/',  // Directory to write the packages or minified files to. This must match the controller..
    'enable_logging' => TRUE,                // Enables logging
    'always_build_manifest'         => TRUE,                // If enabled it will rebuild the manifest every time on instantiation.
);
