<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class JSPredictionOpenLeagues_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct( array(
                'singular' => __( 'Open league', 'joomsport-prediction' ), 
                'plural'   => __( 'Open leagues', 'joomsport-prediction' ),
                'ajax'     => false 

        ) );
        /** Process bulk action */
        $this->process_bulk_action();

    }
    public static function get_stages( $per_page = 5, $page_number = 1 ) {

        global $wpdb;

        $sql = "SELECT pl.*,pb.leagueID FROM {$wpdb->jswprediction_private_league} as pl"
        . " JOIN {$wpdb->jswprediction_private_based} as pb ON pb.privateID=pl.id"
        . " WHERE pl.is_private='1'"
        . "GROUP BY pl.id";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
          $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
          $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;
    }
    
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT pl.*,pb.leagueID FROM {$wpdb->jswprediction_private_league} as pl"
        . " JOIN {$wpdb->jswprediction_private_based} as pb ON pb.privateID=pl.id"
        . " WHERE pl.is_private='1'"
        . "GROUP BY pl.id";

        return count($wpdb->get_results( $sql ));
    }
    public function no_items() {
        echo __( 'There is no open leagues.', 'joomsport-prediction' );
    }
    function column_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'jsprediction_delete_privateleague' );

        
        $title = '<strong>'. $item['leagueName']. '</strong>';

        $actions = array();
          
        

        return $title . $this->row_actions( $actions );
    }
    

    function get_columns() {
        $columns = array(
          
          'name'    => __( 'Name', 'sp' ),
          'leagueName' => __( 'Based on', 'joomsport-prediction' ),
          'leagueLink' => __( 'Link', 'joomsport-prediction' ),  
          'joinLink' => __( 'Join', 'joomsport-prediction' ),    
        );

        return $columns;
    }
    function column_default($item, $column_name){
        global $wpdb;
        switch($column_name){
            case 'leagueName':
                $basedOn = get_post($item["leagueID"]);
                
                return $basedOn->post_title;
                break;
            case 'leagueLink':
                
                $link = get_permalink($item["leagueID"]);
                $link = add_query_arg( 'prl', $item["id"], $link );
                return '<a href="'.$link.'" target="_blank">'.__( 'Link', 'joomsport-prediction' ).'</a>';
                break;
            case 'joinLink':
                $sql = "SELECT pu.id FROM {$wpdb->jswprediction_private_league} as pl"
        
                . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID = pl.id"
                . " WHERE pl.id=".absint( $item['id'] )." AND pu.userID = ".get_current_user_id();
                if(!$wpdb->get_var($sql)){
                    $join_nonce = wp_create_nonce( 'jsprediction_join_privateleague' );
                    $link = sprintf( '?page=%s&action=%s&gamestage=%s&_wpnonce=%s', esc_attr( $_REQUEST['page'] ), 'join', absint( $item['id'] ), $join_nonce );
                    return '<a href="'.$link.'"><input type="button" class="button button-primary" value="'.__( 'Join', 'joomsport-prediction' ).'" /></a>';
                }else{
                    return __( 'Joined', 'joomsport-prediction' );
                }
                break;
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    public function get_sortable_columns() {
        $sortable_columns = array(
          'name' => array( 'name', true )
        );

        return $sortable_columns;
    }
    public function get_bulk_actions() {
        $actions = array(
          
        );

        return $actions;
    }
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        

        $per_page     = $this->get_items_per_page( 'stages_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( array(
          'total_items' => $total_items, //WE have to calculate the total number of items
          'per_page'    => $per_page //WE have to determine how many items to show on a page
        ) );


        $this->items = self::get_stages( $per_page, $current_page );
    }
    public function process_bulk_action() {

        //Detect when a bulk action is being triggered...
        if ( 'join' === $this->current_action() ) {
          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );

          if ( ! wp_verify_nonce( $nonce, 'jsprediction_join_privateleague' ) ) {
            die( 'Error' );
          }
          else {
            self::join_league( absint( $_GET['gamestage'] ) );
            wp_redirect( esc_url(get_dashboard_url(). 'admin.php?page=jsprediction-page-openleagues' ) );
            exit;
          }

        }
        if ( 'join_mail' === $this->current_action() ) {
          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );

          
            self::join_league_mail( absint( $_GET['gamestage'] ) );
            wp_redirect( esc_url(get_dashboard_url(). 'admin.php?page=jsprediction-page-openleagues' ) );
            exit;
          

        }

        
    }
    public static function join_league( $id ) {
        global $wpdb;
        
        if(!get_current_user_id()){
            die();
        }
        $sql = "SELECT pu.id FROM {$wpdb->jswprediction_private_league} as pl"
        
        . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID = pl.id"
        . " WHERE pl.id={$id} AND pu.userID = ".get_current_user_id();
        
        if(!$wpdb->get_var($sql)){
            
            $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_users}(privateID,userID,confirmed) VALUES({$id},".  get_current_user_id().",'1')");
        }
        
    }
    public static function join_league_mail( $id ) {
        global $wpdb;
        
        if(!get_current_user_id()){
            die();
        }
        $sql = "SELECT pu.id FROM {$wpdb->jswprediction_private_league} as pl"
        
        . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID = pl.id"
        . " WHERE pl.id={$id} AND pu.userID = ".get_current_user_id();
        
        if($wpdb->get_var($sql)){
            
            $wpdb->query("UPDATE {$wpdb->jswprediction_private_users} SET confirmed = '1' WHERE  privateID = {$id} AND userID = ".  get_current_user_id());
        }
        
    }
    
}


class JSPredictionOpenLeagues_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		//add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2><?php echo __('My private leagues', 'joomsport-prediction');?>
                        </h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
                    
		</div>
	<?php

	}

	/**
	 * Screen options
	 */
	public function screen_option() {
            if(isset($_POST['wp_screen_options']['option'])){
                update_user_meta(get_current_user_id(), 'openleagues_per_page', $_POST['wp_screen_options']['value']);



            }
		$option = 'per_page';
		$args   = array(
			'label'   => 'Leagues',
			'default' => 5,
			'option'  => 'openleagues_per_page'
		);

		add_screen_option( $option, $args );

		$this->customers_obj = new JSPredictionOpenLeagues_List_Table();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

