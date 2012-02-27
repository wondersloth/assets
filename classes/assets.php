<?php
/**
* 
*/

require_once MODPATH . 'assets/lib/cssmin/cssmin.php';
require_once MODPATH . 'assets/lib/jsmin/jsmin.php';

class Assets
{
    const FILE_SEPERATOR = '-';
        
    private static $instance;
    private $data;
    
    public function __construct()
    {
        $this->config = Kohana::config('assets');
        
        // Confirm that the build directory is writeable
        if (!is_writable($this->config['dir_build'])) {
            throw new Kohana_Exception ($this->config['dir_build'].' must be writable.');
        }
        
        $this->data['css'] = array();
        $this->data['js']  = array();
        
        // Load Manifest
        $is_success = $this->load_manifest();
        
        if (!$is_success || $this->config->get('always_build_manifest')) {
            $this->build_manifest();
        }
    }
    
    public static function instance()
    {
        if (!is_object(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class();
        }
        
        return self::$instance;
    }
    
    public function add($type, $path, $package = 'default')
    {
        if (empty($type)) {
            throw new AssetsException('Unable to add asset. Invalid type.');
        }
        
        if (!isset($this->data[$type][$package])) {
            $this->data[$type][$package] = array();
        }
        
        array_push($this->data[$type][$package], $path);
        
        return self::instance();
    }
    
    public function add_css($path, $package = 'default')
    {
        return $this->add('css', $path, $package);
    }
    
    public function add_js($path, $package = 'default')
    {
        return $this->add('js', $path, $package);
    }
    
    public function has_files($type, $package = 'default')
    {
        return !empty($this->data[$type][$package]);
    }
    
    public function has_css_files($package = 'default')
    {
        return $this->has_files('css', $package);
    }
    
    public function has_js_files($package = 'default')
    {
        return $this->has_files('js', $package);
    }
    
    public function get_package_url($type, $package = 'default')
    {
        if (!isset($this->data[$type][$package])) {
            throw new AssetsException("Invalid $type package name '$package'.");
        }
        
        $files = $this->data[$type][$package];
        
        $package = $this->encode_package($files);
        
        $url = "/assets/$package.$type";
        
        return $url;
    }
    
    public function get_css_package_url($package = 'default')
    {
        return $this->get_package_url('css', $package);
    }
    
    public function get_js_package_url($package = 'default')
    {
        return $this->get_package_url('js', $package);
    }
    
    public function get_package_list($type, $package_priority = array())
    {
        $packages = array_keys($this->data[$type]);
        
        if (!empty($package_priority)) {
            $packages = array_unique(array_merge($package_priority, $packages));
        }
        
        return $packages;
    }
    
    public function get_css_package_list($package_priority = array())
    {
        return $this->get_package_list('css', $package_priority);
    }
    
    public function get_js_package_list($package_priority = array())
    {
        return $this->get_package_list('js', $package_priority);
    }
    
    public function get_package($type, $package = 'default')
    {
        $packages = &$this->data[$type];
        return array_key_exists($package, $packages) ? $packages[$package] : array();
    }
    
    public function get_css_package($package = 'default')
    {
        return $this->get_package('css', $package);
    }
    
    public function get_js_package($package = 'default')
    {
        return $this->get_package('js', $package);
    }
    
    public function get_build_dir($type)
    {
        return $this->config->get("dir_build");
    }
    
    public function get_css_build_dir()
    {
        return $this->get_build_dir('css');
    }
    
    public function get_js_build_dir()
    {
        return $this->get_build_dir('js');
    }
    
    public function encode_package($files)
    {
        $hashes = array();
        
        $is_minify = $this->config->get('is_minify');
        
        foreach ($files as $file) {
            $hashes[] = $this->get_hash_by_file($file);
        }
        
        return implode(self::FILE_SEPERATOR, $hashes);
    }
    
    public function decode_package($package)
    {
        $hashes = explode(self::FILE_SEPERATOR, $package);
        
        $files = array();
        
        foreach ($hashes as $hash) {
            $files[] = $this->get_file_by_hash($hash);
        }
        
        return $files;
    }
    
    public function get_hash_by_file($file)
    {
        if (!isset($this->manifest['by_file'][$file])) {
            throw new AssetsException("Invalid file ${file}, unable to get hash."); 
        }
        
        return $this->manifest['by_file'][$file];
    }
    
    public function get_file_by_hash($hash)
    {
        if (!isset($this->manifest['by_hash'][$hash])) {
            throw new AssetsException("Invalid hash ${hash}, unable to find file."); 
        }
        
        return $this->manifest['by_hash'][$hash];
    }
    
    public function resolve_content_type($type)
    {
        switch ($type) {
            case 'css':
                $content_type = 'text/css';
                break;
            case 'js':
                $content_type = 'text/javascript';
                break;
            default:
                throw new AssetException('Invalid content type.');
                break;
        }
        
        return $content_type;
    }
    
    public function generate_package_file($type, $package)
    {
        $files             = $this->decode_package($package);        
        $content           = $this->generate_file($type, $files);
        $package_file_path = $this->get_build_dir($type) . $package . '.' . $type;
        
        file_put_contents($package_file_path, $content);
        
        return $package_file_path;
    }
    
    public function generate_file($type, $files)
    {
        $key = $this->config->get('is_minify') ? "dir_build" : "dir_source";
        
        $dir = $this->config->get($key);
        
        $content = "/* Generated $type file. TS " . date('YMd H:i:s') . " */\n";
        
        foreach ($files as $file) {
            $content .= "\n/*** File: $file ***/\n";
            
            if ($this->config->get('is_minify')) {
                $file = str_ireplace(".$type", ".min.$type", $file);
            }
            
            
            $full_path = $dir . $file;
            
            $content .= file_get_contents($full_path);
        }
        
        return $content;
    }
    
    public function generate_css_file($files, $args = array())
    {
        return $this->generate_file('css', $files, $args);
    }
    
    public function generate_js_file($files, $args = array())
    {
        return $this->generate_file('js', $files, $args);
    }
    
    public function build_manifest()
    {
        if ($this->config->get('enable_logging')) {
            Log::instance()->add(Log::NOTICE, '[Assets] Building manifest started.');
        }
        
        $dir_source = $this->config->get('dir_source');
        $dir_build = $this->config->get('dir_build');
        
        $iterator   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_source));
        
