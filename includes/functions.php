<?php

if( function_exists( 'cgcci_quiz_button' ) )
	return;

function cgcci_quiz_button( $lesson_id = NULL ){
	global $cgcci_plugin;
	return $cgcci_plugin->quiz_button( $lesson_id );
}

function cgcci_report_card(){
	global $cgcci_plugin;
	return $cgcci_plugin->report_card();
}

function cgcci_leaderboard(){
	global $cgcci_plugin;
	return $cgcci_plugin->leaderboard();
}