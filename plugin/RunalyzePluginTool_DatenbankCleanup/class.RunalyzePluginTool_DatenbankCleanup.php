<?php
/**
 * This file contains the class of the RunalyzePluginTool "DatenbankCleanup".
 * @package Runalyze\Plugins\Tools
 */
$PLUGINKEY = 'RunalyzePluginTool_DatenbankCleanup';
/**
 * Class: RunalyzePluginTool_DatenbankCleanup
 * @author Hannes Christiansen
 * @package Runalyze\Plugins\Tools
 */
class RunalyzePluginTool_DatenbankCleanup extends PluginTool {
	/**
	 * Success messages
	 * @var array
	 */
	protected $SuccessMessages = array();

	/**
	 * Initialize this plugin
	 * @see PluginPanel::initPlugin()
	 */
	protected function initPlugin() {
		$this->type = Plugin::$TOOL;
		$this->name = __('Database cleanup');
		$this->description = __('Recalculation of some statistics may be needed after deleting some activities.'.
								'In addition, values for elevation, TRIMP and VDOT can be recalculated.');
	}

	/**
	 * Display long description 
	 */
	protected function displayLongDescription() {
		echo HTML::p( __('Due to performance reasons, some statistics are saved in the database.'.
						'Under some circumstances, you have to recalculate these values after deleting an activity by hand.') );
	}

	/**
	 * Set default config-variables
	 * @see PluginPanel::getDefaultConfigVars()
	 */
	protected function getDefaultConfigVars() {
		$config = array();

		return $config;
	}

	/**
	 * Display the content
	 * @see PluginPanel::displayContent()
	 */
	protected function displayContent() {
		if (isset($_GET['clean'])) {
			$this->cleanDatabase();

			foreach ($this->SuccessMessages as $Message)
				echo HTML::okay($Message);
		}

		$AndApplyElevationToVDOT = CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION ? __(' and adjust VDOT') : '';

		$Fieldset = new FormularFieldset( __('Cleanup database') );

		$Fieldset->addBlock(
				__('This tool allows you to cleanup your database.'.
					'This process does only touch some cumulative statistics for your shoes and some cached values.') );
		$Fieldset->addBlock('&nbsp;');

		$Fieldset->addInfo(
				'<strong>'.self::getActionLink( __('Simple cleanup'), 'clean=simple').'</strong><br>'.
				__('Recalculation of cumulative statistics for shoes and maximal values for ATL/CTL/TRIMP.') );
        $Fieldset->addInfo(
            '<strong>'.self::getActionLink( __('Complete cleanup'), 'clean=complete').'</strong><br>'.
            __('Recalculation of TRIMP and VDOT for every activity. Afterwards, the simple cleanup will be done.') );
        $Fieldset->addInfo(
            '<strong>'.self::getActionLink( __('Recalculate statistics'), 'clean=statistics').'</strong><br>'.
            __('Recalculation of statistics for every activity.') );
		$Fieldset->addInfo(
				'<strong>'.self::getActionLink( __('Recalculate elevation').$AndApplyElevationToVDOT, 'clean=elevation').'</strong><br>'.
				__('Recalculation of elevation for every activity with gps data.<br>'.
					'This has to be done after changing your configuration concerning the calculation of elevation.<br>'.
					'<br>'.
					'<small>This does not change your manual value for the elevation. The calculated value is only shown in the detailed view.</small>') );
		$Fieldset->addInfo(
				'<strong>'.self::getActionLink( __('Recalculate elevation').$AndApplyElevationToVDOT.' '.__('(overwrite manual value)'), 'clean=elevation&overwrite=true').'</strong><br>'.
				__('Recalculation of elevation for every activity with gps data.<br>'.
					'This has to be done after changing your configuration concerning the calculation of elevation.<br>'.
					'<br>'.
					'<small>This <strong>does</strong> change your manual value for the elevation.</small>') );

		if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION) {
			$Fieldset->addWarning(
				__('The VDOT-adjustment for elevation data is activated (see configuration).'.
					'The complete cleanup will not work as expected, recalculate the elevation first.') );
		}

