<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

class jswp_prediction_user_stat extends WP_Widget {

  function __construct() {
    parent::__construct('jswp_prediction_user_stat', __('My prediction stats', 'joomsport-sports-league-results-management'),
      array( 'description' => __( 'My prediction stats', 'joomsport-sports-league-results-management' ), )
      );
  }


    public function widget( $args, $instance ) {
        global $post, $post_type;
        $user = new WP_User(get_current_user_id());
        if(!$user->ID){
            return;
        }
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $args['before_widget'];
        if ( ! empty( $title ) ){
            echo $args['before_title'] . $title . $args['after_title'];
        }
        $leagueID = $instance['league_id'];
        if($instance['league_on_fly']) {
            if ($post_type == 'jswprediction_league') {
                $leagueID = $post->ID;
            } elseif ($post_type == 'jswprediction_round') {
                $leagueID = get_post_meta($post->ID, '_joomsport_round_leagueid', true);
            }
        }

        if(!$leagueID){
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jsselect2',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/js/select2.min.js');
        wp_enqueue_style('jscssselect2',plugin_dir_url( __FILE__ ).'../../joomsport-sports-league-results-management/sportleague/assets/css/select2.min.css');
        wp_enqueue_script('jsbootstrap-js','https://maxcdn.bootstrapcdn.com/bootstrap/4.2.0/js/bootstrap.min.js',array ( 'jquery' ));
        wp_enqueue_script( 'jswprediction-predfe-js', plugins_url('../sportleague/assets/js/jsprediction.js', __FILE__), array( 'wp-i18n' ) );
        wp_set_script_translations('jswprediction-predfe-js', 'joomsport-prediction');
        wp_enqueue_style('jsprediction',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/prediction.css');
        wp_enqueue_style('jscssfont','//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');


        require_once JOOMSPORT_PREDICTION_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'sportleague.php';
        require_once JOOMSPORT_PREDICTION_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'objects' . DIRECTORY_SEPARATOR . 'class-jsport-prediction-prleaders.php';

        $obj = new classJsportPrleaders($leagueID);
        $myPos = $obj->lists['mypos'];

        require JOOMSPORT_PREDICTION_PATH_VIEWS . 'widgets' . DIRECTORY_SEPARATOR . 'user-stat.php';

        echo $args['after_widget'];
    }

    // Widget Backend 
    public function form( $instance ) {
        global  $wpdb;

        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
            $league_id = $instance['league_id'];
            $league_on_fly = $instance['league_on_fly'];
        }
        else {
            $title = __( 'My prediction stats', 'joomsport-sports-league-results-management' );
            $league_id = $instance['league_id'];
            $league_on_fly = $instance['league_on_fly'];
        }
        // Widget admin form
        $args = array(
            'offset'           => 0,
            'orderby'          => 'title',
            'order'            => 'ASC',
            'post_type'        => 'jswprediction_league',
            'post_status'      => 'publish',
            'posts_per_page'   => -1,
        );
        $bLeagues = get_posts( $args );

        $tmp = array();
        for($intA=0;$intA<count($bLeagues);$intA++){
            $tmpA = new stdClass();
            $tmpA->id = $bLeagues[$intA]->ID;
            $tmpA->name = $bLeagues[$intA]->post_title;
            $tmp[] = $tmpA;
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php echo __( 'Title' ).':'; ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label><?php echo __("Select league", "joomsport-sports-league-results-management");?> *</label>

            <?php echo JoomSportHelperSelectBox::Simple($this->get_field_name( 'league_id' ), $tmp,$league_id, ' class="jsshrtcodesid" id="'.$this->get_field_id( 'league_id' ).'"');?>

        </p>
        <p>
            <label><?php echo __("League on the fly", "joomsport-sports-league-results-management");?></label>
            <input type="checkbox" name="<?=$this->get_field_name( 'league_on_fly' )?>" value="1" <?=$league_on_fly?" checked='checked'":"";?> />

        </p>

        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['league_id'] = ( ! empty( $new_instance['league_id'] ) ) ? strip_tags( $new_instance['league_id'] ) : '';
        $instance['league_on_fly'] = ( ! empty( $new_instance['league_on_fly'] ) ) ? esc_sql( $new_instance['league_on_fly'] ) : '';

        return $instance;
    }
}



// Register and load the widget
function jswp_prediction_joomsport_widget() {
	register_widget( 'jswp_prediction_user_stat' );

}
add_action( 'widgets_init', 'jswp_prediction_joomsport_widget' );
