<?php
/**
 * This file contains class::ZonesHeartrate
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display heartrate zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class ZonesHeartrate extends ZonesAbstract {
	/**
	 * Get title
	 * @return string
	 */
	public function title() {
		return __('Pulszonen');
	}

	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return 'Pace'; }

	/**
	 * Init data
	 */
	protected function initData() {
		$Zones = $this->Training->GpsData()->getPulseZonesAsFilledArrays();

        $colors=array (10=>ProgressBarSingle::$COLOR_RED, 9=>ProgressBarSingle::$COLOR_YELLOW, 8=>ProgressBarSingle::$COLOR_GREEN,
                        7=>ProgressBarSingle::$COLOR_BLUE, 6=>ProgressBarSingle::$COLOR_GREY);

		foreach ($Zones as $hf => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE)
				$this->Data[] = array(
                    'zone'     => (10*($hf==5?0:$hf-1)).'-'.(10*$hf).'&#37; (Z'.($hf-5).')',
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => SportSpeed::getSpeedWithAppendix($Info['distance'], $Info['time'], SportSpeed::$MIN_PER_KM),
                    'color' => isset($colors[$hf])?$colors[$hf]:$colors[6]
				);
		}
	}
}
