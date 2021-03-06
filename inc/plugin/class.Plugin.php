<?php
/**
 * This file contains class::Plugin
 * @package Runalyze\Plugin
 */
/**
 * Abstract class for Plugins
 * @author Hannes Christiansen
 * @package Runalyze\Plugin
 */
abstract class Plugin {
	/**
	 * Enum for plugin-type: Statistic
	 * @var int
	 */
	public static $STAT = 0;
	
	/**
	* Enum for plugin-type: Panel
	* @var int
	*/
	public static $PANEL = 1;
	
	/**
	* Enum for plugin-type: Tool
	* @var int
	*/
	public static $TOOL = 2;

	/**
	* Enum for active-flag: hidden
	* @var int
	*/
	public static $ACTIVE_NOT = 0;

	/**
	* Enum for active-flag: active
	* @var int
	*/
	public static $ACTIVE = 1;

	/**
	* Enum for active-flag: various/clapped
	* @var int
	*/
	public static $ACTIVE_VARIOUS = 2;

	/**
	 * Url for displaying the install-window
	 * @var string
	 */
	public static $INSTALL_URL = 'call/call.Plugin.install.php';

	/**
	 * Url for displaying the config-window
	 * @var string
	 */
	public static $CONFIG_URL = 'call/call.Plugin.config.php';

	/**
	 * Url for displaying the plugin
	 * @var string
	 */
	public static $DISPLAY_URL = 'call/call.Plugin.display.php';

	/**
	 * CSS-Flag for plugins: Don't reload if config has changed
	 * @var string
	 */
	public static $DONT_RELOAD_FOR_CONFIG_FLAG = 'dont-reload-for-config';

	/**
	 * CSS-Flag for plugins: Don't reload if a training has changed
	 * @var string
	 */
	public static $DONT_RELOAD_FOR_TRAINING_FLAG = 'dont-reload-for-training';

	/**
	 * Id for not installed plugin
	 * @var int
	 */
	public static $INSTALLER_ID = -1;

	/**
	 * Array with all plugin keys
	 * @var array
	 */
	private static $ALL_KEYS = array();

	/**
	 * Internal ID from database
	 * @var int
	 */
	protected $id;

	/**
	 * Pluginkey (classname)
	 * @var string
	 */
	public $key;

	/**
	 * Internal plugin-type-id
	 * @var int
	 */
	protected $type;

	/**
	 * Integer flag: Is this statistic acitve?
	 * @var int
	 */
	protected $active;

	/**
	 * Integer position of plugins
	 * @var int
	 */
	protected $order;

	/**
	 * Array with all config vars
	 * @var array
	 */
	protected $config;

	/**
	 * Name of this plugin
	 * @var string
	 */
	protected $name;

	/**
	 * Description
	 * @var string
	 */
	protected $description;

	/**
	 * Internal sport-ID from database
	 * @var int
	 */
	protected $sportid;

	/**
	 * Displayed year
	 * @var int
	 */
	protected $year;

	/**
	 * Internal data from database
	 * @var array
	 */
	protected $dat;

	/**
	 * Method for initializing everything (implemented in each plugin)
	 */
	abstract protected function initPlugin();

	/**
	 * Method for initializing default config-vars (implemented in each plugin)
	 */
	abstract protected function getDefaultConfigVars();

	/**
	 * Includes the plugin-file for displaying the plugin (implemented in subclass)
	 */
	abstract public function display();

	/**
	 * Inits all data (implemented in each plugin)
	 */
	protected function prepareForDisplay() {}

	/**
	 * Method for displaying the content (implemented in each plugin)
	 */
	abstract protected function displayContent();

	/**
	 * Returns the html-link to this plugin for tab-navigation
	 * @return string
	 */
	abstract public function getLink();

	/**
	 * Returns the html-link for inner-html-navigation
	 * @param string $name displayed link-name
	 * @param int $sport id of sport, default $this->sportid
	 * @param int $year year, default $this->year
	 * @param string $dat optional dat-parameter
	 * @return string
	 */
	abstract protected function getInnerLink($name, $sport = 0, $year = 0, $dat = '');

	/**
	 * Display description, can be overwritten for displaying a longer description 
	 */
	protected function displayLongDescription() {
		echo HTML::p($this->description);
	}

