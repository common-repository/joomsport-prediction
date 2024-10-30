<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class JSPredictionLeagues_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct( array(
                'singular' => __( 'My private league', 'joomsport-prediction' ), 
                'plural'   => __( 'My private leagues', 'joomsport-prediction' ),
                'ajax'     => false 

        ) );
        /** Process bulk action */
        $this->process_bulk_action();

    }
    public static function get_stages( $per_page = 5, $page_number = 1 ) {

        global $wpdb;

        $sql = "SELECT pl.*,pb.leagueID FROM {$wpdb->jswprediction_private_league} as pl"
        . " JOIN {$wpdb->jswprediction_private_based} as pb ON pb.privateID=pl.id"
        . " LEFT JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID = pl.id"
        . " WHERE (pl.creatorID = ".get_current_user_id()
        . " OR pu.userID = ".get_current_user_id().")"
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
    public static function delete_stage( $id ) {
        global $wpdb;
        $sql = "SELECT creatorID FROM {$wpdb->jswprediction_private_league} WHERE id=".$id;
        if($wpdb->get_var($sql) != get_current_user_id()){
            die();
        }
        $wpdb->delete(
          "{$wpdb->jswprediction_private_league}",
          array( 'id' => $id ),
          array( '%d' )
        );
    }
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT pl.*,pb.leagueID FROM {$wpdb->jswprediction_private_league} as pl"
        . " JOIN {$wpdb->jswprediction_private_based} as pb ON pb.privateID=pl.id"
        . " LEFT JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID = pl.id"
        . " WHERE (pl.creatorID = ".get_current_user_id()
        . " OR pu.userID = ".get_current_user_id().")"
        . "GROUP BY pl.id";

        return count($wpdb->get_results( $sql ));
    }
    public function no_items() {
        echo __( 'There is no private leagues.', 'joomsport-prediction' );
    }
    function column_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'jsprediction_delete_privateleague' );

        
        global $wpdb;

        $sql = "SELECT creatorID FROM {$wpdb->jswprediction_private_league} WHERE id=".$item["id"];
        if($wpdb->get_var($sql) == get_current_user_id()){
            $title = '<strong><a href="'.get_admin_url(get_current_blog_id(), 'admin.php?page=jsprediction-myleagues-form&id='.absint( $item['id'] )).'">' . $item['leagueName'] . '</a></strong>';
        
            $actions = array(
            'delete' => sprintf( '<a href="?page=%s&action=%s&gamestage=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
          );
        }else{
            $title = '<strong>'. $item['leagueName']. '</strong>';
        
            $actions = array();
        }    
        

        return $title . $this->row_actions( $actions );
    }
    

    function get_columns() {
        $columns = array(
          
          'name'    => __( 'Name', 'sp' ),
          'leagueName' => __( 'Based on', 'joomsport-prediction' ),
          'leagueLink' => __( 'Link', 'joomsport-prediction' ),  
        );

        return $columns;
    }
    function column_default($item, $column_name){
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
        if ( 'delete' === $this->current_action() ) {
          // In our file that handles the request, verify the nonce.
          $nonce = esc_attr( $_REQUEST['_wpnonce'] );

          if ( ! wp_verify_nonce( $nonce, 'jsprediction_delete_privateleague' ) ) {
            die( 'Error' );
          }
          else {
            self::delete_stage( absint( $_GET['gamestage'] ) );
            wp_redirect( esc_url(get_dashboard_url(). 'admin.php?page=jsprediction-page-myleagues' ) );
            exit;
          }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

          $delete_ids = esc_sql( $_POST['bulk-delete'] );

          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
            self::delete_stage( $id );

          }

          wp_redirect( esc_url(get_dashboard_url(). 'admin.php?page=jsprediction-page-myleagues' ) );
          exit;
        }
    }
    
}


class JSPredictionLeagues_Plugin {

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
                        <a class="add-new-h2"
                                 href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=jsprediction-myleagues-form');?>"><?php echo __('Add new', 'joomsport-prediction')?></a>
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
                update_user_meta(get_current_user_id(), 'privateleagues_per_page', $_POST['wp_screen_options']['value']);



            }
		$option = 'per_page';
		$args   = array(
			'label'   => 'Leagues',
			'default' => 5,
			'option'  => 'privateleagues_per_page'
		);

		add_screen_option( $option, $args );

		$this->customers_obj = new JSPredictionLeagues_List_Table();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


