<?php
class Controller {
    
    private $viewVars = array();
    private $viewTemplate = array();
    protected $models = array();

    public function __construct() {
        $this->_setUpVars();
        $this->_setUpModels();
        $this->setTemplate();
        
    }
    
    private function _setUpVars() {
        $defaultVars = get_class_vars('DefaultController');
        
        # models
        if(!is_array($this->models)) {
            $this->models = array($this->models);
        }
        if(isset($defaultVars['models'])) {
            if(is_array($defaultVars['models'])) {
                $this->models = array_merge($this->models, $defaultVars['models']);
            }
            else {
                $this->models[] = $defaultVars['models'];
            }
        }
        $this->models = array_unique($this->models);
        # end models
    }
    
    private function _setUpModels() {
                
        foreach($this->models as $model) {
            include('models'.DS.$model.'.php');
            $modelClass = $model.'Model';
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
    
    protected function setTemplate($templateName = 'default') {
        $template = array(
            'viewTheme' => Config::read('viewTheme'),
            'viewTemplate' => $templateName,
        );
        $this->viewTemplate = $template;
    }
    
    public function renderPage($route) {
        
        $controller = $route['controller'];
        $method = $route['method'];
        $params = $route['params'];
                
        $opts = array();
        $opts['controller'] = $controller;
        $opts['method'] = $method;
        $opts['params'] = $params;
        $opts['viewVars'] = $this->viewVars;
        $opts['viewTemplate'] = $this->viewTemplate;
        
        $viewObj = new View();
        $viewObj->renderPage($opts);
    }

}