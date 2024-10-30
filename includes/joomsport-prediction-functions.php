<?php

/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


add_action( 'admin_init', 'joomsport_prediction_linkjs' );
function joomsport_prediction_linkjs() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'joomsport-sports-league-results-management/joomsport.php' ) ) {
        add_action( 'admin_notices', 'joomsport_prediction_linkjs_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function joomsport_prediction_linkjs_notice(){
    ?>
    <div class="error">
        <p>
            <?php echo sprintf(__('Predictions plugin requires activated %sJoomSport plugin%s', 'joomsport-prediction'),'<a href="https://wordpress.org/plugins/joomsport-sports-league-results-management/">','</a>');?>
        </p>
    </div>
    <?php
}

if (is_admin()){
    //this hook will create a new filter on the admin area for the specified post type
    add_action( 'restrict_manage_posts', function(){
        global $wpdb, $table_prefix;

        $post_type = (isset($_GET['post_type'])) ? addslashes($_GET['post_type']) : 'post';

        //only add filter to post type you want
        if ($post_type == 'jswprediction_round'){
            //query database to get a list of years for the specific post type:
            $args = array(
                'offset'           => 0,
                'orderby'          => 'title',
                'order'            => 'ASC',
                'post_type'        => 'jswprediction_league',
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
            );
            $bLeagues = get_posts( $args );

            //give a unique name in the select field
            ?><select name="jsw_admin_filter_league">
            <option value="">All leagues</option>

            <?php
            $current_v = isset($_GET['jsw_admin_filter_league'])? $_GET['jsw_admin_filter_league'] : '';
            if(count($bLeagues)) {
                foreach ($bLeagues as $bLeague) {
                    printf(
                        '<option value="%s"%s>%s</option>',
                        $bLeague->ID,
                        $bLeague->ID == $current_v ? ' selected="selected"' : '',
                        $bLeague->post_title
                    );
                }
            }
            ?>
            </select>
            <?php
        }
    });

    //this hook will alter the main query according to the user's selection of the custom filter we created above:
    add_filter( 'parse_query', function($query){
        global $pagenow;
        $post_type = (isset($_GET['post_type'])) ? addslashes($_GET['post_type']) : 'post';

        if ($query->query["post_type"] == 'jswprediction_round' && $post_type == 'jswprediction_round' && $pagenow=='edit.php' && isset($_GET['jsw_admin_filter_league']) && !empty($_GET['jsw_admin_filter_league'])) {
            $query->query_vars['meta_key'] = '_joomsport_round_leagueid';
            $query->query_vars['meta_value'] = $_GET['jsw_admin_filter_league'];
            $query->query_vars['meta_compare'] = '=';
        }
    });

    function jswp_round_columns($columns) {
        var_dump($columns);
        $new_columns = array(
            'league' => __('League', 'joomsport-prediction')
        );
        return array_merge($columns, $new_columns);
    }
    add_filter('manage_edit-jswprediction_round_columns' , 'jswp_round_columns');

    // let's say we have a CPT called 'product'
    function jswp_round_custom_column_values( $column, $post_id ) {

        switch ( $column ) {

            // in this example, a Product has custom fields called 'product_number' and 'product_name'
            case 'league'   :

                $leagueID = get_post_meta($post_id, '_joomsport_round_leagueid', true);
                echo get_the_title($leagueID);
                break;

        }
    }
    add_action( 'manage_jswprediction_round_posts_custom_column' , 'jswp_round_custom_column_values', 10, 2 );
}