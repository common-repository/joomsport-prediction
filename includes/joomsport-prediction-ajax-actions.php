<?php

/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

require_once JOOMSPORT_PREDICTION_PATH . DIRECTORY_SEPARATOR . "sportleague/classes/class-jsprediction-myleague_actions.php";
require_once JOOMSPORT_PREDICTION_PATH . DIRECTORY_SEPARATOR . "sportleague/classes/class-jsprediction-league-row.php";

class JoomsportPredictionAjaxActions{

    public static function ajaxInit(){
        add_action( 'wp_ajax_jspred_private_league_add', array("JoomsportPredictionAjaxActions",'jspred_private_league_add') );
        add_action( 'wp_ajax_nopriv_jspred_private_league_add', array("JoomsportPredictionAjaxActions",'jspred_private_league_add') );

        add_action( 'wp_ajax_jspred_private_league_invite', array("JoomsportPredictionAjaxActions",'jspred_private_league_invite') );
        add_action( 'wp_ajax_nopriv_jspred_private_league_invite', array("JoomsportPredictionAjaxActions",'jspred_private_league_invite') );

        add_action( 'wp_ajax_jspred_private_remove_part', array("JoomsportPredictionAjaxActions",'jspred_private_remove_part') );
        add_action( 'wp_ajax_nopriv_jspred_private_remove_part', array("JoomsportPredictionAjaxActions",'jspred_private_remove_part') );

        add_action( 'wp_ajax_jspred_private_join', array("JoomsportPredictionAjaxActions",'jspred_private_join') );
        add_action( 'wp_ajax_nopriv_jspred_private_join', array("JoomsportPredictionAjaxActions",'jspred_private_join') );

        add_action( 'wp_ajax_jspred_private_reject', array("JoomsportPredictionAjaxActions",'jspred_private_reject') );
        add_action( 'wp_ajax_nopriv_jspred_private_reject', array("JoomsportPredictionAjaxActions",'jspred_private_reject') );

        add_action( 'wp_ajax_jspred_private_leave', array("JoomsportPredictionAjaxActions",'jspred_private_leave') );
        add_action( 'wp_ajax_nopriv_jspred_private_leave', array("JoomsportPredictionAjaxActions",'jspred_private_leave') );

        add_action( 'wp_ajax_jspred_private_update_league', array("JoomsportPredictionAjaxActions",'jspred_private_update_league') );
        add_action( 'wp_ajax_nopriv_jspred_private_update_league', array("JoomsportPredictionAjaxActions",'jspred_private_update_league') );

        add_action( 'wp_ajax_jspred_private_load_league', array("JoomsportPredictionAjaxActions",'jspred_private_load_league') );
        add_action( 'wp_ajax_nopriv_jspred_private_load_league', array("JoomsportPredictionAjaxActions",'jspred_private_load_league') );

        add_action( 'wp_ajax_jspred_private_users', array("JoomsportPredictionAjaxActions",'jspred_private_users') );
        add_action( 'wp_ajax_nopriv_jspred_private_users', array("JoomsportPredictionAjaxActions",'jspred_private_users') );

        add_action( 'wp_ajax_jspred_private_remove_league', array("JoomsportPredictionAjaxActions",'jspred_private_remove_league') );
        add_action( 'wp_ajax_nopriv_jspred_private_remove_league', array("JoomsportPredictionAjaxActions",'jspred_private_remove_league') );

    }

