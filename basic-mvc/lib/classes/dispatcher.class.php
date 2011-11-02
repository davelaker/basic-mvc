<?php
class Dispatcher {
    
    private $_defaultUrl = array(
        0 => 'controller',
        1 => 'method',
    );
    private $_defaultRoutes = array(
        'controller' => 'index',
        'method' => 'index',
    );
    private $_possibleRoutes = array(
        '#^/(?:([^/]+))[/]*$#', #/controller/
        '#^/(?:([^/]+))/(?:([^/]+))(?:/(?P<_args_>.*))?[/]*$#', #/countroller/action/args
    );
    
    public function __construct() {
    }
    /*
    public function addRoute($regex, $route) {
        $this->_possibleRoutes[] = array(
            'controller' => $controller,
            'method' => $method,
        );
    }*/

    public function generatePage($url) {
         
        $url = '/'.$url;
        
        $route = array();
        $route += $this->_defaultRoutes;
        $route['_args_'] = $route['params'] = array();
        foreach($this->_possibleRoutes as $possibleRoute) {
            
            if (preg_match($possibleRoute, $url, $parsed)) {
                array_shift($parsed);
                foreach ($this->_defaultUrl as $i => $key) {
                    if (isset($parsed[$i])) {
                        $route[$key] = $parsed[$i];
                    }
                }
                if (isset($parsed['_args_'])) {
                    $_args_ = $parsed['_args_'];
                    $args = explode('/', $_args_);
                    $route['_args_'] = $_args_;
                    $route['params'] = $args;
                }
                foreach ($route as $key => $value) {
                    if (is_integer($key)) {
                        $route['pass'][] = $value;
                        unset($route[$key]);
                    }
                }
                break;
            }
        }
        $controller = false;
            
		$ctrlClass = $route['controller'];
        include(APP_PATH.'models'.DS.'default_model.php');
        include(APP_PATH.'controllers'.DS.'default_controller.php');
        
        $controllerFile = APP_PATH.'controllers'.DS.$ctrlClass.'.php';
        if(!Core::checkFileExists($controllerFile)) {
            $messages = array();
            $messages['controller'] = $route['controller'];
            Core::structureError('missingController', $messages);
        }
        include($controllerFile);
        $ctrlClass .= 'Controller';
		if (class_exists($ctrlClass)) {
            $controller = new $ctrlClass();
            if (method_exists($controller, $route['method'])) {
                $controller->method = $route['method'];
                $controller->params = $route['params'];
                $controller->beforeMethod();
                $controller->{$route['method']}();
                $controller->beforeView($route);
                
                $controller->renderPage($route);
            }
            else {
                $messages = array();
                $messages['method'] = $route['method'];
                $messages['controller'] = $route['controller'];
                
                Core::structureError('missingMethod', $messages);
            }
            
		}
        else {
            $messages = array();
                $messages['controller'] = $route['controller'];
                Core::structureError('controllerNaming', $messages);
        }
        
		return $controller;
        
    }

}