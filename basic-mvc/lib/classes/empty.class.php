<?php
class EmptyObject {

    public function __toString() {
        
    }
    
    public function __get($param) {
        return '';
    }
    
}