		$Formular = new Formular();
		$Formular->setId('datenbank-cleanup');
		$Formular->addFieldset($Fieldset);
		$Formular->display();
	}

	/**
	 * Clean the databse
	 */
	private function cleanDatabase() {
		$this->SuccessMessages[] = __('The database has been purged.');

		if ($_GET['clean'] == 'complete')
			$this->resetTrimpAndVdot();

		if ($_GET['clean'] == 'simple' || $_GET['clean'] == 'complete') {
			$this->resetMaxValues();
			$this->resetShoes();
		}

        if ($_GET['clean'] == 'elevation')
            $this->calculateElevation();

        if ($_GET['clean'] == 'statistics')
            $this->calculateStatistics();

		JD::recalculateVDOTform();
		BasicEndurance::recalculateValue();
		Helper::recalculateStartTime();
		Helper::recalculateHFmaxAndHFrest();

		// TODO: Nicht existente Kleidung aus DB loeschen
	}

	/**
	 * Reset all TRIMP- and VDOT-values in database
	 */
	private function resetTrimpAndVdot() {
		$DB        = DB::getInstance();
		$Trainings = $DB->query('SELECT `id`,`sportid`,`typeid`,`distance`,`s`,`pulse_avg`,`arr_heart`,`arr_time` FROM `'.PREFIX.'training`')->fetchAll();

		foreach ($Trainings as $Training) {
			$DB->update('training', $Training['id'],
				array(
					'trimp',
					'vdot',
					'vdot_by_time',
					'jd_intensity'
				),
				array(
					Trimp::forTraining($Training),
					JD::Training2VDOT($Training['id'], $Training),
					JD::Competition2VDOT($Training['distance'], $Training['s']),
					JD::Training2points($Training['id'], $Training)
				)
			);
		}

		$this->SuccessMessages[] = sprintf( __('TRIMP and VDOT values have been recalculated for <strong>%s</strong> activities.'), count($Trainings) );
	}

	/**
	 * Calculate elevation
	 */
    private function calculateElevation() {
        $DB        = DB::getInstance();

        $Trainings = $DB->query('SELECT `id`,`arr_alt`,`arr_time`, `distance`,`s` FROM `'.PREFIX.'training` WHERE `arr_alt`!=""')->fetchAll();


        foreach ($Trainings as $Training) {
            $GPS    = new GpsData($Training);
            $elevationArray = $GPS->calculateElevation(true);
            $keys   = array('elevation_calculated');
            $values = array($elevationArray[0]);

            if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION) {
                $keys[] = 'vdot_with_elevation';
                $values[] = JD::Training2VDOTwithElevation($Training['id'], $Training, $elevationArray[1], $elevationArray[2]);
            }

            if (Request::param('overwrite') == 'true') {
                $keys[]   = 'elevation';
                $values[] = $elevationArray[0];
            }

            $DB->update('training', $Training['id'], $keys, $values);

        }

        $this->SuccessMessages[] = sprintf( __('Elevation values have been recalculated for <strong>%s</strong> activities.'), count($Trainings) );

        if (CONF_JD_USE_VDOT_CORRECTION_FOR_ELEVATION)
            $this->recalculateVDOTwithElevationWithoutGPSarray();
    }

    private function calculateStatistics() {
        $DB        = DB::getInstance();

        $DB->exec('DELETE from runalyze_training_hr_zones');
        $DB->exec('DELETE from runalyze_training_pace_zones');

        $Trainings = $DB->query('SELECT `id`,`arr_time`,arr_heart, arr_dist, `distance`,`s` FROM `'.PREFIX.'training` WHERE `arr_alt`!=""')->fetchAll();


        foreach ($Trainings as $Training) {
            $GPS    = new GpsData($Training);

            $zones=$GPS->getPulseZonesAsFilledArrays();

            foreach ($zones as $zone => $zonedata){
                $colarr=array('id_training', 'id_zone', 's', 'distance');
                $valarr=array($Training['id'], $zone-5, $zonedata['time'], $zonedata['distance']);
                $DB->insert('training_hr_zones',$colarr, $valarr );
                //Error::getInstance()->addDebug('ZONE'.$zone);
            }

            $zones=$GPS->getPaceZonesAsFilledArrays();

            foreach ($zones as $zone => $zonedata){
                $colarr=array('id_training', 'id_zone', 's', 'distance', 'avghr');
                $valarr=array($Training['id'], $zone, $zonedata['time'], $zonedata['distance'], round($zonedata['hf-sum']/$zonedata['num']));
                $DB->insert('training_pace_zones',$colarr, $valarr );
                //Error::getInstance()->addDebug('ZONE'.$zone);
            }



        }

        $this->SuccessMessages[] = sprintf( __('Statistics values have been recalculated for <strong>%s</strong> activities.'), count($Trainings) );

    }


	/**
	 * Recalculate VDOT with elevation for trainings without gps array
	 */
	private function recalculateVDOTwithElevationWithoutGPSarray() {
		$DB        = DB::getInstance();
		$Trainings = $DB->query('SELECT `id`,`s`,`distance`,`elevation` FROM `'.PREFIX.'training` WHERE `elevation`>0 AND `arr_alt`=""')->fetchAll();

		foreach ($Trainings as $Training) {
			$newVdot = JD::Training2VDOTwithElevation($Training['id'], $Training, $Training['elevation'], $Training['elevation']);
			$DB->update('training', $Training['id'], 'vdot_with_elevation', $newVdot);
		}
	}

	/**
	 * Clean the databse for max_atl, max_ctl, max_trimp
	 */
	private function resetMaxValues() {
		$OldMaxValues = $this->getMaxValues();

		Trimp::calculateMaxValues();
		JD::recalculateVDOTcorrector();

		$NewMaxValues = $this->getMaxValues();

		if ($OldMaxValues == $NewMaxValues) {
			$this->SuccessMessages[] = __('The maximal values for ATL/CTL/TRIMP and VDOT correction factor did not change.');
		} else {
			foreach (array_keys($OldMaxValues) as $Key) {
				if ($OldMaxValues[$Key] != $NewMaxValues[$Key])
					$this->SuccessMessages[] = __('New').' '.$Key.': <strong>'.$NewMaxValues[$Key].'</strong>, '.__('old value was').' '.$OldMaxValues[$Key];
			}
		}
	}

	/**
	 * Get max values
	 * @return array
	 */
	private function getMaxValues() {
		return array(
			__('maxATL')			=> (int)Trimp::maxATL(),
			__('maxCTL')			=> (int)Trimp::maxCTL(),
			__('maxTRIMP')			=> (int)Trimp::maxTRIMP(),
			__('VDOT corrector')	=> round(JD::correctionFactor(), 4)
		);
	}

	/**
	 * Clean the databse for shoes
	 */
	private function resetShoes() {
		ShoeFactory::recalculateAllShoes();

		$this->SuccessMessages[] = sprintf( __('Statistics have been recalculated for all <strong>%s</strong> shoes.'), count(ShoeFactory::NamesAsArray()) );
	}
}