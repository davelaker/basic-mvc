<?php
class Core {
    
    public static function newInstance($type, $class, $instantiate = true) {
        switch($type) {
            case 'Model' :
                require_once('models'.DS.$class.'.php');
                $modelClass = $class.'Model';
                if($instantiate === true) return new $modelClass();
                break;
            case 'Plugin' :
                require_once('plugins'.DS.$class.'.class.php');
                $folders = explode('/', $class);
                if(!empty($folders)) {
                    $class = end($folders);
                }
                $pluginClass = ucwords($class);
                if($instantiate === true) return new $pluginClass();
                break;
        }

        return true;
    }
    
    /**
     * Check if a file exists in the include path
     * And if it does, return the absolute path.
     * @param string $filename
     *  Name of the file to look for
     * @return string|false
     *  The absolute path if file exists, false if it does not
     */
    public static function checkFileExists($filename) {
        // Check for absolute path
        if (realpath($filename) == $filename) {
            return $filename;
        }
        
        // Otherwise, treat as relative path
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            if (substr($path, -1) == DS) {
                $fullpath = $path.$filename;
            } else {
                $fullpath = $path.DS.$filename;
            }
            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }

        return false;
    }
    
    /**
     * Output error message stored in lib/errors
     * 
     * @param string $error
     * @param array $messages
     * @param bool $die
     * 
     * @return string 
     */
    public static function structureError($error, $messages, $die = true) {
        
        ob_start();
        
        switch($error) {
            case 'missingController':
                $controller = $messages['controller'];
                $errorFile = APP_PATH.'lib'.DS.'errors'.DS.'missing_controller.php';
                self::addError('Missing Controller \''.$controller.'\'');
                break;
            case 'missingMethod':
                $controller = $messages['controller'];
                $method = $messages['method'];
                $errorFile = APP_PATH.'lib'.DS.'errors'.DS.'missing_method.php';
                self::addError('Missing Method \''.$method.'\' for Controller \' '.$controller.'\'');
                break;
            case 'missingTemplate':
                $theme = $messages['theme'];
                $template = $messages['template'];
                $errorFile = APP_PATH.'lib'.DS.'errors'.DS.'missing_template.php';
                self::addError('Missing Templete \''.$theme.'\' for Theme \' '.$theme.'\'');
                break;
            case 'missingView':
                $controller = $messages['controller'];
                $method = $messages['method'];
                $errorFile = APP_PATH.'lib'.DS.'errors'.DS.'missing_view.php';
                self::addError('Missing View \''.$method.'\' for Controller \' '.$controller.'\'');
                break;
            case 'controllerNaming':
                $controller = $messages['controller'];
                $errorFile = APP_PATH.'lib'.DS.'errors'.DS.'controller_naming.php';
                self::addError('Incorrect class naming for Controller \' '.$controller.'\'');
                break;
        }
        require($errorFile);
        
        $out = ob_get_contents();
        ob_end_clean();
        
        if($die === true) {
            die($out);
        }
        else {
            return $out;
        }
        
    }
    
    public static function addError($message) {
        $errors = Config::read('_mvc_errors');
        if(is_null($errors)) {
            $errors = array($message);
        }
        else {
            $errors[] = $message;
        }
        Config::write('_mvc_errors', $errors);
    }
        
}