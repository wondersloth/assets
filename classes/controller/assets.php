<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Assets extends Controller
{
    public function action_index()
    {
        $r     = $this->request;
        $type  = $r->param('type');
        $package  = $r->param('package');
        $a        = Assets::instance();
        
        $files = $a->decode_package($package);
        
        try {
            $content = $a->generate_file($type, $files, array('type' => $type, 'minify' => TRUE));
        } catch (Exception $e) {
            $this->response->status(404);
            $this->response->send_headers();
            $this->response->body("File Not Found! 404'd");
            return;
        }
        
        $content_type = $a->resolve_content_type($type);        
        $assets_dir   = $a->get_output_dir($type);
        
        $package_file_path = $assets_dir . $package . '.' . $type;
                
        file_put_contents($package_file_path, $content);
        // var_dump($assets_dir, $package);
        
        $this->response->headers('Content-Type', $content_type);
        $this->response->body($content);
    }
}