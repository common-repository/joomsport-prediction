<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'meta-boxes' . DIRECTORY_SEPARATOR . 'joomsport-prediction-meta-league.php';

class JoomSportPredictionPostLeague {
    public function __construct() {

    }
    public static function init(){
        self::register_post_types();
    }
    public static function register_post_types(){
        add_action("admin_init", array("JoomSportPredictionPostLeague","admin_init"));
        add_action( 'edit_form_after_title',  array( 'JoomSportPredictionPostLeague','league_edit_form_after_title') );
        add_action( 'admin_print_scripts-post-new.php', array("JoomSportPredictionPostLeague",'joomsport_season_validate'), 11 );

        $slug = get_option( 'joomsporprediciontslug_jswprediction_league', null );
        if(!$slug){
            update_option('joomsporprediciontslug_jswprediction_league', 'jswprediction_league');
        }

        register_post_type( 'jswprediction_league',
                apply_filters( 'joomsport_prediction_register_post_type_league',
                        array(
                                'labels'              => array(
                                                'name'               => __( 'League', 'joomsport-prediction' ),
                                                'singular_name'      => __( 'League', 'joomsport-prediction' ),
                                                'menu_name'          => _x( 'Leagues', 'Admin menu name Matches', 'joomsport-prediction' ),
                                                'add_new'            => __( 'Add League', 'joomsport-prediction' ),
                                                'add_new_item'       => __( 'Add New League', 'joomsport-prediction' ),
                                                'edit'               => __( 'Edit', 'joomsport-prediction' ),
                                                'edit_item'          => __( 'Edit League', 'joomsport-prediction' ),
                                                'new_item'           => __( 'New League', 'joomsport-prediction' ),
                                                'view'               => __( 'View League', 'joomsport-prediction' ),
                                                'view_item'          => __( 'View League', 'joomsport-prediction' ),
                                                'search_items'       => __( 'Search League', 'joomsport-prediction' ),
                                                'not_found'          => __( 'No League found', 'joomsport-prediction' ),
                                                'not_found_in_trash' => __( 'No League found in trash', 'joomsport-prediction' ),
                                                'parent'             => __( 'Parent League', 'joomsport-prediction' )
                                        ),
                                'description'         => __( 'This is where you can add new League.', 'joomsport-prediction' ),
                                'public'              => true,
                                'show_ui'             => true,
                                'show_in_menu'        => 'joomsport_prediction',
                                'publicly_queryable'  => true,
                                'exclude_from_search' => true,
                                'hierarchical'        => false,
                                'query_var'           => true,
                                'supports'            => array( 'title' ),
                                'show_in_nav_menus'   => true,
                                
                                'map_meta_cap' => true,
                                'rewrite' => array(
                                    'slug' => $slug?$slug:'jswprediction_league'
                                )
                        )
                )
        );


       
    }

    public static function league_edit_form_after_title($post_type){
        global $post;

        if($post_type->post_type == 'jswprediction_league'){
            
            echo JoomSportPredictionMetaLeague::output($post_type);

        }
    

    }
    public static function admin_init(){

        add_meta_box('jswprediction_league_seasons_form_meta_box', __('Assign to season', 'joomsport-prediction'), array('JoomSportPredictionMetaLeague','js_meta_season'), 'jswprediction_league', 'jswprediction_tab_league1', 'default');
        add_meta_box('jswprediction_league_points_form_meta_box', __('League Points', 'joomsport-prediction'), array('JoomSportPredictionMetaLeague','js_meta_points'), 'jswprediction_league', 'jswprediction_tab_league1', 'default');
        add_meta_box('jswprediction_league_outcome_points_form_meta_box', __('Outcome League Points', 'joomsport-prediction'), array('JoomSportPredictionMetaLeague','js_meta_outcome_points'), 'jswprediction_league', 'jswprediction_tab_league1', 'default');

        add_action( 'save_post',      array( 'JoomSportPredictionMetaLeague', 'jswprediction_league_save_metabox' ), 10, 2 );
    }
    public static function joomsport_season_validate(){

        global $post_type;
        $post_for_check = array(
            "jswprediction_league",
            "jswprediction_round"
            );
        
        if( in_array($post_type, $post_for_check) )
        wp_enqueue_script( 'joomsport-prediction-admin-script', plugin_dir_url( __FILE__ ) . '../../assets/js/validate.js', array('jquery') );

    }
}  
add_filter('the_title', 'jspred_filter_privatetitle');
function jspred_filter_privatetitle($title) {
    global $wpdb, $post_type, $post;
    if(!$post){
        return $title;
    }
    if($title != $post->post_title){
        return $title;
    }
    $privateID = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
    $gc = isset($_REQUEST['gc'])?intval($_REQUEST['gc']):0;
    if($post_type == 'jswprediction_league' && $privateID){
        $query = "SELECT leagueName FROM {$wpdb->jswprediction_private_league} "
                . " WHERE id = ".$privateID;
        $private_title = $wpdb->get_var($query);
        if($private_title){
            return $private_title . " (".__("Based on",'joomsport-prediction')." <a href='".get_permalink($post->ID)."'>".$title."</a>)";
        }
    }
    return $title;
}