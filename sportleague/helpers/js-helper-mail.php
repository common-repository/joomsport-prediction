<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class jsPredictionHelperMail
{
    public static function sendInviteByEmail($leagueID, $name, $email){
        global $wpdb;

        $current_user = wp_get_current_user();

        $subject = self::getMailSubject();

        $leagueRow = $wpdb->get_row("SELECT p.*,b.leagueID FROM {$wpdb->jswprediction_private_league} as p"
            ." JOIN {$wpdb->jswprediction_private_based} as b ON p.id=b.privateID"
            ." WHERE p.id={$leagueID}");

        if($leagueRow) {
            $url = get_permalink($leagueRow->leagueID);
            $url = add_query_arg(
                array(
                    'invitekey' => $leagueRow->invitekey
                ),
                $url
            );

            $url = '<a href="'.$url.'">'.$url.'</a>';

            $args = array(
                "league_name" => $leagueRow->leagueName,
                "based_on" => $name,
                "site_name" => get_bloginfo( 'name' ),
                "invite_link" => $url
            );
            $message = self::replaceMailText($args);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            return jsPredictionHelperMail::sendEmail( $email, $subject, $message, $headers );

        }
        return false;

    }
    public static function sendInviteSiteUser($leagueID, $userID){
        global $wpdb;

        $user_info = get_userdata($userID);

        $user_email = $user_info->user_email;

        $user_name = $user_info->first_name?$user_info->first_name:$user_info->user_login;

        $current_user = wp_get_current_user();

        $subject = self::getMailSubject();

        $leagueRow = $wpdb->get_row("SELECT p.*,b.leagueID FROM {$wpdb->jswprediction_private_league} as p"
            ." JOIN {$wpdb->jswprediction_private_based} as b ON p.id=b.privateID"
            ." WHERE p.id={$leagueID}");

        if($leagueRow) {
            $url = get_permalink($leagueRow->leagueID);
            $url = add_query_arg(
                array(
                    'invitekey' => $leagueRow->invitekey,
                ),
                $url
            );
            $url = '<a href="'.$url.'">'.$url.'</a>';

            $args = array(
                "league_name" => $leagueRow->leagueName,
                "based_on" => get_the_title($leagueRow->leagueID),
                "site_name" => get_bloginfo( 'name' ),
                "invite_link" => $url
            );
            $message = self::replaceMailText($args);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            return jsPredictionHelperMail::sendEmail( $user_email, $subject, $message, $headers );

        }
        return false;

    }


    public static function sendEmail($to, $subject, $body, $headers){
        return wp_mail( $to, $subject, $body, $headers );
    }

    public static function getMailText(){
        $mail_settings = get_option("joomsport_prediction_mail_settings","");

        return isset($mail_settings["invText"])?$mail_settings["invText"]:"You have been invited to participate {league_name} on {site_name}. Please login / register on site and press Join link below. {invite_link}";
    }
    public static function getMailSubject(){
        $mail_settings = get_option("joomsport_prediction_mail_settings","");
        return isset($mail_settings["invSubject"])?$mail_settings["invSubject"]:"You invited";
    }

    public static function replaceMailText($args){
        $text = self::getMailText();
        $preVars = array("league_name","based_on","site_name","invite_link");
        foreach($args as $key => $val){
            $text = str_replace("{".$key."}",$val,$text);
        }
        return $text;

    }

}
