<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-prediction-page-settings.php';


class JoomSportPredictionAdminInstall {
    
    public static function init(){
        //global $joomsportSettings;
        self::joomsport_languages();
        add_action( 'admin_menu', array('JoomSportPredictionAdminInstall', 'create_menu') );
        
        self::_defineTables();

    }


    public static function create_menu() {

        add_menu_page( __('JoomSport Predictions', 'joomsport-prediction'), __('JoomSport Predictions', 'joomsport-prediction'),
            'manage_options', 'joomsport_prediction', array('JoomSportPredictionAdminInstall', 'action'),'dashicons-icon-arrow-streamline-target');
        add_submenu_page( 'joomsport_prediction', __('Settings', 'joomsport-prediction'), __('Settings', 'joomsport-prediction'),
                'manage_options', 'joomsport_prediction_settings', array('JoomsportPredictionPageSettings', 'action') );
        
        
        
        // javascript
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-uidp-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        add_action('admin_enqueue_scripts', array('JoomSportPredictionAdminInstall', 'joomsport_admin_js'));
        add_action('admin_enqueue_scripts', array('JoomSportPredictionAdminInstall', 'joomsport_admin_css'));
        
    }

    public static function joomsport_fe_wp_head(){
        global $post,$post_type;
        $jsArray = array("jswprediction_league","jswprediction_round");
        if(in_array($post_type, $jsArray)){
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_style('jscssbtstrp',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/btstrp.css');
            wp_enqueue_style('jscssjoomsport',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/joomsport.css');
            wp_enqueue_style('jsprediction',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/prediction.css');
            wp_enqueue_script( 'jswprediction-predfe-js', plugins_url('../sportleague/assets/js/jsprediction.js', __FILE__), array( 'wp-i18n' ) );
            wp_set_script_translations('jswprediction-predfe-js', 'joomsport-prediction');
            wp_enqueue_style('jscssfont','//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
        }
             
     }

    public static function action(){
    
    }
    public static function joomsport_languages() {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'joomsport-prediction' );

            load_textdomain( 'joomsport-prediction', plugin_basename( dirname( __FILE__ ) . "/../languages/joomsport-prediction-$locale.mo" ));
            load_plugin_textdomain( 'joomsport-prediction', false, plugin_basename( dirname( __FILE__ ) . "/../languages" ) );
    }
    
    public static function joomsport_admin_js(){
        global $post_type;
        wp_enqueue_script( 'jswprediction-common-js', plugins_url('../assets/js/common.js', __FILE__) );
        wp_enqueue_media();
    }
    
    public static function joomsport_admin_css(){
        wp_enqueue_style( 'jswprediction-common-css', plugins_url('../assets/css/common.css', __FILE__) );
        wp_register_style('jswprediction-icons-css', plugins_url('../assets/css/iconstyles.css', __FILE__));
        wp_enqueue_style('jswprediction-icons-css');
        
    }
    
    public static function _defineTables()
    {
            global $wpdb;
            $wpdb->jswprediction_league = $wpdb->prefix . 'jswprediction_league';
            $wpdb->jswprediction_round = $wpdb->prefix . 'jswprediction_round';
            $wpdb->jswprediction_round_matches = $wpdb->prefix . 'jswprediction_round_matches';
            $wpdb->jswprediction_round_users = $wpdb->prefix . 'jswprediction_round_users';
            $wpdb->jswprediction_types = $wpdb->prefix . 'jswprediction_types';
            $wpdb->jswprediction_private_league = $wpdb->prefix . 'jswprediction_private_league';
            $wpdb->jswprediction_private_based = $wpdb->prefix . 'jswprediction_private_based';
            $wpdb->jswprediction_private_users = $wpdb->prefix . 'jswprediction_private_users';
            $wpdb->jswprediction_scorepredict = $wpdb->prefix . 'jswprediction_scorepredict';
            
            
            
    }

    public static function _installdb(){
        global $wpdb;
        flush_rewrite_rules();
        self::_defineTables();
        
        include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );

        $charset_collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
                if ( ! empty($wpdb->charset) )
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                if ( ! empty($wpdb->collate) )
                        $charset_collate .= " COLLATE $wpdb->collate";
        }


        $create_config_sql = "CREATE TABLE {$wpdb->jswprediction_league} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `name` varchar(255) NOT NULL,
                                        `seasons` varchar(50) NOT NULL,
                                        `predictions` varchar(255) NOT NULL,
                                        `options` varchar(255) NOT NULL,
                                        PRIMARY KEY ( `id` )) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_league, $create_config_sql );

        
        $create_config_sql = "CREATE TABLE {$wpdb->jswprediction_round} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `rname` varchar(255) NOT NULL,
                                        `ordering` tinyint(4) NOT NULL,
                                        `league_id` int(11) NOT NULL,
                                        `closedate` int(11) NOT NULL,
                                        PRIMARY KEY ( `id` )) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_round, $create_config_sql );
        
        $create_ef_sql = "CREATE TABLE {$wpdb->jswprediction_round_matches} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `round_id` int(11) NOT NULL,
                                        `match_id` int(11) NOT NULL,
                                        PRIMARY KEY ( `id` )) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_round_matches, $create_ef_sql );
        
        $create_ef_select_sql = "CREATE TABLE {$wpdb->jswprediction_round_users} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `user_id` int(11) NOT NULL,
                                        `round_id` int(11) NOT NULL,
                                        `prediction` text NOT NULL,
                                        `editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                        `filldate` timestamp NULL DEFAULT NULL,
                                        `points` int(11) NOT NULL,
                                        `place` smallint(4) NOT NULL,
                                        `filled` smallint(4) NOT NULL DEFAULT '0',
                                        `options` text NOT NULL,
                                        `success` smallint(4) NOT NULL DEFAULT '0',
                                        PRIMARY KEY  (`id`),UNIQUE KEY `user_id` (`user_id`,`round_id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_round_users, $create_ef_select_sql );
        
        $create_events_sql = "CREATE TABLE {$wpdb->jswprediction_types} (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `name` varchar(150) NOT NULL,
                                        `identif` varchar(100) NOT NULL,
                                        `ptype` varchar(100) NOT NULL,
                                        `ordering` tinyint(4) NOT NULL,
                                        `showtype` varchar(1) NOT NULL DEFAULT '0',
                                        `options` text NOT NULL,
                                        PRIMARY KEY  (`id`)) $charset_collate;";
        maybe_create_table( $wpdb->jswprediction_types, $create_events_sql );
        
        if(!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->jswprediction_types}")){
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 1, 'name' => esc_attr('Exact Result'), 'identif' => 'ScoreExact', 'ptype' => 'score', 'ordering' => 0, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 2, 'name' => esc_attr('Winner & Score difference'), 'identif' => 'ScoreSideAndDiff', 'ptype' => 'score', 'ordering' => 1, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));
            $wpdb->insert($wpdb->jswprediction_types,array('id' => 3, 'name' => esc_attr('Correct winner'), 'identif' => 'ScoreWinner', 'ptype' => 'score', 'ordering' => 2, 'showtype' => '0', 'options' => ''),array("%d","%s","%s","%s","%d","%s","%s"));
            
        }
        
        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->jswprediction_round_users} LIKE 'winner_side'");
        
        if (empty($is_col)) {
            $wpdb->query("ALTER TABLE ".$wpdb->jswprediction_round_users." ADD `winner_side` SMALLINT NOT NULL DEFAULT '0' , ADD `score_diff` SMALLINT NOT NULL DEFAULT '0'");

            
        }
        
    }
    

}

add_action( 'init', array( 'JoomSportPredictionAdminInstall', 'init' ), 4);
add_action( 'wp_enqueue_scripts', array('JoomSportPredictionAdminInstall','joomsport_fe_wp_head') );
