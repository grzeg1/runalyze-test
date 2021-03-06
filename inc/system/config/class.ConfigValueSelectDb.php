<?php
/**
 * This file contains class::ConfigValueSelectDb
 * @package Runalyze\System\Config
 */
/**
 * ConfigValueSelectDb
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
class ConfigValueSelectDb extends ConfigValue {
	/**
	 * Type - should be overwritten by subclass
	 * @var string
	 */
	protected $type = 'selectdb';

	/**
	 * Array with all config values of this type
	 * @var array
	 */
	static private $ConfigValues = array();

	/**
	 * Get array with all config values, $Array[KEY] = $Options
	 * @return array
	 */
	static public function getAllValues() {
		return self::$ConfigValues;
	}

	/**
	 * Subclass setup: Save all values in static array
	 */
	protected function subclassSetup() {
		self::$ConfigValues[$this->getKey()] = $this->Options;
	}

	/**
	 * Get value as string, should be overwritten
	 * @return string
	 */
	protected function getValueAsString() {
		return (string)$this->Value;
	}

	/**
	 * Set value from string, should be overwritten
	 * @param string $Value 
	 */
	protected function setValueFromString($Value) {
		$this->Value = (int)$Value;
	}

	/**
	 * Get field, should be overwritten
	 * @return FormularInput 
	 */
	public function getField() {
		$Field  = new FormularSelectBox($this->getKey(), $this->getLabel(), $this->getValue());

		$Table  = $this->Options['table'];
		$Column = $this->Options['column'];
		$Values = DB::getInstance()->query('SELECT `id`,`'.DB::getInstance()->escape($Column,false).'` FROM `'.PREFIX.$Table.'` ORDER BY `'.DB::getInstance()->escape($Column,false).'` ASC')->fetchAll();

		foreach ($Values as $Value)
			$Field->addOption($Value['id'], $Value[$Column]);

		if (!empty($this->Options['layout']))
			$Field->setLayout($this->Options['layout']);

		return $Field;
	}
}