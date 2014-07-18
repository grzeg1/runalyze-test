<?php
/**
 * Draw analyse of training: ATL/CTL/VDOT
 * Call:   include Plot.form.php
 * @package Runalyze\Plugins\Panels
 */
$DebugAllValues = false;
$MaxATLPoints   = 750;
$DataFailed     = false;
$ATLs           = array();
$CTLs           = array();
$VDOTs          = array();
$VDOTsday       = array();
$Trimps_raw     = array();
$VDOTs_raw      = array();
$Durations_raw  = array();
$maxATL=0;
$maxCTL=0;
$Atls_raw           = array();
$Ctls_raw       = array();

$All   = ($_GET['y'] == 'all');
$Year  = $All ? date('Y') : (int)$_GET['y'];

if ($Year >= START_YEAR && $Year <= date('Y') && START_TIME != time()) {
	$StartYear    = !$All ? $Year : START_YEAR;
	$EndYear      = !$All ? $Year : date('Y');
	$MaxDays      = ($EndYear - $StartYear + 1)*366;
	$AddDays      = max(CONF_ATL_DAYS, CONF_CTL_DAYS, CONF_VDOT_DAYS);
	$StartTime    = !$All ? mktime(0,0,0,1,1,$StartYear) : START_TIME;
	$StartDay     = date('Y-m-d', $StartTime);
	$EndTime      = !$All && $Year < date('Y') ? mktime(1,0,0,12,31,$Year) : strtotime( date('Y-m-d'))+(45 * 24 * 60 * 60);
	$NumberOfDays = Time::diffInDays($StartTime, $EndTime);

	$EmptyArray    = array_fill(0, $MaxDays + $AddDays, 0);
	$Trimps_raw    = $EmptyArray;
	$VDOTs_raw     = $EmptyArray;
	$Durations_raw = $EmptyArray;

	// Here ATL/CTL/VDOT will be implemented again
	// Normal functions are too slow, calling them for each day would trigger each time a query
	// - ATL/CTL: SUM(`trimp`) for CONF_ATL_DAYS / CONF_CTL_DAYS
	// - VDOT: AVG(`vdot`) for CONF_VDOT_DAYS

	$Data = DB::getInstance()->query('
		SELECT
			DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") as `index`,
			SUM(`trimp`) as `trimp`,
			SUM('.JD::mysqlVDOTsum().'*(`sportid`='.CONF_RUNNINGSPORT.')) as `vdot`,
			SUM('.JD::mysqlVDOTsumTime().') as `s`
		FROM `'.PREFIX.'training`
		WHERE
			DATEDIFF(FROM_UNIXTIME(`time`), "'.$StartDay.'") BETWEEN -'.$AddDays.' AND '.$NumberOfDays.'
		GROUP BY `index`')->fetchAll();

	foreach ($Data as $dat) {
		$index = $dat['index'] + $AddDays;

		$Trimps_raw[$index] = $dat['trimp'];

		if ($dat['vdot'] != 0) {
			$VDOTs_raw[$index]     = $dat['vdot']; // Remember: These values are already multiplied with `s`
			$Durations_raw[$index] = (double)$dat['s'];
		}
	}

	$StartDayInYear = $All ? Time::diffInDays($StartTime, mktime(0,0,0,1,1,$StartYear)) + 1 : 0;
	$LowestIndex = $AddDays + 1;
	$HighestIndex = $AddDays + 1 + $NumberOfDays;
    $Ctls_raw[$LowestIndex-1]=0;
    $Atls_raw[$LowestIndex-1]=0;

    for ($d = $LowestIndex; $d <= $HighestIndex; $d++) {
//        $Atls_raw[$d]=$Atls_raw[$d-1]*exp(-1/CONF_ATL_DAYS)+($Trimps_raw[$d]-$Atls_raw[$d-1])*(1-exp(-1/CONF_ATL_DAYS));
//        $Ctls_raw[$d]=$Ctls_raw[$d-1]*exp(-1/CONF_CTL_DAYS)+($Trimps_raw[$d]-$Ctls_raw[$d-1])*(1-exp(-1/CONF_CTL_DAYS));
        $Atls_raw[$d]=($Atls_raw[$d-1]+$Trimps_raw[$d-1])*exp(-1/CONF_ATL_DAYS);
        $Ctls_raw[$d]=($Ctls_raw[$d-1]+$Trimps_raw[$d-1])*exp(-1/CONF_CTL_DAYS);
        if ($Atls_raw[$d]>$maxATL) $maxATL=$Atls_raw[$d];
        if ($Ctls_raw[$d]>$maxCTL) $maxCTL=$Ctls_raw[$d];
    }


    for ($d = $LowestIndex; $d <= $HighestIndex; $d++) {
		$index = Plot::dayOfYearToJStime($StartYear, $d - $AddDays + $StartDayInYear);
        $ATLs[$index]    = round( 1* round($Atls_raw[$d]) );
//        $ATLs[$index]    = round(100 * round(array_sum(array_slice($Trimps_raw, $d - CONF_ATL_DAYS, CONF_ATL_DAYS)) / CONF_ATL_DAYS) / Trimp::maxATL());
        $CTLs[$index]    = round(1 * round($Ctls_raw[$d]) );
//        $CTLs[$index]    = round(100 * round(array_sum(array_slice($Trimps_raw, $d - CONF_CTL_DAYS, CONF_CTL_DAYS)) / CONF_CTL_DAYS) / Trimp::maxCTL());
        $TSBs[$index]    = round(1 * round($Ctls_raw[$d]) )-
            round(4 * round($Atls_raw[$d]));
//        $TSBs[$index]    = round(100 * round(array_sum(array_slice($Trimps_raw, $d - CONF_CTL_DAYS, CONF_CTL_DAYS)) / CONF_CTL_DAYS) / Trimp::maxCTL())-
//            round(100 * round(array_sum(array_slice($Trimps_raw, $d - CONF_ATL_DAYS, CONF_ATL_DAYS)) / CONF_ATL_DAYS) / Trimp::maxCTL());
        $CTLs[$index]-$ATLs[$index];
        $TRIMPs[$index]    = $Trimps_raw[$d];
        $TBs[$index]    = $Ctls_raw[$d-1]*(exp(1/CONF_CTL_DAYS)-1);
		//$TRIMPs[$index]    = $Trimps_raw[$d-2];
		if ($d > $HighestIndex - 45) {
			$FutureCTLs[$index]    = round(1 * round($Ctls_raw[$d]) );
		}
		$VDOT_slice      = array_slice($VDOTs_raw, $d - CONF_VDOT_DAYS, CONF_VDOT_DAYS);
		$Durations_slice = array_slice($Durations_raw, $d - CONF_VDOT_DAYS, CONF_VDOT_DAYS);
		$VDOT_sum        = array_sum($VDOT_slice);
		$Durations_sum   = array_sum($Durations_slice);

        if ( $VDOTs_raw[$d]) $VDOTsday[$index]= JD::correctVDOT($VDOTs_raw[$d]/$Durations_raw[$d]);

		if (count($VDOT_slice) != 0 && $Durations_sum != 0)
			$VDOTs[$index]  = JD::correctVDOT($VDOT_sum / $Durations_sum);

		// Only for debuggin purposes
		if ($DebugAllValues) {
			$CTL = Trimp::CTLinPercent($index/1000);
			$ATL = Trimp::ATLinPercent($index/1000);
			$VDOT = JD::calculateVDOTform($index/1000);
			$VDOT_plot = (isset($VDOTs[$index])) ? round($VDOTs[$index],5) : 0;

			$checkFailes = $CTLs[$index] != $CTL || $ATLs[$index] != $ATL || $VDOT_plot != $VDOT;
			$textMessage = date('d.m.Y H:i', $index/1000).': '.$CTLs[$index].'/'.$ATLs[$index].'/'.$VDOT_plot.' - calculated: '.$CTL.'/'.$ATL.'/'.$VDOT.'<br>';

			if ($checkFailes)
				echo HTML::error($textMessage);
			else
				echo HTML::info($textMessage);
		}
	}
} else {
	$DataFailed = true;
}

$Plot = new Plot("form".$_GET['y'], 1100, 450);

$Plot->Data[] = array('label' => __('Shape (CTL)'), 'color' => '#008800', 'data' => $CTLs);
$Plot->Data[] = array('label' => 'Future form', 'color' => '#DDDDDD', 'data' => $FutureCTLs);
if (count($ATLs) < $MaxATLPoints)
	$Plot->Data[] = array('label' => __('Fatigue (ATL)'), 'color' => '#AA1111', 'data' => $ATLs);
$Plot->Data[] = array('label' => 'TSB', 'color' => '#BBBB00', 'data' => $TSBs);
$Plot->Data[] = array('label' => 'VDOTavg', 'color' => '#000000', 'data' => $VDOTs, 'yaxis' => 2);
$Plot->Data[] = array('label' => 'TRIMP', 'color' => '#5555EE', 'data' => $TRIMPs, 'yaxis' => 3);
$Plot->Data[] = array('label' => 'TB', 'color' => '#9999FF', 'data' => $TBs, 'yaxis' => 3);
$Plot->Data[] = array('label' => 'VDOT', 'color' => '#444444', 'data' => $VDOTsday, 'yaxis' => 2);

$Plot->enableTracking();
$Plot->enableSelection('x', '', false);

$Plot->setMarginForGrid(5);
$Plot->setLinesFilled(array(0,1));
$Plot->setLineWidth(2,2);
$Plot->setLineWidth(3,2);
$Plot->setXAxisAsTime();
$Plot->setXAxisTimeFormat('%d %b');

if (!$All)
	$Plot->setXAxisLimitedTo($Year);

$Ymax=max($maxCTL,$maxATL);

$Plot->addYAxis(1, 'left');
//$Plot->addYUnit(1, '%');
$Plot->setYTicks(1, 1);
$Plot->setYLimits(1, -20, $Ymax);
$Plot->addYAxis(2, 'right');
$Plot->setYTicks(2, 1, 1);
$Plot->addYAxis(3, 'right');
$Plot->setYTicks(3, 1);
$Plot->setYLimits(3, -20*(1000/$Ymax), 1000);
$Plot->showAsBars(5);
$Plot->setBarWidth(5,1);
$Plot->setBarLineWidth(5,2);
$Plot->showAsPoints(7);

if ($All)
	$Plot->setTitle( __('Shape for all years') );
else
	$Plot->setTitle( __('Shape').' '.$Year);

if ($DataFailed)
	$Plot->raiseError( __('No data available.') );

//$Plot->addMarkingArea('x1', time().'000' , Plot::dayOfYearToJStime($StartYear, $HighestIndex - $AddDays + $StartDayInYear),  'rgba(200,200,200,0.7)');

$Plot->outputJavaScript();