        $manifest = array();
        
        foreach ($iterator as $full_path => $object) {
            $path_info = pathinfo($full_path);
            
            $type = $path_info['extension'];
            
            if ( $type !== 'css' and $type !== 'js') { continue; }
            
            $rel_path = substr($full_path, strlen($dir_source));
            
            // Minify css or js files here. Then take an MD5 of the content.
            
            if ($this->config->get('is_minify')) {
                $content = file_get_contents($full_path);
                
                // Minification
                switch ($type) {
                    case 'css':
                        $content = CSSMin::minify($content);
                        break;
                    case 'js':
                        $content = JSMin::minify($content);
                        break;
                    default:
                        continue;
                        break;
                }
                
                $min_rel_path = str_ireplace(".$type", ".min.$type", $rel_path);
                
                $min_full_path = $dir_build . $min_rel_path;

                $path_info = pathinfo($min_full_path);
                $dir = $path_info['dirname'];

                if (!is_dir($dir)) { mkdir($dir, 0775, TRUE); }

                file_put_contents($min_full_path, $content);
                
                $path = $rel_path;
                $hash = md5($content);
            }
            else {
                $path = $rel_path;
                $hash = md5_file($full_path);
            }
            
            // Shorten the hash, we don't need all of it.
            $short_hash = substr($hash, 0, 6);
            
            $this->manifest['by_hash'][$short_hash] = $path;
            $this->manifest['by_file'][$path]       = $short_hash;
        }
        
        Cache::instance()->set('assets_manifest', $this->manifest);
        
        if ($this->config->get('enable_logging')) {
            Log::instance()->add(Log::NOTICE, '[Assets] Building manifest finished.');
        }
        
        return $this;
    }
    
    public function delete_manifest () {
        if ($this->config->get('enable_logging')) {
            Log::instance()->write('[Assets] Deleting manifest.');
        }
        
        Cache::instance()->delete('assets_manifest');
        
        return TRUE;
    }
    
    public function load_manifest()
    {
        $manifest = Cache::instance()->get('assets_manifest', FALSE);
        
        if ($manifest === FALSE) {
            if ($this->config->get('enable_logging')) {
                Log::instance()->add(Log::WARNING,'[Assets] Failed to load manifest.');
            }
            
            return FALSE;
        }
        
        return $this->manifest = $manifest;
    }
}