<?php
/**
 * This file contains class::ExporterHTML
 * @package Runalyze\Export\Types
 */
/**
 * Exporter for: HTML
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Export\Types
 */
class ExporterHTML extends ExporterAbstract {
	/**
	 * Display
	 */
	public function display() {
		$Code = str_replace(array("\r", "\n", "\t"), array("", "", ""), $this->getHTMLCode());

		$CodeField = new FormularTextarea('code', __('Code'), $Code);
		$CodeField->addCSSclass('fullwidth');
		$CodeField->addAttribute('rows', 8);

		$FieldsetCode = new FormularFieldset( __('HTML-Code') );
		$FieldsetCode->addField( $CodeField );
		$FieldsetCode->addInfo( __('Add this code to your blog/website.') );

		$FieldsetPreview = new FormularFieldset( __('Preview') );
		$FieldsetPreview->addBlock($Code);

		if (!$this->Training->isPublic())
			$FieldsetPreview->addWarning( __('Your training is private: There is no link included.') );

		$Formular = new Formular();
		$Formular->addFieldset($FieldsetCode);
		$Formular->addFieldset($FieldsetPreview);
		$Formular->setLayoutForFields( FormularFieldset::$LAYOUT_FIELD_W100 );
		$Formular->display();
	}

	/**
	 * Get HTML code for snippet
	 * @return string 
	 */
	protected function getHTMLCode() {
		$Url      = $this->Training->Linker()->publicUrl();
		$Date     = $this->Training->DataView()->getDate(false);
		$Time     = $this->Training->DataView()->getTimeString();
		$Title    = $this->Training->hasDistance() ? $this->Training->DataView()->getDistanceString().' ' : '';
		$Title   .= $this->Training->DataView()->getTitle();
		$Pace     = $this->Training->hasDistance() ? $this->Training->DataView()->getPace().'/km' : '';
		$Elev     = $this->Training->DataView()->getElevation();
		$Heart    = $this->Training->getPulseAvg() > 0 ? $this->Training->getPulseAvg().'bpm' : '';
		$Spans    = '';

		if ($Time != '')
			$Spans .= '<span class="runalyze-emb-time">'.$Time.'</span>';
		if ($Pace != '')
			$Spans .= '<span class="runalyze-emb-pace">'.$Pace.'</span>';
		if ($Heart != '')
			$Spans .= '<span class="runalyze-emb-heart">'.$Heart.'</span>';
		if ($Elev != '')
			$Spans .= '<span class="runalyze-emb-elev">'.$Elev.'</span>';

		$UrlLink = (System::isAtLocalhost()) ? '' : '<a href="'.$Url.'" class="runalyze-emb-share">'.$Url.'</a>';

		return '<div class="runalyze-emb">
	<a href="http://www.runalyze.de/" class="runalyze-emb-runalyze">runalyze.de</a>
	<strong>'.$Title.'</strong> <small>'.$Date.'</small><br>
	<div class="runalyze-emb-infos">
		'.$Spans.'
	</div>
	'.$UrlLink.'
	<div class="runalyze-clear"></div>
</div>
<script src="'.(!System::isAtLocalhost() ? System::getFullDomain().'/lib/embedded.local.js' : 'http://user.runalyze.de/lib/embedded.js').'"></script>';
	}
}