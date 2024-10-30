<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomsportPredictionDelete {
    
    public static function init() {
        add_action('delete_post',array('JoomsportPredictionDelete','delete_joomsport_post'),10);
        add_action('jsOnMatchDelete',array('JoomsportPredictionDelete','deleteMatch'),10,1);
    }
    

    public static function delete_joomsport_post($post_id){
       global $post_type;
       switch($post_type){
           
           case 'joomsport_match':
               self::deleteMatch($post_id);
               break;
           

           default:
       }
    }
    
    public static function deleteMatch($post_id){
        global $wpdb;
        
        //delete round matches
        $wpdb->delete(
          "{$wpdb->jswprediction_round_matches}",
          array( 'match_id' => $post_id ),
          array( '%d' )
        );


    }

    

    
}


JoomsportPredictionDelete::init();
