<?php
/**
 * Draw personal bests for a given distance
 * Call:   include Plot.BestTime.php
 */

$Plugin = Plugin::getInstanceFor('RunalyzePluginStat_BestSegments');

$distance    = !is_numeric($_GET['km']) ? 10 : (float)$_GET['km'];
$Dates       = array();
$Results     = array();
$label       = str_replace('&nbsp;', ' ', sprintf( __('Result over %s'), Running::Km($distance, 1, ($distance <= 3)) ) );
$titleCenter = str_replace('&nbsp;', ' ', sprintf( __('Result overs %s'), Running::Km($distance, 1, ($distance <= 3)) ) );
$timeFormat  = '%M:%S';

$competitions = DB::getInstance()->query('SELECT id,time, runalyze_training_best_segments.s as s FROM `'.PREFIX.'training` JOIN`'.PREFIX.'training_best_segments`
ON runalyze_training.id=runalyze_training_best_segments.id_training WHERE runalyze_training.accountid=0 AND runalyze_training_best_segments.accountid=0 AND runalyze_training_best_segments.`distance`="'.$distance.'" ORDER BY `time` ASC')->fetchAll();
if (!empty($competitions)) {
	foreach ($competitions as $competition) {
			$Dates[]   = $competition['time'];
			$Results[$competition['time'].'000'] = ($competition['s']*1000); // Attention: timestamp(0) => 1:00:00
	}

	if (max($Results) > 3600*1000)
		$timeFormat = '%H:%M:%S';
}

$Plot = new Plot("bestzeit".$distance*1000, 680, 190);
$Plot->Data[] = array('label' => $label, 'data' => $Results);

$Plot->setMarginForGrid(5);
$Plot->setXAxisAsTime();

//if (count($Results) == 1)
$Plot->setXAxisTimeFormat('%d.%m.%y');
$Plot->setXAxisTimeFormat('%d %b %y');

$Plot->addYAxis(1, 'left');
$Plot->setYAxisAsTime(1);
$Plot->setYAxisTimeFormat($timeFormat, 1);

$Plot->lineWithPoints();
$Plot->enableTracking();
$Plot->hideLegend();
$Plot->setTitle($titleCenter);

$Plot->outputJavaScript();