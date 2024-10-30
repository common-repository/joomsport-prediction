<?php

/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

class JoomsportPredictionTemplates {
    
    public static function init() {
        //add_action( 'parse_request', array('JoomsportPredictionTemplates', 'joomsport_parse_request') );
        add_filter( 'the_content', array( 'JoomsportPredictionTemplates', 'joomsport_content' ) );
        
    }

    public static function joomsport_parse_request( &$wp )
        {
            
            if (isset($_REQUEST['wpjoomsport'])) {
                include JOOMSPORT_PATH. 'templates'.DIRECTORY_SEPARATOR.'single_1.php';
                exit();
            }
            return;
        }
        
    public static function joomsport_content($content){
        if ( !in_the_loop() ) return $content;
        global $controllerPredictionSportLeague;
        if(is_singular('jswprediction_league') || is_singular('jswprediction_round')){
            require JOOMSPORT_PREDICTION_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'sportleague.php';
            
            if ( post_password_required() ) {
                echo get_the_password_form();
                return;
            }
            ob_start();
            $controllerPredictionSportLeague->execute();
            return ob_get_clean();
            
        }
        return $content;
    }   

    
}


JoomsportPredictionTemplates::init();
