<?php defined('SYSPATH') or die('No direct script access.');
// Route for assets.
Route::set('assets', 'assets/<package>.<type>',
    array(
        'package' => '([a-f\d\-]*)',
        'type'  => '(css|js)'
    ))
    ->defaults(array(
        'controller' => 'assets',
        'action'     => 'index',
        'package'    => '',
        'type'       => '',
    ));