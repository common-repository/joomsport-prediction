<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class jsPredictionLeagueRow{
    private $leagueID;
    public function __construct($leagueID){
        $this->leagueID = $leagueID;
    }
    public function getUsersCount(){
        global $wpdb;
        $sql = "SELECT COUNT(pu.userID) "
            . " FROM {$wpdb->jswprediction_private_users} as pu"
            . " JOIN {$wpdb->prefix}users as u ON u.id = pu.userID"
            . " WHERE pu.privateID=".$this->leagueID
            . " AND pu.confirmed = '1'"
            . " ORDER BY u.user_login";
        return (int) $wpdb->get_var($sql);
    }
    public function getOwner(){
        global $wpdb;
        $sql = "SELECT u.user_login "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->prefix}users as u ON u.id=p.creatorID"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $uname =  $wpdb->get_var($sql);
        if($this->getOwnerID() == get_current_user_id()){
            $uname = "<b>".$uname." (me)</b>";
        }
         return $uname;
    }
    public function getOwnerID(){
        global $wpdb;
        $sql = "SELECT u.id "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->prefix}users as u ON u.id=p.creatorID"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        return $wpdb->get_var($sql);
    }
    public function getTitle(){
        global $wpdb;
        $sql = "SELECT p.leagueName "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        return $wpdb->get_var($sql);
    }
    public function getTitleLinked(){
        global $wpdb;
        $title = $this->getTitle();
        $sql = "SELECT b.leagueID "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $based = (int) $wpdb->get_var($sql);

        $link = get_permalink($based);
        $link = add_query_arg( 'prl', $this->leagueID, $link );
        return '<a href="'.$link.'">'.stripslashes($title).'</a>';
    }
    public function getLink(){
        global $wpdb;
        $title = $this->getTitle();
        $sql = "SELECT b.leagueID "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $based = (int) $wpdb->get_var($sql);

        $link = get_permalink($based);
        $link = add_query_arg( 'prl', $this->leagueID, $link );
        return $link;
    }
    public function getBasedLeague(){
        global $wpdb;
        $sql = "SELECT b.leagueID "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
            . " WHERE p.id=".$this->leagueID
            . " LIMIT 1"        ;
        $leagueID = (int) $wpdb->get_var($sql);
        if($leagueID){
            return get_the_title($leagueID);
        }

    }
    public function getActionsList(){
        $html = array();
        if($this->getOwnerID() == get_current_user_id()){
            $html = $this->getActionsListOwner();
        }else{
            $html = $this->getActionsListLeave();
        }
        return $html;
    }
    public function getActionsListPending(){
        $html = array();
        //join
        $html["join"] = '<input class="btn btn-success jpbtn-pos jpBtnJoin" type="button" value="'.__('Join', 'joomsport-prediction').'" />';
        //reject
        $html["reject"] = '<input class="btn btn-default jpbtn-neg jpBtnReject" type="button" value="'.__('Reject', 'joomsport-prediction').'" />';
        return $html;
    }
    public function getActionsListOwner(){
        $html = array();
        //delete league
        //$html["delete"] = '<i class="fa fa-trash-o jpBtnDelete" title="'.__('Delete', 'joomsport-prediction').'"></i>';
        //edit league
        $html["edit"] = '<input class="btn btn-default jpBtnEdit" type="button" value="'.__('Edit', 'joomsport-prediction').'" />';

        //invite
        $html["invite"] = '<input class="btn btn-success jpbtn-pos jpBtnInvite" type="button" value="'.__('Invite', 'joomsport-prediction').'" />';
        return $html;
    }
    public function getActionsListLeave(){
        $html = array();
        //leave
        $html["leave"] = '<input class="btn btn-primary jpbtn-neut jpBtnLeave" type="button" value="'.__('Leave', 'joomsport-prediction').'" />';
        return $html;
    }
}