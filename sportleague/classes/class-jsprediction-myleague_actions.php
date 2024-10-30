<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once JOOMSPORT_PREDICTION_PATH . DIRECTORY_SEPARATOR . "sportleague/helpers/js-helper.php";
require_once JOOMSPORT_PREDICTION_PATH . DIRECTORY_SEPARATOR . "sportleague/helpers/js-helper-mail.php";
require_once JOOMSPORT_PREDICTION_PATH . DIRECTORY_SEPARATOR . "sportleague/classes/class-jsprediction-league-row.php";

class jsPredictionMyLeagueActions{
    public $leagueID;
    
    public function __construct($id){
        $this->leagueID = (int) $id;
    }
    
    public function inviteUsersByEmail($name, $email){
        return jsPredictionHelperMail::sendInviteByEmail($this->leagueID, $name, $email);
    }
    public function loadFromLeague($leagueID){
        global $wpdb;
        $users = $wpdb->get_results("SELECT userID FROM {$wpdb->jswprediction_private_users} WHERE privateID = ".$this->leagueID." AND confirmed = '1'");
        foreach($users as $userID){
            jsPredictionHelper::addUserToPrivateLeague($this->leagueID,$userID,1);
        }
    }
    public function removeFromLeague($usersArray){
        foreach($usersArray as $userID){
            jsPredictionHelper::removeUserToPrivateLeague($this->leagueID,$userID);
        }
        
    }
    public function inviteSiteUsers($usersArray){
        foreach($usersArray as $userID){
            if(jsPredictionHelper::addUserToPrivateLeague($this->leagueID,$userID,0)){
                jsPredictionHelperMail::sendInviteSiteUser($this->leagueID,$userID);
            }
        }
    }
    public function getParticipants(){
        global $wpdb;
        $sql = "SELECT u.user_login, pu.userID, pu.confirmed "
                . " FROM {$wpdb->jswprediction_private_users} as pu"
                . " JOIN {$wpdb->prefix}users as u ON u.id = pu.userID"
                . " WHERE pu.privateID=".$this->leagueID
                . " AND pu.userID != ".get_current_user_id()
                . " ORDER BY u.user_login";
        
        return $wpdb->get_results($sql)?$wpdb->get_results($sql):array();
    }
    public function joinLeague(){
        global $wpdb;
        $sql = "UPDATE {$wpdb->jswprediction_private_users} "
            . " SET confirmed = '1'"
            . " WHERE userID = ".get_current_user_id()
            . " AND privateID=".$this->leagueID;
        return $wpdb->query($sql);

    }
    public function rejectLeague(){
        global $wpdb;
        $sql = "UPDATE {$wpdb->jswprediction_private_users} "
            . " SET confirmed = '2'"
            . " WHERE userID = ".get_current_user_id()
            . " AND privateID=".$this->leagueID;
        return $wpdb->query($sql);

    }
    public function leaveLeague(){
        $this->removeFromLeague(array(get_current_user_id()));


    }
    public function removeLeague(){
        global $wpdb;
        $json = array("error"=>"1", "msg"=>"");
        $row = new jsPredictionLeagueRow($this->leagueID);
        if($row->getOwnerID() == get_current_user_id()){

            $wpdb->delete(
                "{$wpdb->jswprediction_private_league}",
                array( 'id' => $this->leagueID ),
                array( '%d' )
            );
            $wpdb->delete(
                "{$wpdb->jswprediction_private_based}",
                array( 'privateID' => $this->leagueID ),
                array( '%d' )
            );
            $wpdb->delete(
                "{$wpdb->jswprediction_private_users}",
                array( 'privateID' => $this->leagueID ),
                array( '%d' )
            );
            $json = array("error"=>"0", "msg"=>"");
        }else{
            $json = array("error"=>"1", "msg"=>__( 'You have no permissions', 'joomsport-prediction' ));
        }
        return ($json);

    }
}