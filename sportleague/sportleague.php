<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<?php
//load defines
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'defines.php';
//add joomsport sportleague
require_once JOOMSPORT_PREDICTION_PATH . '..'. DIRECTORY_SEPARATOR.'joomsport-sports-league-results-management'.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'defines.php';
//load request class classJsportRequest
global $jsDatabase;
require_once JOOMSPORT_PATH_ENV.'classes'.DIRECTORY_SEPARATOR.'class-jsport-database-base.php';
$jsDatabase = new classJsportDatabaseBase();
require_once JOOMSPORT_PATH_SL_HELPERS.'js-helper-images.php';
//load link class
require_once JOOMSPORT_PATH_ENV.'classes'.DIRECTORY_SEPARATOR.'class-jsport-link.php';
require_once JOOMSPORT_PATH_ENV.'classes'.DIRECTORY_SEPARATOR.'class-jsport-date.php';
//load plugin class
require_once JOOMSPORT_PATH_CLASSES.'class-jsport-plugins.php';
require_once JOOMSPORT_PATH_CLASSES.'class-jsport-extrafields.php';
require_once JOOMSPORT_PATH_CLASSES.'class-jsport-pagination.php';
//global $jsConfig;
//$jsConfig = $joomsportSettings;
require_once JOOMSPORT_PATH_ENV.'classes'.DIRECTORY_SEPARATOR.'class-jsport-request.php';
require_once JOOMSPORT_PATH_SL_HELPERS.'js-helper.php';
require_once JOOMSPORT_PREDICTION_PATH.'sportleague'. DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'js-helper.php';
//execute task
require_once JOOMSPORT_PREDICTION_PATH.'sportleague'. DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'. DIRECTORY_SEPARATOR .'classes'.DIRECTORY_SEPARATOR.'class-jsport-prediction-controller.php';
$controllerPredictionSportLeague = new classJsportPredictionController();
// add css

?>