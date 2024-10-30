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
class JSPTOutcome {
    public $row = null;
    public $value = '';
    public function __construct() {
        global $wpdb;
        $this->row = $wpdb->get_row("SELECT * FROM {$wpdb->jswprediction_types} WHERE identif='Outcome'");
        
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
        $jswp_tie = get_post_meta($round_id, 'jswp_tie', true);
        $scoreval = $this->getPrediction($match_id, $round_id, $user_id);

        $availArr = array("1","2");
        if($jswp_tie == 1){
            $availArr[] = "x";
        }
        if($canEdit){
            $html = '<div class="btn-group" role="group">';
            $html .= '<input type="button" class="jspredOutcomeBtn btn btn-default'.((isset($scoreval['choice'])&& ($scoreval['choice']=="1"))?' btn-primary':'').'" val-atr="1" value="&#xe079;" />';
            if($jswp_tie == 1) {
                $html .= '<input type="button" class="jspredOutcomeBtn btn btn-default' . ((isset($scoreval['choice']) && ($scoreval['choice'] == "x")) ? ' btn-primary' : '') . '" val-atr="x" value="&#xe014;" />';
            }
            $html .= '<input type="button" class="jspredOutcomeBtn btn btn-default'.((isset($scoreval['choice'])&& ($scoreval['choice']=="2"))?' btn-primary':'').'" val-atr="2" value="&#xe080;" />';
            $html .= '<input type="hidden" class="jspredOutcomeHidden" name="outcome['.$match_id.']" value="'.((isset($scoreval['choice']) && in_array($scoreval['choice'],$availArr))?($scoreval['choice']):'').'" />';
            $html .= '</div>';
        }else{
            $html = '';
            if(isset($scoreval['choice']) && in_array($scoreval['choice'],$availArr)){
                $txt = '';
                switch ($scoreval['choice']){
                    case '1': $txt = '&#xe079;'; break;
                    case 'x': $txt = '&#xe014;'; break;
                    case '2': $txt = '&#xe080;'; break;
                }
                if($txt){
                    $cl = '';
                    $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
                    if($m_played == '1' && isset($scoreval["points"]) && $scoreval["points"] > 0){
                        $cl = ' btn_success';
                    }elseif($m_played == '1' && isset($scoreval["points"]) && $scoreval["points"] == 0){
                        $cl = ' btn_danger';
                    }
                    $html = '<input type="button" class="jspredOutcomeBtn jspredOutcomeBtnDisable btn'.$cl.'" value="'.$txt.'" />';
                }

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
        if(isset($pred['outcome'][$match_id])){
            return $pred['outcome'][$match_id];
        }
        
    }
    
    public function validateData($user_id, $round_id, $jspJoker = 0){
        global $wpdb;
        $availArr = array("1","x","2");
        $outcome = classJsportRequest::get('outcome');
        $match_res = array();
        $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$user_id} AND round_id={$round_id}");

        $pred = $prediction?json_decode($prediction, true):null;
        if(isset($pred['outcome'])){
            $match_res = $pred['outcome'];
        }

        if(count($outcome)){
            
            foreach ($outcome as $key => $value) {
                if($jspJoker && isset($match_res[$key]["joker"])){
                    unset($match_res[$key]["joker"]);
                }
                if($value != '' && intval($key) && in_array($outcome[$key], $availArr)){
                    if($this->canEditMatch($key)){
                        $match_res[$key]["choice"] = $outcome[$key];
                        if($jspJoker && $jspJoker == $key){
                            $match_res[$key]["joker"] = 1;
                        }
                    }
                }
            }
        }
        return $match_res;
    }
    public function getScore($match, $results) {
        if($match->score1 == $match->score2 && $results['choice'] == 'x'
        || $match->score1 > $match->score2 && $results['choice'] == '1'
        || $match->score1 < $match->score2 && $results['choice'] == '2'){
            return true;
        }else{
            return false;
        }
    }
    private function canEditMatch($match_id){
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