    public static function jspred_private_league_add(){

        $return = array("error"=>0,"msg"=>"","leagueid"=>0,"invite" => '',"partic"=>array(), "tdaction" => '');
        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }else{
            if(!sanitize_text_field($_POST["league_name"]) || !intval($_POST["base_league"])){
                $return["error"] = 1;
                $return["msg"] = __("League name not specified",'joomsport-prediction');
                echo json_encode($return);
                exit();
            }

            global $wpdb;
            $table_name = $wpdb->jswprediction_private_league;

            $token = JoomsportPredictionAjaxActions::generateToken();
            $item = array(
                'id' => 0,
                'leagueName' => sanitize_text_field(strip_tags($_POST["league_name"])),
                'is_private' => 0,
                'creatorID' => get_current_user_id(),
                'invitekey' => $token,
            );

            $result = $wpdb->insert($table_name, $item);
            $item['id'] = $wpdb->insert_id;
            if($item['id']){
                $return["leagueid"] = $item['id'];
                if(intval($_POST["base_league"])){
                    $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_based}(leagueID,privateID) VALUES(".intval($_POST["base_league"]).",".$item['id'].")");
                }

                //add users
                if(isset($_POST["import_from"]) && intval($_POST["import_from"])){
                    $wpdb->query("INSERT INTO {$wpdb->jswprediction_private_users}(privateID,userID,confirmed) SELECT {$item['id']}, userID, confirmed FROM {$wpdb->jswprediction_private_users} WHERE confirmed=1 AND privateID = ".intval($_POST["import_from"]));


                }
                $url = get_permalink($_POST["base_league"]);
                $url = add_query_arg(
                    array(
                        'invitekey' => $token
                    ),
                    $url
                );
                $return["invite"] = $url;

                $obj = new jsPredictionMyLeagueActions($item['id']);
                $partic = $obj->getParticipants();
                jsPredictionHelper::addUserToPrivateLeague($item['id'],get_current_user_id(),1);

                $row = new jsPredictionLeagueRow($item['id']);
                $actions = $row->getActionsList();

                $return["edit"] = $actions["edit"];
                $return["delete"] = $actions["delete"];
                $return["invitation"] = $actions["invite"];
                $return["title_url"] = $row->getLink();
                $return["title"] = $row->getTitle();
                $return["based"] = $row->getBasedLeague();
                $return["owner"] = $row->getOwner();
                $return["users"] = $row->getUsersCount();

                $return["partic"] = $partic;

                $competition = get_the_title($_POST["base_league"]);


                $email_subject = jsPredictionHelperMail::getMailSubject();
                $args = array(
                    "league_name" => sanitize_text_field(strip_tags($_POST["league_name"])),
                    "based_on" => $competition,
                    "site_name" => get_bloginfo( 'name' ),
                    "invite_link" => "%0D%0A%0D%0A" . $url
                );
                $email_body = jsPredictionHelperMail::replaceMailText($args);


                $return["emaillink"] = "mailto:user@example.com?subject=".($email_subject)."&body=".($email_body);

            }
        }
        echo json_encode($return);
        exit();
    }
    public static function generateToken(){
        //Generate a random string.
        $token = openssl_random_pseudo_bytes(16);

        //Convert the binary data into hexadecimal representation.
        $token = bin2hex($token);

        JoomsportPredictionAjaxActions::checkToken($token);

        //Print it out for example purposes.
        return $token;
    }

    public static function checkToken($token){
        global $wpdb;
        $query = "SELECT id FROM {$wpdb->jswprediction_private_league} WHERE invitekey='".addslashes($token)."'";

        if($wpdb->get_var($query)){
            JoomsportPredictionAjaxActions::generateToken();
        }

    }

    public static function jspred_private_league_invite(){
        $return = array("error"=>0,"msg"=>"");
        parse_str($_POST["form"], $formadata);
        $leagueid = intval($_POST["leagueid"]);
        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);

            if(isset($formadata["user_invited"]) && count($formadata["user_invited"])){

                $obj->inviteSiteUsers($formadata["user_invited"]);

            }

            if(isset($formadata["invbyemail_name"]) && isset($formadata["invbyemail_email"])){
                for($intA=0;$intA<count($formadata["invbyemail_name"]);$intA++){
                    if($formadata["invbyemail_name"][$intA] && $formadata["invbyemail_email"][$intA]){
                        $res = $obj->inviteUsersByEmail($formadata["invbyemail_name"][$intA], $formadata["invbyemail_email"][$intA]);
                        if($res !== true){
                            $return["error"] = 1;
                            $return["msg"] .= __("Can't send email to ".$formadata["invbyemail_email"][$intA],'joomsport-prediction');
                        }
                    }
                }
            }
        }
        echo json_encode($return);
        exit();
    }

    public static function jspred_private_remove_part(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        $pid = intval($_POST["pid"]);
        $obj = new jsPredictionMyLeagueActions($leagueid);
        $obj->removeFromLeague(array($pid));

        $row = new jsPredictionLeagueRow($leagueid);

        $return["users"] = $row->getUsersCount();

        echo json_encode($return);
        exit();
    }

    public static function jspred_private_remove_league(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        $pid = intval($_POST["pid"]);

        $obj = new jsPredictionMyLeagueActions($leagueid);
        $return = $obj->removeLeague();


        echo json_encode($return);
        exit();
    }


    public static function jspred_private_join(){
        $return = array("error"=>0,"msg"=>"","tdaction"=>"");
        $leagueid = intval($_POST["leagueid"]);
        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);
            $obj->joinLeague();
            $row = new jsPredictionLeagueRow($leagueid);
            $actions = $row->getActionsList();

            $return["leave"] = $actions["leave"];
            $return["users"] = $row->getUsersCount();
            $return["owner"] = $row->getOwner();
        }
        echo json_encode($return);
        exit();
    }

    public static function jspred_private_reject(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);
            $obj->rejectLeague();
        }
        echo json_encode($return);
        exit();
    }

    public static function jspred_private_leave(){
        $return = array("error"=>0,"msg"=>"");
        $leagueid = intval($_POST["leagueid"]);
        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }else{
            $obj = new jsPredictionMyLeagueActions($leagueid);
            $obj->leaveLeague();
        }
        echo json_encode($return);
        exit();
    }

    public static function jspred_private_update_league(){
        $return = array("error"=>0,"msg"=>"","title"=>"");
        $leagueid = intval($_POST["leagueid"]);
        $import_from = intval($_POST["import_from"]);

        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }elseif(sanitize_text_field($_POST['league_name'])){
            global $wpdb;
            $query = "UPDATE {$wpdb->jswprediction_private_league} SET leagueName = '".sanitize_text_field(strip_tags($_POST['league_name']))."' WHERE id=".$leagueid." AND creatorID = ".get_current_user_id();
            $wpdb->query($query);
            $return["title"] = stripslashes(strip_tags($_POST['league_name']));
            if($import_from){
                $wpdb->query("INSERT IGNORE INTO {$wpdb->jswprediction_private_users}(privateID,userID,confirmed) SELECT {$leagueid}, userID, confirmed FROM {$wpdb->jswprediction_private_users} WHERE confirmed=1 AND privateID = ".intval($import_from));
                $row = new jsPredictionLeagueRow($leagueid);

                $return["users"] = $row->getUsersCount();
            }
        }else{
            $return["error"] = 1;
            $return["msg"] = __("League name not specified",'joomsport-prediction');

        }
        echo json_encode($return);
        exit();
    }

    public static function jspred_private_load_league(){


        $leagueid = intval($_POST["leagueid"]);
        $return = array("error"=>0,"msg"=>"","leagueid"=>$leagueid,"invite" => '',"partic"=>array());
        if(!get_current_user_id()){
            $return["error"] = 1;
            $return["msg"] = __("Please login",'joomsport-prediction');
        }else{
            global $wpdb;

            $leagueRow = $wpdb->get_row("SELECT p.*,b.leagueID FROM {$wpdb->jswprediction_private_league} as p"
                ." JOIN {$wpdb->jswprediction_private_based} as b ON p.id=b.privateID"
                ." WHERE p.id={$leagueid} AND p.creatorID=".get_current_user_id());

            if($leagueRow){

                $url = get_permalink($leagueRow->leagueID);
                $url = add_query_arg(
                    array(
                        'invitekey' => $leagueRow->invitekey
                    ),
                    $url
                );
                $return["invite"] = $url;

                $obj = new jsPredictionMyLeagueActions($leagueid);
                $partic = $obj->getParticipants();

                $return["partic"] = $partic;

                $competition = get_the_title($leagueRow->leagueID);

                $email_subject = "Invite";
                $email_body = "You invited to league %s based on %s.";
                $email_body = sprintf($email_body, $leagueRow->leagueName, $competition);
                $email_body .= "%0D%0A%0D%0A" . $url;
                $return["emaillink"] = "mailto:user@example.com?subject=".($email_subject)."&body=".($email_body);

            }
        }
        echo json_encode($return);
        exit();
    }

    public static function jspred_private_users(){
        global $wpdb;


        $q = sanitize_text_field($_POST["q"]);
        $leagueid = intval($_POST["leagueid"]);
        $return = array("results" => array());

        $sql = "SELECT pu.userID"
            . " FROM {$wpdb->jswprediction_private_users} as pu"
            . " JOIN {$wpdb->prefix}users as u ON u.id = pu.userID"
            . " WHERE pu.privateID=".$leagueid
            . " AND pu.confirmed IN (0,1)"
            . " ORDER BY u.user_login";

        $usrs = $wpdb->get_col($sql);

        $query = "SELECT ID as id, user_login as text FROM {$wpdb->prefix}users"
            ." WHERE user_login LIKE '%".$q."%'"
            .(count($usrs)?" AND ID NOT IN (".implode(",",$usrs).")":"")
            ." ORDER BY user_login LIMIT 25";
        $return["results"] = $wpdb->get_results($query);
        echo json_encode($return);
        exit();
    }

}

add_action( 'admin_init', array('JoomsportPredictionAjaxActions','ajaxInit') );