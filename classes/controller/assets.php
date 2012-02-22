<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Assets extends Controller
{
    public function action_index()
    {
        $r     = $this->request;
        $type  = $r->param('type');
        $package  = $r->param('package');
        $ass      = Assets::instance();
        
        try {
            $file_path = $ass->generate_package_file($type, $package);
        } catch (Exception $e) {
            $this->response->status(404);
            $this->response->send_headers();
            $this->response->body("Package Not Found! 404'd");
            return;
        }
        
        $content = file_get_contents($file_path);
        
        $this->response->headers('Content-Type', $ass->resolve_content_type($type));
        $this->response->body($content);
    }
}