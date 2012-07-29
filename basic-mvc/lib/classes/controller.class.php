<?php
class Controller {

  private $viewVars = array();
  private $hrefs = array();
  private $tempVars = array();
  private $viewTemplate = array();
  protected $models = array();

  public function __construct() {
    $this->_setUpVars();
    $this->_setUpModels();
    $this->_handleTempVars();
    $this->setTemplate();
  }

  private function _setUpVars() {
    $defaultVars = get_class_vars('DefaultController');

    # models
    if (!is_array($this->models)) {
      $this->models = array($this->models);
    }
    if (isset($defaultVars['models'])) {
      if (is_array($defaultVars['models'])) {
        $this->models = array_merge($this->models, $defaultVars['models']);
      } else {
        $this->models[] = $defaultVars['models'];
      }
    }
    $this->models = array_unique($this->models);
    # end models
  }

  private function _setUpModels() {

    foreach ($this->models as $model) {
      include('models' . DS . $model . '.php');

      $modelClass              = $model . 'Model';
      $this->{ucwords($model)} = new $modelClass();
    }
  }

  public function beforeLoad() {

  }

  public function beforeMethod() {

  }

  public function beforeView() {

  }

  protected function setViewVar($name, $value) {
    $this->viewVars[$name] = $value;
  }

  protected function addLink($name, $value) {
    $this->addLinks(array($name => $value));
  }

  protected function addLinks(array $links) {
    foreach ($links as $k => $v) {
      $this->hrefs[$k] = $v;
    }
  }

  protected function isViewVarSet($name) {

    $set = idx($this->viewVars, $name, false);
    if ($set !== false) {
      $set = true;
    }
    return $set;
  }

  protected function setTemplate($templateName = 'default') {
    $template           = array(
      'viewTheme'    => Config::read('viewTheme'),
      'viewTemplate' => $templateName,
    );
    $this->viewTemplate = $template;
  }

  public function renderPage($route) {

    $controller = $route['controller'];
    $method     = $route['method'];
    $params     = $route['params'];

    $opts = array(
      'controller'   => $controller,
      'method'       => $method,
      'params'       => $params,
      'viewVars'     => $this->viewVars,
      'hrefs'        => $this->hrefs,
      'viewTemplate' => $this->viewTemplate,
    );

    $viewObj = new View();
    $viewObj->renderPage($opts);
  }

  protected function redirect($url) {
    header("Location: " . $url);
    exit();
  }

  private function _handleTempVars() {
    create_idx($_SESSION, 'tempVars');
    foreach ($_SESSION['tempVars'] as $k => $v) {
      $this->tempVars[$k] = $v;
    }

    $_SESSION['tempVars'] = array();
  }

  protected function getTempVar($name, $default = '') {
    return idx($this->tempVars, $name, $default);
  }

  protected function getTempVars() {
    return $this->tempVars;
  }

  protected function addTempVar($name, $val) {
    $this->addTempVars(array($name => $val));
  }

  protected function addTempVars($vars) {
    foreach ($vars as $k => $v) {
      $_SESSION['tempVars'][$k] = $v;
    }
  }

}