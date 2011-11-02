<?
class Config {
    
    protected static $_configs = array(
        'debug' => '1',
        'db_host' => 'localhost',
        'db_user' => '',
        'db_pass' => '',
        'db_name' => '',
        'viewTheme' => 'default',
    );
        
    public static function read($var = null) {
		if ($var === null) {
			return self::$_configs;
		}
		if (isset(self::$_configs[$var])) {
			return self::$_configs[$var];
		}
        else {
			return null;
		}
    }
    
    public static function write($config, $value = null, $addToSession = false) {
        
		if (!is_array($config)) {
			$config = array($config => $value);
		}

		foreach ($config as $name => $value) {
			self::$_configs[$name] = $value;
            if($addToSession == true) {
                $_SESSION['_configs'][$name] = $value;
            }
		}

		return true;
	}
    
    public static function addSessionConfigs() {
        if(!empty($_SESSION['_configs'])) {
            foreach ($_SESSION['_configs'] as $name => $value) {
                self::$_configs[$name] = $value;
            }
        }
    }
    
}