	/**
	 * Get an instance for a given pluginkey (starting with 'RunalyzePlugin');
	 * @param string $PLUGINKEY
	 * @return Plugin
	 */
	static public function getInstanceFor($PLUGINKEY) {
		$pluginFile = self::getFileForKey($PLUGINKEY);

		if ($pluginFile === false) {
			Error::getInstance()->addError('Can\'t find plugin-file or -directory in system: '.$PLUGINKEY);
			return false;
		}

		include_once $pluginFile;

		if (!class_exists($PLUGINKEY)) {
			Error::getInstance()->addError('The plugin-file must contain class::'.$PLUGINKEY.'.');
			return false;
		} else {
			$dat = DB::getInstance()->query('SELECT `id` FROM `'.PREFIX.'plugin` WHERE `key`='.DB::getInstance()->escape($PLUGINKEY).' LIMIT 1')->fetch();
			if ($dat === false)
				$id = self::$INSTALLER_ID;
			else
				$id = $dat['id'];

			$Plugin =  new $PLUGINKEY($id);
			$Plugin->key = $PLUGINKEY;
            $Plugin->initPlugin();

			return $Plugin;
		}
	}

	/**
	 * Get array with all avaiable plugins for installation
	 * @return array
	 */
	static public function getPluginsToInstallAsArray() {
		$plugins   = array();
		$dir = opendir(FRONTEND_PATH.'../plugin/');
		while ($file = readdir($dir)) {
			if (substr($file, 0, 6) == 'class.')
				$key = substr($file, 6, -4);
			elseif (strpos($file, '.') === false && is_dir(FRONTEND_PATH.'../plugin/'.$file))
				$key = $file;
			else
				continue;

			if (!self::isInstalled($key))
				$plugins[] = array('key' => $key);
		}

		closedir($dir);

		return $plugins;
	}

	/**
	 * Get link to the window for installing this plugin
	 * @param string $name
	 * @return string
	 */
	final public function getInstallLink($name = '') {
		if ($name == '')
			$name = Icon::$ADD;

		return Ajax::window('<a href="'.self::$INSTALL_URL.'?key='.$this->key.'">'.Ajax::tooltip($name, __('Install plugin') ).'</a>');
	}

	/**
	 * Install a new plugin
	 * @param string $key
	 */
	static public function installPlugin($key) {
		$file = self::getFileForKey($key);

		if ($file === false) {
			Error::getInstance()->addError('Pluginfile for \''.$key.'\' can\'t be found. Installing impossible.');
			return false;
		}

		include_once $file;

		if (!isset($PLUGINKEY)) {
			Error::getInstance()->addError('$PLUGINKEY must be set in the pluginfile \''.$file.'\'.');
			return false;
		} elseif (substr($PLUGINKEY, 0, 14) != 'RunalyzePlugin') {
			Error::getInstance()->addError('$PLUGINKEY must start with \'RunalyzePlugin\', but it is\''.$PLUGINKEY.'\'.');
			return false;
		}

		$Plugin = self::getInstanceFor($PLUGINKEY);
		return $Plugin->install();
	}

	/**
	 * Uninstall plugin
	 * @param string $key 
	 */
	static public function uninstallPlugin($key) {
		DB::getInstance()->exec('DELETE FROM `'.PREFIX.'plugin` WHERE `key`='.DB::getInstance()->escape($key).' LIMIT 1');
	}

	/**
	 * Install this plugin
	 * @return bool
	 */
	final public function install() {
		if ($this->id != self::$INSTALLER_ID) {
			Error::getInstance()->addError('Plugin can not be installed, id is set wrong.');
			return false;
		}

		$columns = array(
			'key',
			'type',
			'name',
			'description',
			'order',
			);
		$values  = array(
			$this->key,
			self::getTypeString($this->type),
			$this->name,
			$this->description,
			'99',
			);

		$this->id = DB::getInstance()->insert('plugin', $columns, $values);
		$this->config = $this->getDefaultConfigVars();

		$this->setActive(1);
		$this->updateConfigVarToDatabase();

		return true;
	}

