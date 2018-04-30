<?php
/*
==============================================================================
	
==============================================================================
		INIT SECTION
==============================================================================
*/
$pathopen = isset($_REQUEST['pathopen']) ? $_REQUEST['pathopen'] : null;
// name of the language file that needs to be included

$language_file[] = 'tracking';
$language_file[] = 'scorm';

include('../inc/global.inc.php');

//includes for SCORM and LP
require_once('../lp/learnpath.class.php');
require_once('../lp/learnpathItem.class.php');
require_once('../lp/learnpathList.class.php');
require_once('../lp/scorm.class.php');
require_once('../lp/scormItem.class.php');
require_once(api_get_path(LIBRARY_PATH).'tracking.lib.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
require_once(api_get_path(LIBRARY_PATH).'usermanager.lib.php');
require_once (api_get_path(LIBRARY_PATH).'export.lib.inc.php');





$htmlHeadXtra[] = "<style type='text/css'>
/*<![CDATA[*/
.secLine {background-color : #E6E6E6;}
.content {padding-left : 15px;padding-right : 15px; }
.specialLink{color : #0000FF;}
/*]]>*/
</style>
<style media='print' type='text/css'>

</style>";


/*
-----------------------------------------------------------
	Constants and variables
-----------------------------------------------------------
*/
// regroup table names for maintenance purpose
$TABLETRACK_ACCESS      = Database::get_main_table(TABLE_STATISTIC_TRACK_E_LASTACCESS);
$tbl_stats_exercices 		= Database :: get_main_table(TABLE_STATISTIC_TRACK_E_EXERCISES);
$TABLETRACK_LINKS       = Database::get_main_table(TABLE_STATISTIC_TRACK_E_LINKS);
$TABLETRACK_DOWNLOADS   = Database::get_main_table(TABLE_STATISTIC_TRACK_E_DOWNLOADS);
$TABLETRACK_ACCESS_2    = Database::get_main_table("track_e_access");
$TABLECOURSUSER	        = Database::get_main_table(TABLE_MAIN_COURSE_USER);
$TABLECOURSE	        = Database::get_main_table(TABLE_MAIN_COURSE);
$TABLECOURSE_LINKS      = Database::get_course_table(TABLE_LINK);
$table_user = Database::get_main_table(TABLE_MAIN_USER);



$tbl_learnpath_main = Database::get_course_table('lp');
$tbl_learnpath_item = Database::get_course_table('lp_item');
$tbl_learnpath_view = Database::get_course_table('lp_view');
$tbl_learnpath_item_view = Database::get_course_table('lp_item_view');

$view = $_REQUEST['view'];


//Display::display_header($nameTools, "Tracking");
include(api_get_path(LIBRARY_PATH)."statsUtils.lib.inc.php");
include("../resourcelinker/resourcelinker.inc.php");


$ex_user_id =isset($_GET['ex_user_id'])?$_GET['ex_user_id']:"";
$mod_no=isset($_GET['mod_no'])?$_GET['mod_no']:"";
$score_ex=isset($_GET['score_ex'])?$_GET['score_ex']:"";
$score_rep1=isset($_GET['score_rep1'])?$_GET['score_rep1']:"";
$score_rep2=isset($_GET['score_rep2'])?$_GET['score_rep2']:"";
$coment=isset($_GET['coment'])?$_GET['coment']:"";

foreach($_POST as $index => $valeur) {
    $$index = Database::escape_string(trim($valeur));
}

$sql4 = "INSERT INTO $tbl_stats_exercices "."(exe_user_id,c_id,mod_no,score_ex,score_rep1,score_rep2,coment) " .
			"VALUES "."('$ex_user_id','0','$mod_no','$score_ex', '$score_rep1', '$score_rep2', '$coment' )";
			api_sql_query($sql4);// OR die("<p>Erreur Mysql<br/>$sql4<br/>".mysql_error()."</p>");
 header("location:../mySpace/myStudents.php?student=$ex_user_id");

?>
