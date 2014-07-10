<?php
// This script and data application were generated by AppGini 4.52
// Download AppGini for free from http://www.bigprof.com/appgini/download/

	$d=dirname(__FILE__);
	include("$d/defaultLang.php");
	include("$d/language.php");
	include("$d/lib.php");
	@include("$d/hooks/diseases.php");
	include("$d/diseases_dml.php");

	// mm: can the current member access this page?
	$perm=getTablePermissions('diseases');
	if(!$perm[0]){
		echo StyleSheet();
		echo "<div class=\"error\">".$Translation['tableAccessDenied']."</div>";
		echo '<script language="javaScript">setInterval("window.location=\'index.php?signOut=1\'", 2000);</script>';
		exit;
	}

	$x = new DataList;
	$x->TableName = "diseases";

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV=array(
		"`diseases`.`id`" => "ID",
		"`diseases`.`short_name`" => "Short name",
		"`diseases`.`latin_name`" => "Latin name",
		"`diseases`.`description`" => "Description",
		"`diseases`.`other_details`" => "Other details",
		"`diseases`.`comments`" => "Comments"
	);
	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV=array(
		"`diseases`.`id`" => "ID",
		"`diseases`.`short_name`" => "Short name",
		"`diseases`.`latin_name`" => "Latin name",
		"`diseases`.`description`" => "Description",
		"`diseases`.`other_details`" => "Other details",
		"`diseases`.`comments`" => "Comments"
	);
	// Fields that can be filtered
	$x->QueryFieldsFilters=array(
		"`diseases`.`id`" => "ID",
		"`diseases`.`short_name`" => "Short name",
		"`diseases`.`latin_name`" => "Latin name",
		"`diseases`.`description`" => "Description",
		"`diseases`.`other_details`" => "Other details",
		"`diseases`.`comments`" => "Comments"
	);

	// Fields that can be quick searched
	$x->QueryFieldsQS=array(
		"`diseases`.`id`" => "ID",
		"`diseases`.`short_name`" => "Short name",
		"`diseases`.`latin_name`" => "Latin name",
		"`diseases`.`description`" => "Description",
		"`diseases`.`other_details`" => "Other details",
		"`diseases`.`comments`" => "Comments"
	);

	$x->QueryFrom="`diseases` ";
	$x->QueryWhere='';
	$x->QueryOrder='';

	$x->DataHeight = 300;
	$x->AllowSelection = 1;
	$x->HideTableView = ($perm[2]==0 ? 1 : 0);
	$x->AllowDelete = $perm[4];
	$x->AllowInsert = $perm[1];
	$x->AllowUpdate = $perm[3];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 0;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowPrintingMultiSelection = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 20;
	$x->QuickSearch = 3;
	$x->QuickSearchText = $Translation["quick search"];
	$x->ScriptFileName = "diseases_view.php";
	$x->RedirectAfterInsert = "diseases_view.php?SelectedID=#ID#";
	$x->TableTitle = "Diseases";
	$x->PrimaryKey = "`diseases`.`id`";
	$x->DefaultSortField = '1';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth   = array(150, 150, 400);
	$x->ColCaption = array("Short name", "Latin name", "Description");
	$x->ColNumber  = array(2, 3, 4);

	$x->Template = 'templates/diseases_templateTV.html';
	$x->SelectedTemplate = 'templates/diseases_templateTVS.html';
	$x->ShowTableHeader = 1;
	$x->ShowRecordSlots = 0;
	$x->HighlightColor = '#FFF0C2';

	// mm: build the query based on current member's permissions
	if($perm[2]==1){ // view owner only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `diseases`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='diseases' and lcase(membership_userrecords.memberID)='".getLoggedMemberID()."'";
	}elseif($perm[2]==2){ // view group only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `diseases`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='diseases' and membership_userrecords.groupID='".getLoggedGroupID()."'";
	}elseif($perm[2]==3){ // view all
		// no further action
	}elseif($perm[2]==0){ // view none
		$x->QueryFields = array("Not enough permissions" => "NEP");
		$x->QueryFrom = '`diseases`';
		$x->QueryWhere = '';
		$x->DefaultSortField = '';
	}

	// handle date sorting correctly
	// end of date sorting handler

	// hook: diseases_init
	$render=TRUE;
	if(function_exists('diseases_init')){
		$args=array();
		$render=diseases_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: diseases_header
	$headerCode='';
	if(function_exists('diseases_header')){
		$args=array();
		$headerCode=diseases_header($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$headerCode){
		include("$d/header.php"); 
	}else{
		ob_start(); include("$d/header.php"); $dHeader=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%HEADER%%>', $dHeader, $headerCode);
	}

	echo $x->HTML;
	// hook: diseases_footer
	$footerCode='';
	if(function_exists('diseases_footer')){
		$args=array();
		$footerCode=diseases_footer($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$footerCode){
		include("$d/footer.php"); 
	}else{
		ob_start(); include("$d/footer.php"); $dFooter=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%FOOTER%%>', $dFooter, $footerCode);
	}
?>