	/**
	 * Initialize all variables
	 */
	final protected function initVars() {
		if ($this->id == self::$INSTALLER_ID)
			return;

		$dat = DB::getInstance()->fetchByID('plugin', $this->id);

		$this->key         = $dat['key'];
		$this->active      = $dat['active'];
		$this->order       = $dat['order'];
		$this->name        = $dat['name'];
		$this->description = $dat['description'];
		$this->sportid     = $this->defaultSport();
		$this->year        = $this->defaultYear();
		$this->dat         = '';

		if (isset($_GET['sport']))
			if (is_numeric($_GET['sport']))
				$this->sportid = $_GET['sport'];
		if (isset($_GET['jahr']))
			if (is_numeric($_GET['jahr']))
				$this->year = $_GET['jahr'];
		if (isset($_GET['dat']))
			$this->dat = $_GET['dat'];

		$this->initConfigVars($dat['config']);
		$this->checkConfigVarsForChanges();
	}

	/**
	 * Default sport
	 * 
	 * May be overwritten in subclass.
	 * Default setting: CONF_MAINSPORT
	 * 
	 * @return int sportid, can be -1 for all sports
	 */
	protected function defaultSport() {
		return CONF_MAINSPORT;
	}

	/**
	 * Default year
	 * 
	 * May be overwritten in subclass.
	 * Default setting: current year
	 * 
	 * @return int year, can be -1 for no year/comparison of all years
	 */
	protected function defaultYear() {
		return date('Y');
	}

	/**
	 * Title for all years
	 * @return string
	 */
	protected function titleForAllYears() {
		return __('Year on year');
	}

	/**
	 * Initialize all config vars from database
	 * Each line should be in following format: var_name|type=something|description
	 * @param string $configSetup as $dat['config'] from database
	 */
	private function initConfigVars($configSetup) {
		$this->config     = array();
		$configSetup      = explode("\n", $configSetup);

		foreach ($configSetup as $configLine) {
			$configParts = explode('|', $configLine);

			if (count($configParts) != 3)
				break;

			$valueParts = explode('=', $configParts[1]);
			if (count($valueParts) == 2) {
				$value = $valueParts[1];
				switch ($valueParts[0]) {
					case 'array':
						$type  = 'array';
						$value = explode(',', $value);
						break;
					case 'bool':
						$value = ($value == 'true');
					case 'int':
					case 'float':
						$type  = $valueParts[0];
						break;
					default:
						$type  = 'string';
				}
			} else {
				$value = $valueParts[0];
				$type  = 'string';
			}

			$this->config[$configParts[0]] = array(
				'type'			=> $type,
				'var'			=> $value,
				'description'	=> trim($configParts[2]));
		}
	}

	private function checkConfigVarsForChanges() {
		$somethingChanged = false;

		$this->checkConfigVarsForMissingValues($somethingChanged);
		$this->checkConfigVarsForAdditionalValue($somethingChanged);

		if ($somethingChanged)
			$this->updateConfigVarToDatabase ();
	}

	/**
	 * Check config vars: Are any values from default config vars missing?
	 * @param boolean $somethingChanged 
	 */
	private function checkConfigVarsForMissingValues(&$somethingChanged) {
		$defaultSetup = $this->getDefaultConfigVars();

		foreach ($defaultSetup as $key => $keyArray)
			if (!isset($this->config[$key])) {
				$somethingChanged   = true;
				$this->config[$key] = $keyArray;
			} elseif ($this->config[$key]['description'] != $keyArray['description']) {
				$somethingChanged   = true;
				$this->config[$key]['description'] = $keyArray['description'];
			}
	}

	/**
	 * Check config vars: Are any values not in default config vars?
	 * @param boolean $somethingChanged 
	 */
	private function checkConfigVarsForAdditionalValue(&$somethingChanged) {
		$defaultSetup = $this->getDefaultConfigVars();

		foreach (array_keys($this->config) as $key)
			if (!isset($defaultSetup[$key])) {
				$somethingChanged   = true;
				unset($this->config[$key]);
			}
	}

