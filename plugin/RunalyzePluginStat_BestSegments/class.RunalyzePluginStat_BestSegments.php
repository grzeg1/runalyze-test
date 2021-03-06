<?php
/**
 * This file contains class::RunalyzePluginStat_Wettkampf
 * @package Runalyze\Plugins\Stats
 */
$PLUGINKEY = 'RunalyzePluginStat_BestSegments';
/**
 * Plugin "Wettkampf"
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Stats
 */
class RunalyzePluginStat_BestSegments extends PluginStat {
	private $distances = array();

	/**
	 * Initialize this plugin
	 * @see PluginStat::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$STAT;
		$this->name = __('Best Segments');
		$this->description = __('Your best segments of trainings.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p(
			__('This plugin shows your best segments of each training.') );
	}

	/**
	 * Set default config-variables
	 * @see PluginStat::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();
		$config['main_distance'] = array(
			'type' => 'int',
			'var' => 10,
			'description' => '<span class="atLeft" rel="tooltip" title="'.__('initial distance for the plot').'">'.__('Main distance').'</span>'
		);
		$config['pb_distances'] = array(
			'type' => 'array',
			'var' => array(1, 3, 5, 10, 21.1, 42.2),
			'description' => '<span class="atLeft" rel="tooltip" title="'.__('comma seperated').'">'.__('Distances for yearly comparison').'</span>'
		);
		return $config;
	}

	/**
	 * Set own navigation
	 */
	protected function setOwnNavigation() {
		$LinkList  = '';
		$LinkList .= '<li>'.Ajax::change(__('Personal Bests'), 'statistics-inner', '#bestzeiten', 'triggered').'</li>';
		$LinkList .= '<li>'.Ajax::change(__('All Races'), 'statistics-inner', '#wk-tablelist').'</li>';

		$this->setToolbarNavigationLinks(array($LinkList));
	}

	/**
	 * Init data 
	 */
	protected function prepareForDisplay() {
		//$this->setOwnNavigation();
	}

	/**
	 * Display the content
	 * @see PluginStat::displayContent()
	 */
	protected function displayContent() {
		$this->handleGetData();
		$this->displayDivs();
	}

	/**
	 * Get table for year comparison - not to use within this plugin!
	 * @return string
	 */
	public function getYearComparisonTable() {
		ob_start();
		$this->displayPersonalBestYears();
		return ob_get_clean();
	}

	/**
	 * Display all divs 
	 */
	private function displayDivs() {
		echo HTML::clearBreak();

		$this->displayDivForPersonalBest();
		//$this->displayDivForWkTable();
	}

	/**
	 * Display div for personal bests 
	 */
	private function displayDivForPersonalBest() {
		echo '<div id="bestzeiten" class="change">'.NL;
			$this->displayPersonalBests();
		echo '</div>'.NL;
	}

	/**
	 * Display div for competitions 
	 */
	private function displayDivForWkTable() {
		echo '<div id="wk-tablelist" class="change" style="display:none;">'.NL;
			$this->displayAllCompetitions();
			$this->displayWeatherStatistics();
		echo '</div>'.NL;
	}

