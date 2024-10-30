<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class jsPredictionMyLeagues{
    public static function getActiveLeaguesList(){
        global $wpdb;
        $posts = jsPredictionHelper::getActiveMainLeaguesList();
        $postsArr = jsPredictionHelper::getPostsAsArray($posts);
        if(!count($postsArr)){
            return array();
        }
        $sql = "SELECT p.leagueName as post_title,p.id as ID "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
            . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID=b.privateID  AND pu.confirmed = '1'"
            . " WHERE pu.userID=".get_current_user_id()
            . " AND b.leagueID IN (" . implode(',', $postsArr) .")"
            . " GROUP BY p.id"
            . " ORDER BY p.leagueName"        ;

        return $wpdb->get_results($sql)?$wpdb->get_results($sql):array();
        
    }
    public static function getArchiveLeaguesList(){
        global $wpdb;
        $posts = jsPredictionHelper::getArchiveMainLeaguesList();
        $postsArr = jsPredictionHelper::getPostsAsArray($posts);
        if(!count($postsArr)){
            return array();
        }
        $sql = "SELECT p.leagueName as post_title,p.id as ID "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
            . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID=b.privateID  AND pu.confirmed = '1'"
            . " WHERE pu.userID=".get_current_user_id()
            . " AND b.leagueID IN (" . implode(',', $postsArr) .")"
            . " ORDER BY p.leagueName"        ;

        return $wpdb->get_results($sql)?$wpdb->get_results($sql):array();
    }
    public static function getMyLeagues(){
        global $wpdb;
        $sql = "SELECT p.leagueName as post_title,p.id as ID "
                . " FROM {$wpdb->jswprediction_private_league} as p"
                . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
                . " WHERE p.creatorID=".get_current_user_id()
                . " ORDER BY p.leagueName"        ;
        
        return $wpdb->get_results($sql)?$wpdb->get_results($sql):array();
        
    }
    public static function getParticipateLeagues(){

    }
    public static function getInvitedLeagues(){
        global $wpdb;
        $posts = jsPredictionHelper::getActiveMainLeaguesList();
        $postsArr = jsPredictionHelper::getPostsAsArray($posts);
        if(!count($postsArr)){
            return array();
        }
        $sql = "SELECT p.leagueName as post_title,p.id as ID "
            . " FROM {$wpdb->jswprediction_private_league} as p"
            . " JOIN {$wpdb->jswprediction_private_based} as b ON b.privateID = p.id"
            . " JOIN {$wpdb->jswprediction_private_users} as pu ON pu.privateID=b.privateID AND pu.confirmed = '0'"
            . " WHERE pu.userID=".get_current_user_id()
            . " AND b.leagueID IN (" . implode(',', $postsArr) .")"
            . " ORDER BY p.leagueName"        ;

        return $wpdb->get_results($sql)?$wpdb->get_results($sql):array();
    }
}