class JSPredictionLeaguesNew_Plugin {
    public static function view(){

        global $wpdb;
        $table_name = $wpdb->jswprediction_private_league; 

        $message = '';
        $notice = '';

        // this is default $item which will be used for new records
        $default = array(
            'id' => 0,
            'leagueName' => '',
            'is_private' => 0,
            'creatorID' => get_current_user_id()
        );
        
        $item = array();
        // here we are verifying does this request is post back and have correct nonce
        if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
            // combine our default item with request params
            $item = shortcode_atts($default, $_REQUEST);
            // validate data, and if all ok save item to database
            // if id is zero insert otherwise update
            $item_valid = self::joomsport_gamestages_validate($item);
            if ($item_valid === true) {
                if ($item['id'] == 0) {
                    
                    $result = $wpdb->insert($table_name, $item);
                    $item['id'] = $wpdb->insert_id;
                    
                    //add league
                    if(intval($_POST["basedOn"])){
                        $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_based}(leagueID,privateID) VALUES(".intval($_POST["basedOn"]).",".$item['id'].")");
                    }
                    
                    //add users
                    if(isset($_POST["user_invited"]) && count($_POST["user_invited"])){
                        foreach($_POST["user_invited"] as $iUser){
                            $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_users}(privateID, userID) VALUES(".$item['id'].",".intval($iUser).")");
                            self::jswprediction_invite($item, $iUser);
                            
                        }
                        
                    }
                    
                    
                    if ($result) {
                        $message = __('Item was successfully saved', 'joomsport-prediction');
                    } else {
                        $notice = __('There was an error while saving item', 'joomsport-prediction');
                    }
                } else {
                    if($item["id"]){
                        $sql = "SELECT creatorID FROM {$wpdb->jswprediction_private_league} WHERE id=".$item["id"];
                        if($wpdb->get_var($sql) != get_current_user_id()){
                            die();
                        }
                    }
                    $result = $wpdb->update($table_name, $item, array('id' => $item['id']));
                    //add league
                    if(intval($_POST["basedOn"])){
                        $wpdb->query("DELETE FROM {$wpdb->jswprediction_private_based} WHERE privateID=".$item['id']);
                        $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_based}(leagueID,privateID) VALUES(".intval($_POST["basedOn"]).",".$item['id'].")");
                    }
                    
