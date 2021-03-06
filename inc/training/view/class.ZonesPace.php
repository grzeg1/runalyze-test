<?php
/**
 * This file contains class::ZonesPace
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display pace zones
 * 
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class ZonesPace extends ZonesAbstract {
	/**
	 * Get title
	 * @return string
	 */
	public function title() {
		return __('Tempozonen');
	}

	/**
	 * Get title for average
	 * @return string
	 */
	public function titleForAverage() { return '&oslash;&nbsp;Pace'; }

	/**
	 * Init data
	 */
	protected function initData() {
		$Zones = $this->Training->GpsData()->getPaceZonesAsFilledArrays();

		foreach ($Zones as $min => $Info) {
			if ($Info['distance'] > self::$MINIMUM_DISTANCE_FOR_ZONE) {
				if ($Info['hf-sum'] > 0)
					$Avg = round(100*$Info['hf-sum']/Helper::getHFmax()/$Info['num']).'&nbsp;&#37;';
				else
					$Avg = '-';

				$this->Data[] = array(
					'zone'     => ($min == 0 ? 'schneller' : SportFactory::getSpeed(1,($min*30), $this->Training->get('sportid')).'-'.SportFactory::getSpeed(1,($min*30+30), $this->Training->get('sportid')).' '.SportSpeed::$MIN_PER_KM),
					'time'     => $Info['time'],
					'distance' => $Info['distance'],
					'average'  => $Avg);
			}
		}
	}
}
