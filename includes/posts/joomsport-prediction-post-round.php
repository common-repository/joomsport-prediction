<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

require_once JOOMSPORT_PREDICTION_PATH_INCLUDES . 'meta-boxes' . DIRECTORY_SEPARATOR . 'joomsport-prediction-meta-round.php';

class JoomSportPredictionPostRound {
    public function __construct() {

    }
    public static function init(){
        self::register_post_types();
    }
    public static function register_post_types(){
        add_action("admin_init", array("JoomSportPredictionPostRound","admin_init"));
        add_action( 'edit_form_after_title',  array( 'JoomSportPredictionPostRound','round_edit_form_after_title') );
        add_action( 'wp_ajax_prediction_leaguemodal', array("JoomSportPredictionPostRound",'joomsport_prediction_leaguemodal') );
        add_action( 'wp_ajax_jspred_round_filters', array("JoomSportPredictionPostRound",'jspred_round_filters') );

        $slug = get_option( 'joomsporprediciontslug_jswprediction_round', null );
        if(!$slug){
            update_option('joomsporprediciontslug_jswprediction_round', 'jswprediction_round');
        }
        register_post_type( 'jswprediction_round',
                apply_filters( 'joomsport_prediction_register_post_type_round',
                        array(
                                'labels'              => array(
                                                'name'               => __( 'Round', 'joomsport-prediction' ),
                                                'singular_name'      => __( 'Round', 'joomsport-prediction' ),
                                                'menu_name'          => _x( 'Rounds', 'Admin menu name Players', 'joomsport-prediction' ),
                                                'add_new'            => __( 'Add Round', 'joomsport-prediction' ),
                                                'add_new_item'       => __( 'Add New Round', 'joomsport-prediction' ),
                                                'edit'               => __( 'Edit', 'joomsport-prediction' ),
                                                'edit_item'          => __( 'Edit Round', 'joomsport-prediction' ),
                                                'new_item'           => __( 'New Round', 'joomsport-prediction' ),
                                                'view'               => __( 'View Round', 'joomsport-prediction' ),
                                                'view_item'          => __( 'View Round', 'joomsport-prediction' ),
                                                'search_items'       => __( 'Search Round', 'joomsport-prediction' ),
                                                'not_found'          => __( 'No Round found', 'joomsport-prediction' ),
                                                'not_found_in_trash' => __( 'No Round found in trash', 'joomsport-prediction' ),
                                                'parent'             => __( 'Parent Round', 'joomsport-prediction' )
                                        ),
                                'description'         => __( 'This is where you can add new Round.', 'joomsport-prediction' ),
                                'public'              => true,
                                'show_ui'             => true,
                                'show_in_menu'        => 'joomsport_prediction',
                                'publicly_queryable'  => true,
                                'exclude_from_search' => true,
                                'hierarchical'        => false,
                                'query_var'           => true,
                                'supports'            => array( 'title'),
                                'show_in_nav_menus'   => true,
                                
                                'map_meta_cap' => true,
                            'rewrite' => array(
                                'slug' => $slug?$slug:'jswprediction_league'
                            )
                        )
                )
        );
    }
    public static function round_edit_form_after_title($post_type){
        global $post, $wp_meta_boxes;

        if($post_type->post_type == 'jswprediction_round'){
            
            echo JoomSportPredictionMetaRound::output($post_type);

        }
    

    }
    public static function admin_init(){
        global $post;

        add_meta_box('joomsport_player_personal_form_meta_box', __('Matches', 'joomsport-prediction'), array('JoomSportPredictionMetaRound','js_meta_matches'), 'jswprediction_round', 'jswprediction_tab_round1', 'default');
        $roundtype = isset($_REQUEST['roundtype'])?  intval($_REQUEST['roundtype']):0;
        if(!isset($_REQUEST['roundtype']) && isset($post->ID)){
            $roundtype = get_post_meta($post->ID, '_joomsport_round_roundtype', true);
        }

        add_meta_box('jswprediction_round_settings_form_meta_box', __('Settings', 'joomsport-prediction'), array('JoomSportPredictionMetaRound','js_meta_settings'), 'jswprediction_round', 'jswprediction_tab_round1', 'default');


        
        add_action( 'save_post',      array( 'JoomSportPredictionMetaRound', 'jswprediction_round_save_metabox' ), 10, 2 );
    }
    
