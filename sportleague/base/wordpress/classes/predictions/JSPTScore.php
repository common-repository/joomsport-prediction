<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of JSPTScoreExact
 *
 * @author andreykarhalev
 */
class JSPTScore {
    public $row = null;
    public $value = '';
    public function __construct() {
        
        
    }
    public function getTitle(){
        return __($this->row->name,'joomsport-prediction');
    }
    public function setValue($val = ''){
        $this->value = $val;
    }
    public function getAdminView(){
        return '<input type="number" value="'.$this->value.'" name="pred['.$this->row->id.']" />';
    }
    public function getView($match_id, $round_id, $user_id, $canEdit){
        $scoreval = $this->getPrediction($match_id, $round_id, $user_id);
        
        if($canEdit){
            $html = '<input type="number" class="jsNumberNotNegative" value="'.(isset($scoreval['score1'])?$scoreval['score1']:'').'" name="pred_home['.$match_id.']" />';
            $html .= '&nbsp;:&nbsp;';
            $html .= '<input type="number" class="jsNumberNotNegative" value="'.(isset($scoreval['score2'])?$scoreval['score2']:'').'" name="pred_away['.$match_id.']" />';
        }else{
            $html = '';
            if(isset($scoreval['score1']) && isset($scoreval['score2'])){
                $html = $scoreval['score1'];
                $html .= '&nbsp;:&nbsp;';
                $html .= $scoreval['score2'];
            }
        }
        return $html;
    }
    public function getPrediction($match_id, $round_id, $user_id){
        global $wpdb;
        $query = "SELECT prediction"
                . " FROM {$wpdb->jswprediction_round_users}"
                . " WHERE user_id={$user_id}"
                . " AND round_id={$round_id}";
        $pred = $wpdb->get_var($query);        
        $pred = $pred?json_decode($pred, true):null;
        if(isset($pred['score'][$match_id])){
            return $pred['score'][$match_id];
        }
        
    }
    
    public function validateData($user_id, $round_id, $jspJoker = 0){
        global $wpdb;
        $pred_home = classJsportRequest::get('pred_home');
        $pred_away = classJsportRequest::get('pred_away');
        $match_res = array();
        $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$user_id} AND round_id={$round_id}");

        $pred = $prediction?json_decode($prediction, true):null;
        if(isset($pred['score'])){
            $match_res = $pred['score'];
        }

        if(count($pred_home)){
            
            foreach ($pred_home as $key => $value) {
                if($jspJoker && isset($match_res[$key]["joker"])){
                    unset($match_res[$key]["joker"]);
                }
                if($value != '' && intval($key) && $pred_away[$key] != ''){
                    if($this->canEditMatch($key)){
                        $match_res[$key]["score1"] = (int) $value;
                        $match_res[$key]["score2"] = (int) $pred_away[$key];
                        if($jspJoker && $jspJoker == $key){
                            $match_res[$key]["joker"] = 1;
                        }
                    }
                }
            }
        }
        return $match_res;
    }
    public function getScore($match, $results){
      
    }
    private function canEditMatch($match_id){
        global $jsDatabase;
        $user_id = get_current_user_id();
        if(!$user_id){
            return FALSE;
        }
        $m_date = get_post_meta( $match_id, '_joomsport_match_date', true );
        $m_time = get_post_meta( $match_id, '_joomsport_match_time', true );
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
        if($m_played == '1'){
            return false;
        }
        if(($m_date > date("Y-m-d", current_time( 'timestamp', 0 ))) || ($m_date == date("Y-m-d", current_time( 'timestamp', 0 )) && $m_time > date("H:i", current_time( 'timestamp', 0 )))){
            return true;
        }
        return false;
    }
    
}