	/**
	 * Function to get a property from object
	 * @param $property
	 * @return mixed      objects property or false if property doesn't exist
	 */
	final public function get($property) {
		switch($property) {
			case 'id': return $this->id;
			case 'key': return $this->key;
			case 'type': return $this->type;
			case 'active': return $this->active;
			case 'order': return $this->order;
			case 'config': return $this->config;
			case 'name': return $this->name;
			case 'description': return $this->description;
			case 'sportid': return $this->sportid;
			case 'year': return $this->year;
			case 'dat': return $this->dat;
			default: Error::getInstance()->addWarning('Asked for non-existant property "'.$property.'" in class::Stat::get()');
				return false;
		}
	}

	/**
	 * Function to set a property of this object
	 * @param $property
	 * @param $value
	 * @return bool       false if property doesn't exist
	 */
	final public function set($property, $value) {
		switch($property) {
			case 'name': $this->name = $value;
			case 'description': $this->description = $value;
			case 'sportid': $this->sportid = $value;
			case 'year': $this->year = $value;
			case 'dat': $this->dat = $value;
			default: Error::getInstance()->addWarning('Tried to set non-existant or locked property "'.$property.'" in class::Stat::set()');
				return false;
		}
	}

	/**
	 * Get link for the config-window
	 * @param string $name [optional], default: settings-icon
	 * @param string $add_param [optional] additional parameter starting with '&...'
	 * @return string
	 */
	final public function getConfigLink($name = '', $add_param = '') {
		if ($name == '')
			$name = Icon::$CONF;

		return Ajax::window('<a href="'.self::$CONFIG_URL.'?id='.$this->id.$add_param.'">'.$name.'</a>','small');
	}

	/**
	 * Get reload-link
	 * @return string
	 */
	final public function getReloadLink() {
		return '<span class="link" onclick="Runalyze.reloadPlugin(\''.$this->id.'\');">'.Icon::$REFRESH_SMALL.'</span>';
	}

	/**
	 * Get link for removing plugin
	 * @param string $key PLUGINKEY
	 * @return string
	 */
	static public function getRemoveLink($key) {
		return Ajax::window('<a href="'.self::$CONFIG_URL.'?key='.$key.'">'.Ajax::tooltip(Icon::$CROSS, __('Remove plugin') ).'</a>','small');
	}

	/**
	 * Handle Get/Post-data and update database
	 */
	private function handleGetPostRequest() {
		if (isset($_GET['active']))
			$this->setActive((int)$_GET['active']);

		if (isset($_POST['edit']) && $_POST['edit'] == 'true') {
			foreach($this->config as $name => $dat) {
				switch ($dat['type']) {
					case 'array':
						$array = explode(',', $_POST[$name]);
						foreach ($array as $i => $var)
							$array[$i] = trim($var);
						$this->config[$name]['var'] = $array;
						break;
					case 'bool':
						$this->config[$name]['var'] = isset($_POST[$name]) && ($_POST[$name] == 'on');
						break;
					case 'int':
						$this->config[$name]['var'] = Helper::CommaToPoint(trim($_POST[$name]));
						break;
					default:
						$this->config[$name]['var'] = trim($_POST[$name]);
				}

				$this->updateConfigVarToDatabase();
			}
		}
	}

	/**
	 * Displays the config window for editing the variables
	 */
	final public function displayConfigWindow() {
		$this->handleGetPostRequest();

		$activationLink = ($this->active == 0)
			? $this->getConfigLink( __('Activate plugin'), '&active='.Plugin::$ACTIVE)
			: $this->getConfigLink( __('Deactivate plugin'), '&active='.Plugin::$ACTIVE_NOT);

		$name = ($this instanceof PluginTool)
			? $this->getWindowLink()
			: $this->name;

		include FRONTEND_PATH.'plugin/tpl.Plugin.config.php';
	}

	/**
	 * Get input-field for configuration formular
	 * @param string $name
	 * @param array $config_var
	 * @return string 
	 */
	final protected function getInputFor($name, $config_var) {
		$value = (is_array($config_var['var'])) ? implode(', ', $config_var['var']) : $config_var['var'];

		switch ($config_var['type']) {
			case 'bool':
				return '<input id="conf_'.$name.'" class="" type="checkbox" name="'.$name.'"'.($config_var['var'] == 'true' ? ' checked' : '').'>';
			case 'array':
				return '<input id="conf_'.$name.'" class="full-size" type="text" name="'.$name.'" value="'.$value.'">';
			case 'int':
				return '<input id="conf_'.$name.'" class="small-size" type="text" name="'.$name.'" value="'.$value.'">';
			default:
				return '<input id="conf_'.$name.'" class="middle-size" type="text" name="'.$name.'" value="'.$value.'">';
		}
	}

