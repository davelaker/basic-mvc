<?
class Config {

  protected static $_filenames = array();
  protected static $_configs = array();

  public static function read($var = null) {
    if ($var === null) {
      return self::$_configs;
    }
    if (isset(self::$_configs[$var])) {
      return self::$_configs[$var];
    } else {
      return null;
    }
  }

  public static function write($config, $value = null, $addToSession = false) {

    if (!is_array($config)) {
      $config = array($config => $value);
    }

    foreach ($config as $name => $value) {
      self::$_configs[$name] = $value;
      if ($addToSession == true) {
        $_SESSION['_configs'][$name] = $value;
      }
    }

    return true;
  }

  public static function save($config, $value = null) {

    $db  = Database::getInstance();
    $sql = "
            INSERT INTO `configs`
            SET `cf_name` = '" . $db->mres($config) . "', `cf_value` = '" . $db->mres($value) . "'
            ON DUPLICATE KEY UPDATE `cf_value` = '" . $db->mres($value) . "'
        ";
    $db->doExecute($sql);

    self::write($config, $value, true);

    return true;
  }

  public static function load($var = null) {

    $db = Database::getInstance();

    if ($var === null) {
      $sql = "SELECT `cf_name`, `cf_value` FROM `configs`";
      $res = $db->doQuery($sql);

      $configs = array();
      while ($config = $db->fetchNextRow($res)) {
        $configs[$config->cf_name] = $config->cf_value;
      }
      return $configs;
    }
    if (isset(self::$_configs[$var])) {
      return self::$_configs[$var];
    } else {
      $sql = "SELECT `cf_value` FROM `configs` WHERE `cf_name` = '" . $db->mres($var) . "'";
      $val = $db->doQuerySingle($sql);

      if ($val === false) {
        $val = null;
      }

      self::write($var, $val, true);

      return $val;
    }
  }

  public static function addConfigs() {

    $directory = CORE_INCLUDES_PATH . DS . 'configs';
    $filenames = array();
    $iterator  = new DirectoryIterator($directory);

    $configs = array();
    if (file_exists($directory . DS . 'required.ini')) {
      $configs = array_merge($configs, self::_addINIConfigs($directory . DS . 'required.ini'));
    }
    foreach ($iterator as $fileinfo) {
      if ($fileinfo->isFile() && ($fileinfo->getFilename() != 'required.ini')) {

        $extension = pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION);
        $filename  = $fileinfo->getFilename();
        $fullPath  = $fileinfo->getPathname();

        switch ($extension) {
          case 'php' :
            $configs = array_merge($configs, self::_addPHPConfigs($fullPath));
            break;
          case 'ini' :
            $configs = array_merge($configs, self::_addINIConfigs($fullPath));
            break;
        }
        $filenames[] = array(
          'name'      => $filename,
          'extenstion'=> $extension,
        );
      }
    }
    $requiredConfigs = array('debug', 'viewTheme');
    foreach ($requiredConfigs as $requiredConfig) {
      if (empty($configs[$requiredConfig])) {
        Core::addError('Missing required config \'' . $requiredConfig . '\'');
      }
    }

    self::$_filenames = $filenames;

    # set the configs from the config files
    foreach ($configs as $configName=> $configVal) {
      self::$_configs[$configName] = $configVal;
    }


  }

  private static function _addPHPConfigs($fullPath) {

    require_once($fullPath);
    return $phpConfigs;

  }

  private static function _addINIConfigs($fullPath) {

    # Parse without sections
    $iniConfigs = parse_ini_file($fullPath);

    # Parse with sections
    # $iniConfigs = parse_ini_file($fullPath, true); # If we want to allow sections (multi dimensional array)

    return $iniConfigs;

  }

  public static function addSessionConfigs() {
    if (!empty($_SESSION['_configs'])) {
      foreach ($_SESSION['_configs'] as $name => $value) {
        self::$_configs[$name] = $value;
      }
    }
  }

}