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

require_once __DIR__.DIRECTORY_SEPARATOR.'JSPTScore.php';

class JSPTScoreBonus extends JSPTScore{
    public function __construct() {
        global $wpdb;
        $this->row = $wpdb->get_row("SELECT * FROM {$wpdb->jswprediction_types} WHERE identif='ScoreBonus'");


    }
    public function getScore($match, $results) {
        if($match->score1 == $results['score1'] XOR $match->score2 == $results['score2']){
            return true;
        }else{
            return false;
        }
    }
}
