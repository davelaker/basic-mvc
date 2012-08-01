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

  public function generatePage($url) {

    $url = '/'.$url;

    $route = array();
    $route += $this->_defaultRoutes;
    $route['_args_'] = $route['params'] = array();

    $customRoutes = Config::read('custom_routes');
    $foundCustom = false;
    if(is_array($customRoutes)) {
      foreach($customRoutes as $customRoute=>$route_setup) {
        if (preg_match($customRoute, $url, $parsed)) {

          $foundCustom = true;
          array_shift($parsed);

          if(is_int($route_setup['controller'])) {
            $route['controller'] = $parsed[$route_setup['controller']];
          } else {
            $route['controller'] = $route_setup['controller'];
          }
          if(is_int($route_setup['method'])) {
            $route['method'] = $parsed[$route_setup['method']];
          } else {
            $route['method'] = $route_setup['method'];
          }
          if (isset($parsed['_args_'])) {
            $_args_ = $parsed['_args_'];
            $args = explode('/', $_args_);
            $route['_args_'] = $_args_;
            $route['params'] = $args;
          }
          break;
        }
      }
    }

    if($foundCustom === false) {

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
    }
    $controller = false;

    $ctrlClass = $route['controller'];
    include(APP_PATH.'models'.DS.'default_model.php');
    include(APP_PATH.'controllers'.DS.'default_controller.php');

    $controllerFile = APP_PATH.'controllers'.DS.$ctrlClass.'.php';
    if(!Core::checkFileExists($controllerFile)) {
      $messages = array();
      $messages['controller'] = $route['controller'];
      Core::fatalError('missingController', $messages);
    }
    include($controllerFile);
    $ctrlClass .= 'Controller';
    if (class_exists($ctrlClass)) {
      $controller = new $ctrlClass();
      if (method_exists($controller, $route['method'])) {
        $controller->method = $route['method'];
        $controller->params = $route['params'];
        $controller->getVars = $this->handleGetVars();
        $controller->postVars = $this->handlePostVars();
        $controller->beforeMethod();
        $controller->{$route['method']}();
        $controller->beforeView($route);

        $controller->renderPage($route);
      }
      else {
        $messages = array();
        $messages['method'] = $route['method'];
        $messages['controller'] = $route['controller'];

        Core::fatalError('missingMethod', $messages);
      }

    }
    else {
      $messages = array();
      $messages['controller'] = $route['controller'];
      Core::fatalError('controllerNaming', $messages);
    }

    return $controller;

  }

  private function handleGetVars() {
    return $_GET;
  }

  private function handlePostVars() {
    return $_POST;
  }

}