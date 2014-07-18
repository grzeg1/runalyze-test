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
class BestSegments {

    public $Data=array();

    public function __construct(TrainingObject &$Training) {
                $this->Training = $Training;

                $this->initData();
                $this->convertData();
        }



	/**
	 * Get title
	 * @return string
	 */
	public function title() {
		return __('Best segments');
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
		$Segments = $this->Training->GpsData()->getBestSegments();

		foreach ($Segments as $distance => $time) {

				$this->Data[] = array(
					'distance'     => $distance,
					'time'     => $time,
					'pace'     => $time/$distance);
			}
	}

       private function convertData() {

                foreach ($this->Data as $i => $Info) {
                        $this->Data[$i]['pace']   = SportSpeed::getSpeedWithAppendix($this->Data[$i]['distance'], $this->Data[$i]['time'], SportSpeed::$MIN_PER_KM);
                        $this->Data[$i]['time']       = Time::toString($Info['time'], false, $Info['time'] < 60 ? 2 : false);
                        $this->Data[$i]['distance']   = Running::Km($Info['distance'], 2);
                }
        }

        final public function display() {
                if (empty($this->Data))
                        return;

                echo '<div class="databox training-zones left">';
                echo '<div class="databox-header">'.$this->title().'</div>';
                echo '<table class="zebra-style zebra-blue small" style="white-space:nowrap;">';
                echo '<thead><tr>';
                echo '<th>'.__('Distance').'</th>';
                echo '<th>'.__('Time').'</th>';
                echo '<th>'.__('Pace').'</th>';
                echo '</tr></thead>';

                echo '<tbody>';
                $this->displayData();
                echo '</tbody>';
                echo '</table>';

                echo '</div>';
        }

        /**
        * Display data
        */
        private function displayData() {
                foreach ($this->Data as $Info) {
                        $opacity = 100;//0.5 + $Info['percentage']/200;

                        echo '<tr class="r" style="opacity:'.$opacity.';">';
                        echo '<td>'.$Info['distance'].'</td>';
                        echo '<td>'.$Info['time'].'</td>';
                        echo '<td>'.$Info['pace'].'</td>';

                        echo '</tr>';
                }
        }


}
