<?php
/**
 * This file contains class::TrainingPlotCollection
 * @package Runalyze\Draw\Training
 */
/**
 * Training plot with collection of pace, heartrate and elevation
 * @author Hannes Christiansen
 * @package Runalyze\Draw\Training
 */
class TrainingPlotCollection extends TrainingPlot {
	/**
	 * Data for pace
	 * @var array
	 */
	protected $DataPace = array();

	/**
	 * Data for heartrate
	 * @var array
	 */
	protected $DataPulse = array();

	/**
	 * Data for elevation
	 * @var array
	 */
	protected $DataElevation = array();

	/**
	 * Is this plot visible?
	 * @return string
	 */
	public function isVisible() {
		return CONF_TRAINING_SHOW_PLOT_COLLECTION;
	}

	/**
	 * Set key and title for this plot
	 */
	protected function setKeyAndTitle() {
		$this->key   = 'collection';
		$this->title = 'Pace/Heart rate/Elevation';
	}

	/**
	 * Init data
	 */
	protected function initData() {
		$this->DataPace      = TrainingPlotPace::getData($this->Training);
		$this->DataPulse     = TrainingPlotPulse::getData($this->Training);
		$this->DataElevation = TrainingPlotElevation::getData($this->Training);
        $this->DataTime = $this->Training->GpsData()->getPlotDataForTime();

		$this->Plot->Data[] = array('label' => 'Elevation', 'color' => 'rgba(227,217,187,1)', 'data' => $this->DataElevation);
		$this->Plot->Data[] = array('label' => 'Heat rate', 'color' => 'rgb(136,0,0)', 'data' => $this->DataPulse, 'yaxis' => 2);
		$this->Plot->Data[] = array('label' => 'Pace', 'color' => 'rgb(60,60,216)', 'data' => $this->DataPace, 'yaxis' => 3);
        $this->Plot->Data[] = array('label' => 'Time', 'color' => 'rgba(255,255,255,0)', 'data' => $this->DataTime, 'yaxis'=>4);

	}

	/**
	 * Set all properties for this plot 
	 */
	protected function setProperties() {
		$this->Plot->addYAxis(1, 'left');
		TrainingPlotElevation::setPropertiesTo($this->Plot, 1, $this->Training, $this->DataElevation);

		$this->Plot->addYAxis(2, 'right', false);
		TrainingPlotPulse::setPropertiesTo($this->Plot, 2, $this->Training, $this->DataPulse);

		$this->Plot->addYAxis(3, 'right', true, 0);
		TrainingPlotPace::setPropertiesTo($this->Plot, 3, $this->Training, $this->DataPace);
                $this->Plot->setLineWidth(1,2);
                $this->Plot->setLineWidth(2,2);

        $this->Plot->addYAxis(4, 'left', false, 1, false);
        $this->Plot->setYAxisTimeFormat('%H:%M:%S', 4);
        $this->Plot->setLineWidth(3,0);


    }
}