	/**
	 * Update current values from $this->config to database
	 */
	final protected function updateConfigVarToDatabase() {
		$string = '';
		foreach($this->config as $name => $dat) {
			switch ($dat['type']) {
				case 'array':
					$var = implode(', ', Helper::arrayTrim($dat['var']));
					break;
				case 'bool':
					$var = $dat['var'] ? 'true' : 'false';
					break;
				case 'int':
				default:
					$var = $dat['var'];
			}

			$string .= $name.'|'.$dat['type'].'='.$var.'|'.trim($dat['description']).NL;
		}

		DB::getInstance()->update('plugin', $this->id, 'config', $string);
	}

	/**
	 * Function to (in)activate the plugin
	 * @param int $active
	 */
	final public function setActive($active = 1) {
		DB::getInstance()->update('plugin', $this->id, 'active', $active);
		$this->active = $active;
	}

	/**
	 * Get string for internal type-enum
	 * @param enum $type
	 * @return string
	 */
	static public function getTypeString($type) {
		switch ($type) {
			case self::$STAT:
				return 'stat';
			case self::$PANEL:
				return 'panel';
			case self::$TOOL:
				return 'tool';
		}
	}

	/**
	 * Get readable string for internal type-enum
	 * @param enum $type
	 * @return string
	 */
	static public function getReadableTypeString($type) {
		switch ($type) {
			case self::$STAT:
				return __('Statistic');
			case self::$PANEL:
				return __('Panel');
			case self::$TOOL:
				return __('Tool');
		}
	}

	/**
	 * Get all keys for a given plugintype as array
	 * @param enum $type [optional]
	 * @param enum $active [optional]
	 * @return array
	 */
	static public function getKeysAsArray($type = -1, $active = -1) {
		if ($type == -1) {
			if (empty(self::$ALL_KEYS))
				self::$ALL_KEYS = DB::getInstance()->query('SELECT `key` FROM `'.PREFIX.'plugin`')->fetchAll();

			$array = self::$ALL_KEYS;
		} elseif ($active == -1)
			$array = DB::getInstance()->query('SELECT `key` FROM `'.PREFIX.'plugin` WHERE `type`="'.self::getTypeString($type).'" ORDER BY `order` ASC')->fetchAll();
		else
			$array = DB::getInstance()->query('SELECT `key` FROM `'.PREFIX.'plugin` WHERE `type`="'.self::getTypeString($type).'" AND `active`="'.$active.'" ORDER BY `order` ASC')->fetchAll();

		$return = array();
		foreach ($array as $v)
			$return[] = $v['key'];

		return $return;
	}

	/**
	 * Is the plugin already installed?
	 * @param string $key
	 * @return bool
	 */
	static public function isInstalled($key) {
		return in_array($key, self::getKeysAsArray(-1));
	}

	/**
	 * Get the PLUGINKEY for a given ID from database
	 * @param int $id
	 * @return string
	 */
	static public function getKeyForId($id) {
		$dat = DB::getInstance()->fetchByID('plugin', $id);

		if ($dat === false) {
			Error::getInstance()->addError('Plugin::getKeyForId(): No Plugin with id \''.$id.'\' found.');
			return '';
		}

		return $dat['key'];
	}

	/**
	 * Get the filename for a given PLUGINKEY, searches for folder first
	 * @param string $PLUGINKEY
	 * @return string
	 */
	static public function getFileForKey($PLUGINKEY) {
		if (file_exists(FRONTEND_PATH.'../plugin/'.$PLUGINKEY.'/class.'.$PLUGINKEY.'.php'))
			return FRONTEND_PATH.'../plugin/'.$PLUGINKEY.'/class.'.$PLUGINKEY.'.php';

		if (file_exists(FRONTEND_PATH.'../plugin/class.'.$PLUGINKEY.'.php'))
			return FRONTEND_PATH.'../plugin/class.'.$PLUGINKEY.'.php';

		return false;
	}
}