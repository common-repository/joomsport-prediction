<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


class JoomSportPredictionPostTypes {
    public function __construct() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 0 );
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 0 );
        
    }
   

    public static function register_post_types(){
        if ( post_type_exists('joomsport_tournament') ) {
            return;
        }
        
        $custom_posts = array(
            "joomsport-prediction-post-league",
            "joomsport-prediction-post-round",
        );
        
        foreach ($custom_posts as $cpost) {
            include_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'posts' . DIRECTORY_SEPARATOR . $cpost . '.php';
            $className = str_replace('-', '', $cpost);
            $postObject = new $className();
            $postObject->init();
        }
        flush_rewrite_rules();

    }
    
    public static function register_taxonomies(){
        
    }
 
}
new JoomSportPredictionPostTypes();