                    //add users
                    //$wpdb->query("DELETE FROM {$wpdb->jswprediction_private_users} WHERE privateID=".$item['id']);
                    $in_users = array();
                    if(isset($_POST["user_invited"]) && count($_POST["user_invited"])){
                        foreach($_POST["user_invited"] as $iUser){
                            $sql = "SELECT pu.id FROM {$wpdb->jswprediction_private_league} as pl"
        
                            . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID = pl.id"
                            . " WHERE pl.id=".absint($item['id'])." AND pu.userID = ".get_current_user_id();

                            if(!$wpdb->get_var($sql)){
                                $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_users}(privateID, userID) VALUES(".$item['id'].",".intval($iUser).")");
                                self::jswprediction_invite($item, $iUser);
                            }
                            $in_users[] = $iUser;
                        }
                        
                    }
                    if(count($in_users)){
                        $wpdb->query("DELETE FROM {$wpdb->jswprediction_private_users} WHERE userID NOT IN (".implode(",",$in_users).") AND privateID=".$item['id']);
                    }else{
                        $wpdb->query("DELETE FROM {$wpdb->jswprediction_private_users} WHERE privateID=".$item['id']);
                    }
                    
                    if ($result) {
                        $message = __('Item was successfully updated', 'joomsport-prediction');
                    } else {
                        $notice = __('There was an error while updating item', 'joomsport-prediction');
                    }
                }
                echo '<script> window.location="'.(esc_url(get_dashboard_url())).'admin.php?page=jsprediction-page-myleagues"; </script> ';
                
            } else {
                // if $item_valid not true it contains error message(s)
                $notice = $item_valid;
            }
        }
        else {
            // if this is not post back we load item to edit or give new one to create
            $item = $default;
            if (isset($_REQUEST['id'])) {
                $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
                if (!$item) {
                    $item = $default;
                    $notice = __('Item not found', 'joomsport-prediction');
                }
            }
        }
        if($item["id"]){
            $sql = "SELECT creatorID FROM {$wpdb->jswprediction_private_league} WHERE id=".$item["id"];
            if($wpdb->get_var($sql) != get_current_user_id()){
                die();
            }
        }
        // here we adding our custom meta box
        add_meta_box('joomsport_gamestage_form_meta_box', __('Details', 'joomsport-prediction'), array('JSPredictionLeaguesNew_Plugin','joomsport_gamestage_form_meta_box_handler'), 'joomsport-gamestages-form', 'normal', 'default');

        ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php echo __('Edit league page', 'joomsport-prediction')?> <a class="add-new-h2"
                                        href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=jsprediction-page-myleagues');?>"><?php echo __('back to list', 'joomsport-prediction')?></a>
            </h2>

            <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo $notice ?></p></div>
            <?php endif;?>
            <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo $message ?></p></div>
            <?php endif;?>

            <form id="form" method="POST">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
                <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                <input type="hidden" name="id" value="<?php echo $item['id'] ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content"  class="jsRemoveMB">
                            <?php /* And here we call our custom meta box */ ?>
                            <?php do_meta_boxes('joomsport-gamestages-form', 'normal', $item); ?>
                            <input type="submit" value="<?php echo __('Save & close', 'joomsport-prediction')?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
    public static function joomsport_gamestage_form_meta_box_handler($item)
    {
        global $wpdb;
        $is_field = array();
        $is_field[] = JoomSportHelperSelectBox::addOption(0, __("Closed", "joomsport-prediction"));
        $is_field[] = JoomSportHelperSelectBox::addOption(1, __("Open", "joomsport-prediction"));
        $lists['is_private'] = JoomSportHelperSelectBox::Radio('is_private', $is_field,$item['is_private'],'');
        
        $args = array(
                'offset'           => 0,
                'orderby'          => 'title',
                'order'            => 'ASC',
                'post_type'        => 'jswprediction_league',
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
        );
        $bLeagues = get_posts( $args );
        
        $query = "SELECT u.ID FROM {$wpdb->prefix}users as u"
                . " JOIN {$wpdb->jswprediction_private_users} as pm ON pm.userID = u.ID"
                . " WHERE pm.privateID = ".$item["id"];
        $exclude_users = $wpdb->get_col($query);
        //array_push($exclude_users, get_current_user_id());
        
        $query = "SELECT leagueID FROM {$wpdb->jswprediction_private_based} WHERE privateID=".intval($item["id"]);
        $based_league = $wpdb->get_col($query);
               
                
    ?>

    <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
        <tbody>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php echo __('League name', 'joomsport-prediction')?></label>
                </th>
                <td>
                    <input id="leagueName" name="leagueName" type="text" style="width: 95%" value="<?php echo esc_attr(isset($item['leagueName'])?$item['leagueName']:"")?>"
                           size="50" class="code"  required>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php echo __('Based on', 'joomsport-prediction')?></label>
                </th>
                <td>
                    <?php
                    if(count($bLeagues)){
                        echo '<select name="basedOn" class="jswf-chosen-select">';
                        foreach ($bLeagues as $tm) {
                            $selected = '';
                            if(count($based_league) && in_array($tm->ID, $based_league)){
                                $selected = ' selected';
                            }
                            
                            echo '<option value="'.$tm->ID.'" '.$selected.'>'.$tm->post_title.'</option>';
                        }
                        echo '</select>';
                    }else{
                        echo __('No leagues', 'joomsport-prediction');
                    }
                    ?>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php echo __('Type', 'joomsport-prediction')?></label>
                </th>
                <td>
                    <?php echo $lists['is_private'];?>
                </td>
            </tr>
            <tr class="form-field">
                <th valign="top" scope="row">
                    <label for="name"><?php echo __('Invite users', 'joomsport-prediction')?></label>
                </th>
                <td>
                    <?php
                    $users = get_users();
                    echo '<select multiple name="user_invited[]" class="jswf-chosen-select" data-placeholder="'.__('Invite users', 'joomsport-prediction').'" >';
                    foreach ( $users as $user ) {
                        $selected = '';
                        if(count($exclude_users) && in_array($user->ID, $exclude_users)){
                            $selected = ' selected';
                        }
                        echo '<option value="'.$user->ID.'" '.$selected.'>' . esc_html( $user->display_name ) . '</option>';
                    }
                    echo '</select>';
                    ?>
                </td>
            </tr>
            
        </tbody>
    </table>
    <?php
    }
    public static function joomsport_gamestages_validate($item)
    {
        $messages = array();

        if (empty($item['leagueName'])) $messages[] = __('Name is required', 'joomsport-prediction');
        if(!isset($_POST["basedOn"]) || !intval($_POST["basedOn"])){
            $messages[] = __('Based league not selected', 'joomsport-prediction');
        }
        //if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format', 'custom_table_example');
        //if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format', 'custom_table_example');
        //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
        //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
        //...

        if (empty($messages)) return true;
        return implode('<br />', $messages);
    }
    public static function jswprediction_invite($item,$user_id){
        $user = get_userdata($user_id);
        $wp_nonce = wp_create_nonce( 'jsprediction_join_privateleague_'.get_current_user_id().'_'.absint($item["id"]) );
        $wp_link = admin_url('admin.php?page=jsprediction-page-openleagues&action=join_mail&gamestage='.absint($item["id"]).'&_wpnonce='.$wp_nonce);
        $to = $user->user_email;
        $subject = 'Prediction League';
        $body = sprintf(__("%s invited you to compete in %s prediction league.%s Click to confirm. %s","joomsport-prediction"),$user->display_name,$item["leagueName"],'<br /><a href="'.$wp_link.'">','</a>');
        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail( $to, $subject, $body, $headers );
        
    }
}