	/**
	 * Display all competitions
	 */
	private function displayAllCompetitions() {
		$this->displayTableStart('wk-table');

		$wks = DB::getInstance()->query('
			SELECT
				id,
				time,
				sportid,
				typeid,
				comment,
				distance,
				s,
				is_track,
				pulse_avg,
				pulse_max,
				weatherid,
				temperature
			FROM `'.PREFIX.'training`
			WHERE `typeid`='.CONF_WK_TYPID.'
			ORDER BY `time` DESC')->fetchAll();
		$num = count($wks);
		if ($num > 0) {
			foreach($wks as $wk) {
				$this->displayWkTr($wk);
			}
		} else {
			$this->displayEmptyTr( __('There are no races.') );
		}
		
		$this->displayTableEnd('wk-table');
	}

	/**
	 * Display all personal bests
	 */
	private function displayPersonalBests() {
//		$this->displayTableStart('pb-table');
		$this->displayPersonalBestsTRs();
//		$this->displayTableEnd('pb-table');

		if (!empty($this->distances))
			$this->displayPersonalBestsImages();

//		$this->displayPersonalBestYears();
	}

	/**
	 * Display all table-rows for personal bests
	 */
	private function displayPersonalBestsTRs() {
		$this->distances = array();
		$dists = DB::getInstance()->query('SELECT distinct `distance` FROM `'.PREFIX.'training_best_segments`')->fetchAll();

		foreach ($dists as $i => $dist) {
			if (in_array($dist['distance'], $this->config['pb_distances']['var'])) {
				$this->distances[] = $dist['distance'];

//				$SingleRequest->bindValue('distance', $dist['distance']);
//				$SingleRequest->execute();
//				$wk = $SingleRequest->fetch();
//				$this->displayWKTr($wk, $i, true);
			}
		}

		if (empty($this->distances))
			$this->displayEmptyTr('<em>'.__('There are no races for the given distances.').'</em>');
	}

	/**
	 * Display all image-links for personal bests
	 */
	private function displayPersonalBestsImages() {
		$SubLinks = array();
		foreach ($this->distances as $km) {
			$name       = Running::Km($km, (round($km) != $km ? 1 : 0), ($km <= 3));
			$SubLinks[] = Ajax::flotChange($name, 'bestzeitenFlots', 'bestzeit'.($km*1000));
		}
		$Links = array(array('tag' => '<a href="#">'.__('Choose distance').'</a>', 'subs' => $SubLinks));

		echo '<div class="databox" style="float:none;padding:0;width:690px;margin:20px auto;">';
		echo '<div class="panel-heading">';
		echo '<div class="panel-menu">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
		echo '<h1>'.__('Results trend').'</h1>';
		echo '</div>';
		echo '<div class="panel-content">';

		$display_km = $this->distances[0];
		if (in_array($this->config['main_distance']['var'], $this->distances))
			$display_km = $this->config['main_distance']['var'];

		echo '<div id="bestzeitenFlots" class="flot-changeable" style="position:relative;width:682px;height:192px;">';
		foreach ($this->distances as $km) {
			echo Plot::getInnerDivFor('bestzeit'.($km*1000), 680, 190, $km != $display_km);
			$_GET['km'] = $km;
			include 'Plot.BestTime.php';
		}
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Display comparison for all years for personal bests
	 */
	private function displayPersonalBestYears() {
		$year = array();
		$dists = array();
		$kms = (is_array($this->config['pb_distances']['var'])) ? $this->config['pb_distances']['var'] : array(3, 5, 10, 21.1, 42.2);
		foreach ($kms as $km)
			$dists[$km] = array('sum' => 0, 'pb' => INFINITY);
		
		$wks = DB::getInstance()->query('SELECT YEAR(FROM_UNIXTIME(`time`)) as `y`, `distance`, `s` FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' ORDER BY `y` ASC')->fetchAll();

		if (empty($wks))
			return;

		foreach ($wks as $wk) {
			if (!isset($year[$wk['y']])) {
				$year[$wk['y']] = $dists;
				$year[$wk['y']]['sum'] = 0;
				$year['sum'] = 0;
			}
			$year[$wk['y']]['sum']++;
			foreach($kms as $km)
				if ($km == $wk['distance']) {
					$year[$wk['y']][$km]['sum']++;
					if ($wk['s'] < $year[$wk['y']][$km]['pb'])
						$year[$wk['y']][$km]['pb'] = $wk['s'];
				}
		}

		echo '<table class="fullwidth zebra-style">';
		echo '<thead>';
		echo '<tr>';
		echo '<th></th>';

		foreach (array_keys($year) as $y)
			if ($y != 'sum')
				echo '<th>'.$y.'</th>';

		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ($kms as $i => $km) {
			echo '<tr class="r"><td class="b">'.Running::Km($km, 1, $km <= 3).'</td>';
		
			foreach ($year as $key => $y)
				if ($key != 'sum')
					echo '<td>'.($y[$km]['sum'] != 0 ? '<small>'.Time::toString($y[$km]['pb']).'</small> '.$y[$km]['sum'].'x' : '<em><small>---</small></em>').'</td>';
		
			echo '</tr>';
		}

		echo '<tr class="top-spacer no-zebra r">';
		echo '<td class="b">'.__('In total').'</td>';

		foreach ($year as $i => $y)
			if ($i != 'sum')
				echo '<td>'.$y['sum'].'x</td>';

		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Display table start
	 * @param string $id
	 */
	private function displayTableStart($id) {
		echo('
			<table class="fullwidth zebra-style" id="'.$id.'">
				<thead>
					<tr class="c">
						<th class="{sorter: false}">&nbsp;</th>
						<th class="{sorter: \'germandate\'}">'.__('Date').'</th>
						<th>'.__('Name').'</th>
						<th class="{sorter: \'distance\'}">'.__('Distance').'</th>
						<th class="{sorter: \'resulttime\'}">'.__('Time').'</th>
						<th>'.__('Pace').'</th>
						<th>'.__('Heart rate').'</th>
						<th class="{sorter: \'temperature\'}">'.__('Weather').'</th>
					</tr>
				</thead>
				<tbody>');
	}

	/**
	 * Display table-row for a competition
	 * @param array $data
	 */
	private function displayWKTr(array $data) {
		$Training = new TrainingObject($data);

		echo('
			<tr class="r">
				<td>'.$this->getIconForCompetition($data['id']).'</td>
				<td class="c small">'.$Training->DataView()->getDateAsWeeklink().'</a></td>
				<td class="l"><strong>'.$Training->Linker()->linkWithComment().'</strong></td>
				<td>'.$Training->DataView()->getDistanceStringWithoutEmptyDecimals().'</td>
				<td>'.$Training->DataView()->getTimeString().'</td>
				<td class="small">'.$Training->DataView()->getSpeedString().'</td>
				<td class="small">'.Helper::Unknown($Training->getPulseAvg()).' / '.Helper::Unknown($Training->getPulseMax()).' bpm</td>
				<td class="small">'.$Training->Weather()->asString().'</td>
			</tr>');	
	}

	/**
	 * Display an empty table-row
	 * @param string $text [optional]
	 */
	private function displayEmptyTr($text = '') {
		echo '<tr class="a">
				<td colspan="8">'.$text.'</td>
			</tr>';
	}

	/**
	 * Display table end
	 * @param string $id
	 */
	private function displayTableEnd($id) {
		echo '</tbody>';
		echo '</table>';

		Ajax::createTablesorterFor('#'.$id, true);
	}

	/**
	 * Display statistics for weather
	 */
	private function displayWeatherStatistics() {
		$Strings = array();
		$Weather = DB::getInstance()->query('SELECT SUM(1) as num, weatherid FROM `'.PREFIX.'training` WHERE `typeid`='.CONF_WK_TYPID.' AND `weatherid`!="'.Weather::$UNKNOWN_ID.'" GROUP BY `weatherid` ORDER BY `weatherid` ASC')->fetchAll();
		foreach ($Weather as $W)
			$Strings[] = $W['num'].'x '.Icon::getWeatherIcon($W['weatherid']);

		if (!empty($Strings)) {
			echo '<strong>'.__('Weather statistics').':</strong> ';
			echo implode(', ', $Strings);
			echo '<br><br>';
		}
	}

	/**
	 * Get linked icon for this competition
	 * @param int $id ID of the training
	 * @return string
	 */
	private function getIconForCompetition($id) {
		if ($this->isFunCompetition($id)) {
			$tag = 'nofun';
			$icon = Ajax::tooltip(Icon::$CLOCK_GREY, __('Fun race | Click to mark this activity as a \'normal race\'.'));
		} else {
			$tag = 'fun';
			$icon = Ajax::tooltip(Icon::$CLOCK_ORANGE, __('Race | Click to mark this activity as a \'fun race\'.'));
		}

		return $this->getInnerLink($icon, 0, 0, $tag.'-'.$id);
	}

	/**
	 * Handle data from get-variables
	 */
	private function handleGetData() {
		if (isset($_GET['dat']) && strlen($_GET['dat']) > 0) {
			$parts = explode('-', $_GET['dat']);
			$tag   = $parts[0];
			$id    = $parts[1];

			if ($tag == 'fun' && is_numeric($id)) {
				$this->config['fun_ids']['var'][] = $id;
			} elseif ($tag == 'nofun' && is_numeric($id)) {
				if (($index = array_search($id, $this->config['fun_ids']['var'])) !== FALSE)
					unset($this->config['fun_ids']['var'][$index]);
			}

			$this->updateConfigVarToDatabase();
		}
	}

	/**
	 * Is this competition just for fun?
	 * @param int $id
	 * @return bool
	 */
	public function isFunCompetition($id) {
		return (in_array($id, $this->config['fun_ids']['var']));
	}
}