    public static function joomsport_prediction_leaguemodal(){
        ?>
        <table>
            <tr>
                <td><?php echo __('Select League', 'joomsport-prediction');?>*</td>
                <td>
                    <?php
                    $args = array(
                        'posts_per_page' => -1,
                        'offset'           => 0,
                        'orderby'          => 'title',
                        'order'            => 'ASC',
                        'post_type'        => 'jswprediction_league',
                        'post_status'      => 'publish',

                    );
                    $posts_array = get_posts( $args );
                    $bulk = __('Select', 'joomsport-prediction');
                    if(count($posts_array)){
                        echo '<select name="jsw_round_league" id="jsw_round_league" class="jswf-chosen-select">';
                        echo '<option value="">'.esc_attr($bulk).'</option>';
                        foreach ($posts_array as $post) {
                            
                            echo '<option value="'.$post->ID.'">'.$post->post_title.'</option>';
                            

                        }
                        echo '</select>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><?php echo __('Round type', 'joomsport-prediction');?></td>
                <td>
                    <?php
                    $is_fieldA = array();
                    $is_fieldA[] = JoomSportHelperSelectBox::addOption(0, __("Matches", "joomsport-prediction"));
                    
                    //$is_fieldA[] = JoomSportHelperSelectBox::addOption(3, __("Special Round", "joomsport-prediction"));
                    echo JoomSportHelperSelectBox::Radio('jsp_round_type', $is_fieldA, 0,'',array('lclasses'=>array(1,1,1,1)));

                    ?>
                </td>
            </tr>
        </table>

        <?php
         echo '<table>
<tbody>
<tr><td>Outcome and Knockout round type available in <a href="https://joomsport.com/product-features/compare-editions.html" target="_blank">pro version</a>.
</td></tr>
</tbody>
</table>';
        exit();
    }
    
    public static function jspred_round_filters(){
        $season_id = intval($_POST["season_id"]);
        $matchday_id = intval($_POST["matchday_id"]);
        $leagueid = intval($_POST["leagueid"]);
        
        $json_array = array(
            "matches"=>"",
            "matchdays"=>'<option value="0">'.__('Select matchday','joomsport-prediction').'</option>');
        
        $seasons = get_post_meta($leagueid,'_jswprediction_league_seasons',true);
        if($season_id){
            $seasons = array($season_id);
        }
        //played != 1 AND season IN, Round?
        $metaquery = array();
        $metaquery[] = 
                array(
                    'relation' => 'AND',
                        array(
                    'key' => '_joomsport_home_team',
                    'value' => 0,
                    'compare' => '>'
                    ),

                    array(
                    'key' => '_joomsport_away_team',
                    'value' => 0,
                    'compare' => '>'
                    ),
                    array(
                    'key' => '_joomsport_match_played',
                    'value' => 1,
                    'compare' => '!='
                    ),
                    array(
                    'key' => '_joomsport_seasonid',
                    'value' => $seasons,
                    'compare' => 'IN'
                    )
                    
                ) ;
        
        $match_arr = array(
            'post_type' => 'joomsport_match',
            'posts_per_page'   => -1,
            'orderby' => 'id',
            'order'=>'ASC',
            'meta_query' => $metaquery   
        );
        
        if($matchday_id){
            $match_arr['tax_query'] = array(
                array(
                'taxonomy' => 'joomsport_matchday',
                'field' => 'term_id',
                'terms' => $matchday_id)
            );
        }
        $matches = new WP_Query($match_arr);
        if(is_array($matches->posts) && count($matches->posts)){
            for($intA = 0; $intA < count($matches->posts); $intA ++){
                $match = $matches->posts[$intA];
                $m_date = get_post_meta( $match->ID, '_joomsport_match_date', true ).' ';
               // $json_array["matches"][$match->ID] = $m_date.$match->post_title;
                $json_array["matches"] .= '<option value="'.$match->ID.'">'.$m_date.$match->post_title.'</option>';

            }
        }
        if($season_id){
            if(get_bloginfo('version') < '4.5.0'){
                $tx = get_terms('joomsport_matchday',array(
                    "hide_empty" => false
                ));
            }else{
                $tx = get_terms(array(
                    "taxonomy" => "joomsport_matchday",
                    "hide_empty" => false
                ));
            }

            for($intA=0;$intA<count($tx);$intA++){
                if(class_exists("JoomsportTermsMeta")){
                    $term_meta = JoomsportTermsMeta::getTermMeta($tx[$intA]->term_id);
                }else{
                    $term_meta = get_option("taxonomy_{$tx[$intA]->term_id}_metas");
                }

                if(isset($term_meta['season_id']) && $term_meta['season_id'] == $season_id){

                        $tmp = new stdClass();
                        $tmp->id = $tx[$intA]->term_id;
                        $tmp->name = $tx[$intA]->name;
                        $json_array["matchdays"] .= '<option value="'.$tx[$intA]->term_id.'">'.$tx[$intA]->name.'</option>';

                }
            }
        }
        
        echo json_encode($json_array);
        
        
        exit();
    }
    
}    
