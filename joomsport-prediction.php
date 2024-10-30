<?php
/*
Plugin Name: JoomSport Predictions
Plugin URI: http://joomsport.com
Description: Create sport predictions for your JoomSport matches
Version: 3.0.3
Author: BearDev
Author URI: http://BearDev.com
License: GPLv3
Requires at least: 4.0
Text Domain: joomsport-prediction
Domain Path: /languages/
*/

/* Copyright 2017
BearDev, JB SOFT LLC, BY (sales@beardev.com)
This program is free licensed software; 

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/
//error_reporting(E_ALL);
//ini_set("display_errors", 1); 
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}
define('JOOMSPORT_PREDICTION_PATH', plugin_dir_path( __FILE__ ));
define('JOOMSPORT_PREDICTION_PATH_INCLUDES', JOOMSPORT_PREDICTION_PATH  . 'includes' . DIRECTORY_SEPARATOR);
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-permalink.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-post-types.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-admin-install.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-templates.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-actions.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-delete.php';
require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'joomsport-prediction-widgets.php';


try{
    //select helper from joomsport plugin
    if(file_exists(JOOMSPORT_PREDICTION_PATH . '..' .DIRECTORY_SEPARATOR. 'joomsport-sports-league-results-management'.DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'joomsport-helper-selectbox.php' )){
        require_once JOOMSPORT_PREDICTION_PATH . '..' .DIRECTORY_SEPARATOR. 'joomsport-sports-league-results-management'.DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'joomsport-helper-selectbox.php';
        require_once JOOMSPORT_PREDICTION_PATH . '..' .DIRECTORY_SEPARATOR. 'joomsport-sports-league-results-management'.DIRECTORY_SEPARATOR. 'includes' .DIRECTORY_SEPARATOR. 'helpers' .DIRECTORY_SEPARATOR. 'joomsport-helper-objects.php';
    }
}catch(Exception $e){
    
}
register_activation_hook(__FILE__, array('JoomSportPredictionAdminInstall', '_installdb') );



