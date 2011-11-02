<?php

function cleanForOutput($string) {

    $string = strip_tags($string);
    $string = nl2br($string);
    $string = htmlspecialchars($string);

    return $string;
}

function pr($var, $msg = false) {
    if (Config::read('debug') > 0) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        if($msg != false) die($msg);
    }
}

function _pr($var, $msg = true) {
    pr($var, $msg);
}

function vd($var, $msg = false) {
    if (Config::read('debug') > 0) {
        var_dump($var);
        if($msg != false) die($msg);
    }
}

function _vd($var, $msg = true) {
    vd($var, $msg);
}

if (!function_exists('getMicrotime')) {

    /**
     * Returns microtime for execution time checking
     *
     * @return float Microtime
     */
	function getMicrotime() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
}
?>