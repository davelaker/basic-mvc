<?php
class View {
    
    public $viewVars = array();
    
    public function __construct() {
        
    }
    
    public function renderPage($controllerOptions) {
        
        $this->viewVars = $controllerOptions['viewVars'];
        $this->params = $controllerOptions['params'];
        
        $layout = $this->_getTemplate($controllerOptions['viewTemplate']);
        $view = $this->_getViewFile($controllerOptions);
        
        $page = str_replace('{VIEW-CONTENT}', $view, $layout);
        
        echo $page;
    }
    
    private function _getTemplate($viewTemplate) {

        extract($this->viewVars);
        ob_start();
        $templateFile = APP_PATH.'templates'.DS.$viewTemplate['viewTheme'].DS.$viewTemplate['viewTemplate'].'.php';
        if(!Core::checkFileExists($templateFile)) {
            $messages = array();
            $messages['theme'] = $viewTemplate['viewTheme'];
            $messages['template'] = $viewTemplate['viewTemplate'];
            $out = Core::structureError('missingTemplate', $messages, false);
        }
        else {
            ob_start();
            include($templateFile);    
            $out = ob_get_contents();
            ob_end_clean();
        }
        
        return $out;
    }
    
    private function _getViewFile($controllerOptions) {
        extract($this->viewVars);
        
        $viewFile = APP_PATH.'views'.DS.$controllerOptions['controller'].DS.$controllerOptions['method'].'.php';
        if(!Core::checkFileExists($viewFile)) {
            $messages = array();
            $messages['controller'] = $controllerOptions['controller'];
            $messages['method'] = $controllerOptions['method'];
            $out = Core::structureError('missingView', $messages, false);
        } 
        else {
            ob_start();
            include($viewFile);    
            $out = ob_get_contents();
            ob_end_clean();
        }
                
        return $out;
        
    }
    
    public function commonInclude($location, $extraViewVars = array()) {
        ob_start();
        $this->viewVars += $extraViewVars;
        extract($this->viewVars);
        include(APP_PATH.'views'.DS.'includes'.DS.$location.'.php');
        
        $out = ob_get_contents();
        ob_end_clean();
        
        return $out;
    }
}