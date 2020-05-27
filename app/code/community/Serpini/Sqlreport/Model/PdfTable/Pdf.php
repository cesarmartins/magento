<?php
class Serpini_Sqlreport_Model_PdfTable_Pdf extends Zend_Pdf{
	
	const TOP=0;
	const RIGHT=1;
	const BOTTOM=2;
	const LEFT=3;
	const CENTER=4;	//horizontal center
	const MIDDLE=5; //vertical center
	const JUSTIFY=6;
	
	const DEF_TABLE_HEADER_BACKGROUNDCOLOR = "#e3eff1";
	const DEF_TABLE_FOOTER_BACKGROUNDCOLOR = "#e3eff1";
	
	const SIZE_A3                = '842:1188:';
	const SIZE_A3_LANDSCAPE      = '1188:842:';
	const SIZE_A5                = '418:576:';
	const SIZE_A5_LANDSCAPE      = '576:418:';
	
}
?>