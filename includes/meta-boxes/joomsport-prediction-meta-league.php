<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomSportPredictionMetaLeague {
    public static function output( $post ) {
        global $post, $thepostid, $wp_meta_boxes;
        
        
        $thepostid = $post->ID;

        wp_nonce_field( 'jswprediction_league_savemetaboxes', 'jswprediction_league_nonce' );
        ?>
        <div id="joomsportContainerBE">

                    <?php
                    do_meta_boxes(get_current_screen(), 'jswprediction_tab_league1', $post);
                    unset($wp_meta_boxes[get_post_type($post)]['jswprediction_tab_league1']);
                    ?>

        </div>
        

        <?php
    }
        
        
    public static function js_meta_season($post){

        $metadata = get_post_meta($post->ID,'_jswprediction_league_seasons',true);
        $posts_array = JoomSportHelperObjects::getSeasons();

        if(count($posts_array)){
            echo '<select id="js_seasons_list" name="js_seasons[]" class="jswf-chosen-select" data-placeholder="'.__('Add item','joomsport-prediction').'" multiple>';
            foreach ($posts_array as $key => $value) {
                for($intA = 0; $intA < count($value); $intA++){
                    $tm = $value[$intA];
                    $selected = '';
                    if($metadata && in_array($tm->id, $metadata)){
                        $selected = ' selected';
                    }
                    echo '<option value="'.$tm->id.'" '.$selected.'>'.$key .' '.$tm->name.'</option>';
                }

            }
            echo '</select>';
        }

        ?>
        
        <?php
    }
    public static function js_meta_points($post){
        global $wpdb;
        $predictions = array();
        $pred = get_post_meta($post->ID,'_jswprediction_league_points',true);
        $predictionsDB = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_types} WHERE ptype='score' ORDER BY ordering");
        
        $intZ = 0;
        
        for($intA = 0; $intA < count($predictionsDB); $intA++){
            $path = JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
            $classN = 'JSPT'.$predictionsDB[$intA]->identif;
            if(is_file($path . $classN.'.php')){
                require_once $path . $classN.'.php';
                if(class_exists($classN)){
                    $predictions[$intZ]['object'] = new $classN;
                    if(isset($pred[$predictionsDB[$intA]->id])){
                        $predictions[$intZ]['object']->setValue($pred[$predictionsDB[$intA]->id]);
                    }
                    $intZ++;
                }
            }
        }
        ?>
        <table class="jsbetable">
            <?php
            $ptype = '';
                for($intA = 0; $intA < count($predictions); $intA++){

                ?>
                <tr>
                    <td>
                        <?php echo $predictions[$intA]['object']->getTitle();?>
                    </td>
                    <td>
                        <?php echo $predictions[$intA]['object']->getAdminView();?>

                    </td>
                </tr>
                <?php
                }
                ?>
        </table>




    <?php
    }

    public static function js_meta_outcome_points($post){
        global $wpdb;
        $predictions = array();
        $pred = get_post_meta($post->ID,'_jswprediction_league_points',true);

        $predictionsDB = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_types} WHERE ptype='outcome' ORDER BY ordering");

        $intZ = 0;

        for($intA = 0; $intA < count($predictionsDB); $intA++){
            $path = JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
            $classN = 'JSPT'.$predictionsDB[$intA]->identif;
            if(is_file($path . $classN.'.php')){
                require_once $path . $classN.'.php';
                if(class_exists($classN)){
                    $predictions[$intZ]['object'] = new $classN;
                    if(isset($pred[$predictionsDB[$intA]->id])){
                        $predictions[$intZ]['object']->setValue($pred[$predictionsDB[$intA]->id]);
                    }
                    $intZ++;
                }
            }
        }
        ?>
        <table class="jsbetable">
            <?php
            $ptype = '';
            for($intA = 0; $intA < count($predictions); $intA++){

                ?>
                <tr>
                    <td>
                        <?php echo $predictions[$intA]['object']->getTitle();?>
                    </td>
                    <td>
                        <?php echo $predictions[$intA]['object']->getAdminView();?>

                    </td>
                </tr>
                <?php
            }
            ?>
        </table>

        <?php
    }

    public static function jswprediction_league_save_metabox($post_id, $post){
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['jswprediction_league_nonce'] ) ? $_POST['jswprediction_league_nonce'] : '';
        $nonce_action = 'jswprediction_league_savemetaboxes';
 
        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }
 
        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
 
        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
 
        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
 
        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        if('jswprediction_league' == $_POST['post_type'] ){
            self::saveMetaSeason($post_id);
            self::saveMetaPoints($post_id);
        }
    }
    
    private static function saveMetaSeason($post_id){

        $meta_array = array_map( 'sanitize_text_field', wp_unslash( $_POST['js_seasons'] ) );
        update_post_meta($post_id, '_jswprediction_league_seasons', $meta_array);
    }
    private static function saveMetaPoints($post_id){
        $meta_array = array_map( 'sanitize_text_field', wp_unslash( $_POST['pred'] ) );
        update_post_meta($post_id, '_jswprediction_league_points', $meta_array);
    }
    
}