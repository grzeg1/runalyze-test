<?php
/**
 * This file contains the class of the RunalyzePluginStat "Wetter".
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_Wetter';
/**
 * Class: RunalyzePluginStat_Wetter
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_Wetter extends PluginStat {
	private $i      = 0;
	private $jahr   = '';
	private $jstart = 0;
	private $jende  = 0;

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = __('Weather');
		$this->description = __('Statistics about weather conditions, temperatures and clothing.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('There is no bad weather, there is only bad clothing.') );
		echo HTML::p( __('Are you a wimp or a tough runner?'.
						'Have a look at these statistics about the weather conditions and your clothing while training.') );
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['for_weather']  = array('type' => 'bool', 'var' => true, 'description' => __('Show statistics about weather conditions'));
		$config['for_clothes']  = array('type' => 'bool', 'var' => true, 'description' => __('Show statistics about your clothing'));

		return $config;
	}

	/**
	 * Get own links for toolbar navigation
	 * @return array
	 */
	protected function getToolbarNavigationLinks() {
		$LinkList = array();

		if ($this->config['for_weather']['var'])
			$LinkList[] = '<li>'.Ajax::window('<a href="plugin/'.$this->key.'/window.php">'.Ajax::tooltip(Icon::$FATIGUE, __('Show temperature plots')).'</a>').'</li>';

		return $LinkList;
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		$this->initData();

		$this->setYearsNavigation();
		$this->setToolbarNavigationLinks($this->getToolbarNavigationLinks());

		$this->setHeader($this->getHeader());
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->displayExtremeTrainings();
		$this->displayMonthTable();
		$this->displayClothesTable();

		if (!$this->config['for_weather']['var'] && !$this->config['for_clothes']['var'])
			echo HTML::warning( __('You have to activate some statistics in the plugin configuration.') );
	}

	/**
	 * Display month-table
	 */
	private function displayMonthTable() {
		echo '<table class="fullwidth zebra-style r">';
		echo '<thead>'.HTML::monthTR(8, 1).'</thead>';
		echo '<tbody>';

		if ($this->config['for_weather']['var']) {
			$this->displayMonthTableTemp();
			$this->displayMonthTableWeather();
		}

		if ($this->config['for_clothes']['var']) {
			$this->displayMonthTableClothes();
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	* Display month-table for temperature
	*/
	private function displayMonthTableTemp() {
		echo '<tr class="top-spacer"><td>&#176;C</td>';

		$temps = DB::getInstance()->query('
			SELECT
				AVG(`temperature`) as `temp`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.CONF_MAINSPORT.'" AND
				`temperature` IS NOT NULL
				'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
			LIMIT 12')->fetchAll();

		$i = 1;

		if (!empty($temps)) {
			foreach ($temps as $temp) {
				for (; $i < $temp['m']; $i++)
					echo HTML::emptyTD();
				$i++;
		
				echo '<td>'.round($temp['temp']).' &deg;C</td>';
			}

			for (; $i <= 12; $i++)
				echo HTML::emptyTD();
		} else {
			echo HTML::emptyTD(12);
		}

		echo '</tr>';
	}

	/**
	* Display month-table for weather
	*/
	private function displayMonthTableWeather() {
		$wetter_all = Weather::getArrayWithoutUnknown();
		foreach ($wetter_all as $wetter) {
			$Weather = new Weather($wetter['id']);
			echo '<tr><td>'.$Weather->icon().'</td>';
		
			$i = 1;
			$data = DB::getInstance()->query('SELECT
					SUM(1) as `num`,
					MONTH(FROM_UNIXTIME(`time`)) as `m`
				FROM `'.PREFIX.'training` WHERE
					`sportid`="'.CONF_MAINSPORT.'" AND
					`weatherid`='.$wetter['id'].'
					'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
				GROUP BY MONTH(FROM_UNIXTIME(`time`))
				ORDER BY `m` ASC
				LIMIT 12')->fetchAll();

			if (!empty($data)) {
				foreach ($data as $dat) {
					for (; $i < $dat['m']; $i++)
						echo HTML::emptyTD();
					$i++;
			
					echo ($dat['num'] != 0)
						? '<td>'.$dat['num'].'x</td>'
						: HTML::emptyTD();
				}
			
				for (; $i <= 12; $i++)
					echo HTML::emptyTD();
			} else {
				echo HTML::emptyTD(12);
			}
		}
	
		echo '</tr>';
	}

	/**
	* Display month-table for clothes
	*/
	private function displayMonthTableClothes() {
		$nums = DB::getInstance()->query('SELECT
				SUM(1) as `num`,
				MONTH(FROM_UNIXTIME(`time`)) as `m`
			FROM `'.PREFIX.'training` WHERE
				`sportid`="'.CONF_MAINSPORT.'" AND
				`clothes`!=""
				'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
			GROUP BY MONTH(FROM_UNIXTIME(`time`))
			ORDER BY `m` ASC
			LIMIT 12')->fetchAll();
		
		if (!empty($nums)) {
			foreach ($nums as $dat)
				$num[$dat['m']] = $dat['num'];
		}

		$kleidungen = DB::getInstance()->query('SELECT `id`, `name` FROM `'.PREFIX.'clothes` ORDER BY `order` ASC')->fetchAll();
		if (!empty($kleidungen)) {
			foreach ($kleidungen as $k => $kleidung) {
				echo '<tr class="'.($k == 0 ? 'top-spacer' : '').'"><td>'.$kleidung['name'].'</td>';
			
				$i = 1;
				$data = DB::getInstance()->query('SELECT
						SUM(IF(FIND_IN_SET("'.$kleidung['id'].'", `clothes`)!=0,1,0)) as `num`,
						MONTH(FROM_UNIXTIME(`time`)) as `m`
					FROM `'.PREFIX.'training` WHERE
						`sportid`="'.CONF_MAINSPORT.'"
						'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').'
					GROUP BY MONTH(FROM_UNIXTIME(`time`))
					HAVING `num`!=0
					ORDER BY `m` ASC
					LIMIT 12')->fetchAll();

				if (!empty($data)) {
					foreach ($data as $dat) {
						for (; $i < $dat['m']; $i++)
							echo HTML::emptyTD();
						$i++;

						if ($dat['num'] != 0)
							echo '
								<td class="r"><span title="'.$dat['num'].'x">
										'.round($dat['num']*100/$num[$dat['m']]).' &#37;
								</span></td>';
						else
							echo HTML::emptyTD();
					}

					for (; $i <= 12; $i++)
						echo HTML::emptyTD();
				} else {
					echo '<td colspan="12"></td>';
				}

				echo '</tr>';
			}
		}
	}

	/**
	 * Display table for clothes
	 */
	private function displayClothesTable() {
		if (!$this->config['for_clothes']['var'])
			return;

		echo '<table class="fullwidth zebra-style">
			<thead><tr>
				<th></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
				<th colspan="2"></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
				<th colspan="2"></th>
				<th>'.__('Temperatures').'</th>
				<th>&Oslash;</th>
			</tr></thead>';
		echo '<tr class="r">';

		$kleidungen = DB::getInstance()->query('SELECT * FROM `'.PREFIX.'clothes` ORDER BY `order` ASC')->fetchAll();
		if (!empty($kleidungen)) {
			foreach ($kleidungen as $i => $kleidung) {
				if ($i%3 == 0)
					echo '</tr><tr class="r">';
				else
					echo '<td>&nbsp;&nbsp;</td>';

				$dat = DB::getInstance()->query('SELECT
						AVG(`temperature`) as `avg`,
						MAX(`temperature`) as `max`,
						MIN(`temperature`) as `min`
					FROM `'.PREFIX.'training`
					WHERE
						`sportid`="'.CONF_MAINSPORT.'" AND
						`temperature` IS NOT NULL AND
						FIND_IN_SET('.$kleidung['id'].',`clothes`) != 0
					'.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : ''))->fetch();

				echo '<td class="l">'.$kleidung['name'].'</td>';

				if (isset($dat['min'])) {
					echo '<td>'.($dat['min']).'&deg;C '.__('to').' '.($dat['max']).'&deg;C</td>';
					echo '<td>'.round($dat['avg']).'&deg;C</td>';
				} else {
					echo '<td colspan="2" class="c"><em>-</em></td>';
				}
			}
		}

		for (; $i%3 != 2; $i++)
			echo HTML::emptyTD(3);

		echo '</tr>';
		echo '</table>';
	}

	/**
	 * Display extreme trainings
	 */
	private function displayExtremeTrainings() {
		$hot  = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').' ORDER BY `temperature` DESC LIMIT 5')->fetchAll();
		$cold = DB::getInstance()->query('SELECT `temperature`, `id`, `time` FROM `'.PREFIX.'training` WHERE `temperature` IS NOT NULL '.($this->year != -1 ? 'AND YEAR(FROM_UNIXTIME(`time`))='.$this->year : '').' ORDER BY `temperature` ASC LIMIT 5')->fetchAll();

		foreach ($hot as $i => $h)
			$hot[$i] = $h['temperature'].'&nbsp;&#176;C '.__('on').' '.Ajax::trainingLink($h['id'], date('d.m.Y', $h['time']));
		foreach ($cold as $i => $c)
			$cold[$i] = $c['temperature'].'&nbsp;&#176;C '.__('on').' '.Ajax::trainingLink($c['id'], date('d.m.Y', $c['time']));

		echo '<p>';
		echo '<strong>'.__('Hottest activities').':</strong> '.NL;
		echo implode(', '.NL, $hot).'<br>'.NL;
		echo '<strong>'.__('Coldest activities').':</strong> '.NL;
		echo implode(', '.NL, $cold).'<br>'.NL;
		echo '</p>';
	}

	/**
	 * Initialize internal data
	 */
	private function initData() {
		if ($this->year == -1) {
			$this->i      = 0;
			$this->jahr   = "Gesamt";
			$this->jstart = mktime(0,0,0,1,1,START_YEAR);
			$this->jende  = time();
		} else {
			$this->i      = $this->year;
			$this->jahr   = $this->year;
			$this->jstart = mktime(0,0,0,1,1,$this->i);
			$this->jende  = mktime(23,59,59,1,0,$this->i+1);
		}
	}

	/**
	 * Get header depending on config
	 */
	private function getHeader() {
		$header = 'Wetter';

		if ($this->config['for_clothes']['var'])
			$header = ($this->config['for_weather']['var']) ? __('Weather and Clothing') : __('Clothing');

		return $header.': '.$this->jahr;
	}
}