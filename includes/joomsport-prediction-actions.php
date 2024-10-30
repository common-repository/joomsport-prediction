<?php

/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

add_action('edited_joomsport_matchday', 'joomsport_prediction_mday_fire_save', 13, 2);
        
add_action( 'save_post', 'joomsport_prediction_fire_save', 13 );

function joomsport_prediction_mday_fire_save($term_id){
    global $wpdb;
    
    $metaquery = array();
        
    $metaquery[] = 
        array(
            'relation' => 'AND',
                array(
            'key' => '_joomsport_round_knock_mday',
            'value' => $term_id,
            'compare' => '='
            ),


        ) ;


    $roundsPosts = new WP_Query(array(
        'post_type' => 'jswprediction_round',
        'posts_per_page'   => -1,
        'orderby' => 'post_date',
        'order'=>'ASC',
        'meta_query' => $metaquery   
    ));

    for($intA=0;$intA<count($roundsPosts->posts);$intA++){
        JSPredictionsCalc::calculateKnockRound($roundsPosts->posts[$intA]->ID, $term_id);
    }
    
    
    
    $matches = get_posts(array(
            'post_type' => 'joomsport_match',
            'posts_per_page' => -1,
            'offset'           => 0,
            'tax_query' => array(
                array(
                'taxonomy' => 'joomsport_matchday',
                'field' => 'term_id',
                'terms' => $term_id)
            )
        )
        );

    $rounds_calc = array();
    for($intA=0;$intA<count($matches);$intA++){
        $match_id = $matches[$intA]->ID;
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
        if($m_played == 1){
            $rounds = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE match_id={$match_id}");
            if(!count($rounds)) { continue;}
            foreach ($rounds as $round) {
                $round_id = (int)$round->round_id;

                JSPredictionsCalc::calculateMatch($match_id, $round_id);
                $rounds_calc[] = $round_id;

            }
        }
    }
    $rounds_calc = array_unique($rounds_calc);

    if(count($rounds_calc)){
        foreach($rounds_calc as $rc){
            JSPredictionsCalc::calculateRound($rc);
        }
    }
    jspw_recalcStartdate($rounds_calc);

}


function joomsport_prediction_fire_save($match_id) {
    global $wpdb,$post_type;
    $recalcrounds = array();
    if(get_post_type($match_id) == 'joomsport_match'){
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
        if($m_played == 1){
            $rounds = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE match_id={$match_id}");
            if(!count($rounds)) { return;}
            foreach ($rounds as $round) {
                $round_id = (int)$round->round_id;
                JSPredictionsCalc::calculateMatch($match_id, $round_id);
                JSPredictionsCalc::calculateRound($round_id);
                $recalcrounds[] = $round_id;
            }
        }
    }
    jspw_recalcStartdate($recalcrounds);
}

function jspw_recalcStartdate($rounds){
    if(!count($rounds)){return;}
    $rounds = array_unique($rounds);
    foreach ($rounds as $round_id){
        global $wpdb;

        $roundtype = get_post_meta($round_id, '_joomsport_round_roundtype', true);

        $match_date = '';
        if($roundtype == 1){
            $mday = (int) get_post_meta($round_id,'_joomsport_round_knock_mday',true);
            if($mday){
                $matches = new WP_Query(array(
                    'post_type' => 'joomsport_match',
                    'posts_per_page'   => -1,
                    'orderby' => 'id',
                    'order'=>'ASC',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'joomsport_matchday',
                            'field' => 'term_id',
                            'terms' => $mday)
                    )

                ));
                $matches = $matches->posts;
                for($intA=0;$intA<count($matches);$intA++){
                    $m_date = get_post_meta( $matches[$intA]->ID, '_joomsport_match_date', true );
                    $m_time = get_post_meta( $matches[$intA]->ID, '_joomsport_match_time', true );
                    if($m_date){
                        if(!$match_date || $match_date > $m_date.' '.$m_time){

                            $match_date = $m_date.' '.$m_time;
                        }
                    }
                }

            }
        }else{
            $matches = $wpdb->get_col("SELECT match_id "
                . " FROM {$wpdb->jswprediction_round_matches}"
                . " WHERE round_id={$round_id}");


            for($intA=0;$intA<count($matches);$intA++){
                $m_date = get_post_meta( $matches[$intA], '_joomsport_match_date', true );
                $m_time = get_post_meta( $matches[$intA], '_joomsport_match_time', true );
                if($m_date){
                    if(!$match_date || $match_date > $m_date.' '.$m_time){
                        $match_date = $m_date.' '.$m_time;
                    }
                }
            }
        }



        update_post_meta($round_id, '_joomsport_round_start_match', $match_date);
    }
}

class JSPredictionsCalc{
    public static function calculateWinnerRound($round_id){
        global $wpdb;
        try {
            require_once dirname(__FILE__) . '/../sportleague/helpers/js-helper.php';
        }catch (Exception $e){
            echo $e->getMessage();
        }
        $leagueID = get_post_meta($round_id, '_joomsport_round_leagueid', true);
        $roundPoints = (int) get_post_meta($round_id, '_joomsport_round_points', true);
        $roundWinner = get_post_meta($round_id, '_joomsport_round_winner', true);

        $roundNStages = get_post_meta($round_id, '_joomsport_round_ned_stage', true);
        $nStagesPoints = get_post_meta($round_id, '_joomsport_round_ned_stage_pts', true);
        $roundTScorers = get_post_meta($round_id, '_joomsport_round_topscorer', true);
        $tScorersPoints = get_post_meta($round_id, '_joomsport_round_topscorer_pts', true);


        $pred = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id = ".$round_id);

        if(count($pred) && ($roundWinner || $roundNStages || $roundTScorers)) {

            foreach ($pred as $predUser) {
                $predU = json_decode($predUser->prediction, true);
                $points = 0;
                if(isset($predU['cWinner']['choice']) && $predU['cWinner']['choice'] == $roundWinner && $predU['cWinner']['choice'] != 0 ){
                    $points += $roundPoints;
                    $predU['cWinner']["points"] = $roundPoints;
                }elseif(isset($predU['cWinner']['choice'])){
                    $predU['cWinner']["points"] = 0;
                }
                if(isset($predU['nedStage']['choice']) && $predU['nedStage']['choice'] == $roundNStages && $predU['nedStage']['choice'] != 0){
                    $points += $nStagesPoints;
                    $predU['nedStage']["points"] = $nStagesPoints;
                }elseif(isset($predU['nedStage']['choice'])){
                    $predU['nedStage']["points"] = 0;
                }
                if(isset($predU['topScorer']['choice']) && in_array($predU['topScorer']['choice'],$roundTScorers) && $predU['topScorer']['choice'] != 0){
                    $points += $tScorersPoints;
                    $predU['topScorer']["points"] = $tScorersPoints;
                }elseif(isset($predU['topScorer']['choice'])){
                    $predU['topScorer']["points"] = 0;
                }

                $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET points = {$points}, prediction='".json_encode($predU)."' WHERE id = ".$predUser->id." ");

            }
            $points = $points?$points:0;
            //$wpdb->query("UPDATE {$roundGuessTable} SET points = {$points} WHERE round_id = ".$round_id." AND match_id=".$match_id." AND score1 = {$score->score1} AND score2={$score->score2}");

        }

    }
    public static function calculateMatch($match_id,$round_id){
        global $wpdb;
        $allredycalc = array();
        $leagueID = get_post_meta($round_id, '_joomsport_round_leagueid', true);
        $predLeague = get_post_meta($leagueID,'_jswprediction_league_points',true);
        $path = JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
        $match = JSPredictionsCalc::getMatch($match_id);

        if($predLeague && count($predLeague)){
            $predictionsDBA = array();
            $predictionsDBAll = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_types}");
            for($intP=0;$intP<count($predictionsDBAll); $intP++){
                $predictionsDBA[$predictionsDBAll[$intP]->id] = $predictionsDBAll[$intP];
            }

            $results = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            
            for($intA=0;$intA<count($results);$intA++){
                $points = NULL;
                $pred = json_decode($results[$intA]->prediction,true);
                if(isset($pred['score'][$match_id])){
                    if(isset($allredycalc[$pred['score'][$match_id]["score1"]."-".$pred['score'][$match_id]["score2"]])){
                        $points = $allredycalc[$pred['score'][$match_id]["score1"]."-".$pred['score'][$match_id]["score2"]];
                    }else {


                        foreach ($predLeague as $key => $value) {
                            if (isset($predictionsDBA[$key])) {
                                $predictionsDB = $predictionsDBA[$key];
                            } else {
                                die();
                            }

                            //$predictionsDB = $wpdb->get_row("SELECT * FROM {$wpdb->jswprediction_types} WHERE id={$key}");

                            $classN = 'JSPT' . $predictionsDB->identif;
                            if (is_file($path . $classN . '.php')) {
                                require_once $path . $classN . '.php';
                                if (class_exists($classN)) {
                                    $predObject = new $classN;
                                    if ($points === NULL) {
                                        $score_tmp = $predObject->getScore($match, $pred['score'][$match_id]);
                                        if ($score_tmp === true) {

                                            $points = $value;
                                        }
                                    } elseif ($predictionsDB->identif == 'ScoreBonus') {
                                        $score_tmp = $predObject->getScore($match, $pred['score'][$match_id]);
                                        if ($score_tmp === true) {

                                            $points += $value;
                                        }
                                    }

                                }
                            }

                        }
                    }
                    $points_before_joker = $points;
                    if(isset($pred['score'][$match_id]["joker"]) && $pred['score'][$match_id]["joker"] == 1 && $points){
                        $points *=2;
                    }

                    if ($points == NULL) {
                        $points = 0;
                    }
                    if ($points !== NULL) {
                        $pred['score'][$match_id]['points'] = $points;
                        $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET prediction='" . addslashes(json_encode($pred)) . "'  WHERE id=" . $results[$intA]->id);
                    }
                    $allredycalc[$pred['score'][$match_id]["score1"] . "-" . $pred['score'][$match_id]["score2"]] = $points_before_joker;

                    
                }elseif(isset($pred['outcome'][$match_id])){


                    foreach ($predLeague as $key => $value) {
                        if (isset($predictionsDBA[$key])) {
                            $predictionsDB = $predictionsDBA[$key];
                        } else {
                            die();
                        }

                        //$predictionsDB = $wpdb->get_row("SELECT * FROM {$wpdb->jswprediction_types} WHERE id={$key}");

                        $classN = 'JSPT' . $predictionsDB->identif;
                        if (is_file($path . $classN . '.php') && $predictionsDB->identif=='Outcome') {
                            require_once $path . $classN . '.php';
                            if (class_exists($classN)) {
                                $predObject = new $classN;
                                if ($points === NULL) {
                                    $score_tmp = $predObject->getScore($match, $pred['outcome'][$match_id]);
                                    if ($score_tmp === true) {

                                        $points = $value;
                                    }
                                } elseif ($predictionsDB->identif == 'ScoreBonus') {
                                    $score_tmp = $predObject->getScore($match, $pred['outcome'][$match_id]);
                                    if ($score_tmp === true) {

                                        $points += $value;
                                    }
                                }

                            }
                        }


                    }
                    $points_before_joker = $points;
                    if(isset($pred['outcome'][$match_id]["joker"]) && $pred['outcome'][$match_id]["joker"] == 1 && $points){
                        $points *=2;
                    }

                    if ($points == NULL) {
                        $points = 0;
                    }
                    if ($points !== NULL) {
                        $pred['outcome'][$match_id]['points'] = $points;
                        $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET prediction='" . addslashes(json_encode($pred)) . "'  WHERE id=" . $results[$intA]->id);
                    }

                }
            }

        }
    }
    public static function getMatch($match_id){
        global $wpdb;
        //$jsconfig =  new JoomsportSettings();
        $match = new stdClass();
        $jmscore = get_post_meta($match_id, '_joomsport_match_jmscore',true);

        if(JoomsportSettings::get('partdisplay_awayfirst',0) == 1){
            $match->score2 = get_post_meta($match_id, '_joomsport_home_score', true);
            $match->score1 = get_post_meta($match_id, '_joomsport_away_score', true);

            if(isset($jmscore["is_extra"]) && $jmscore["is_extra"] == 1){
                if(intval($jmscore["aet1"]) > 0){
                    $match->score2 -= $jmscore["aet1"];
                }
                if(intval($jmscore["aet2"]) > 0){
                    $match->score1 -= $jmscore["aet2"];
                }
            }



        }else{
            $match->score1 = get_post_meta($match_id, '_joomsport_home_score', true);
            $match->score2 = get_post_meta($match_id, '_joomsport_away_score', true);

            if(isset($jmscore["is_extra"]) && $jmscore["is_extra"] == 1){
                if(intval($jmscore["aet1"]) > 0){
                    $match->score1 -= $jmscore["aet1"];
                }
                if(intval($jmscore["aet2"]) > 0){
                    $match->score2 -= $jmscore["aet2"];
                }
            }

        }
        
                            
        return $match;
    }
    public static function calculateRound($round_id){
        global $wpdb;

        $calc_complete = false;
        
        $settings = get_option("joomsport_prediction_settings","");
        if(isset($settings["roundcalc"]) && $settings["roundcalc"] == "1"){
            $calc_complete = true;
        }
        
        $round_complete = true;
        
        $all_matches = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$round_id}");
        $matches = array();
        for($intB = 0 ; $intB < count($all_matches); $intB ++){
            $m_played = (int) get_post_meta( $all_matches[$intB]->match_id, '_joomsport_match_played', true );
            if($m_played == '1'){
                $matches[] = $all_matches[$intB];
            }else{
                $round_complete = false;
            }
            
        }
        
        
        if(!$calc_complete || $round_complete){
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            
            for($intA=0;$intA<count($results);$intA++){
                $points = 0;
                $filled = 0;
                $success = 0;
                $winner_side = 0;
                $diff = 0;
                $pred = json_decode($results[$intA]->prediction,true);
                for($intB = 0 ; $intB < count($matches); $intB ++){
                    $match_id = $matches[$intB]->match_id;
                    $matches_res = JSPredictionsCalc::getMatch($match_id);

                    if(isset($pred['score'][$match_id]['points'])){
                        $points += $pred['score'][$match_id]['points'];
                        $filled++;
                        
                        if(($matches_res->score1 == $pred['score'][$match_id]['score1'])
                                && ($matches_res->score2 == $pred['score'][$match_id]['score2'])){
                            $success++;
                        }else
                        if(($matches_res->score1 - $matches_res->score2)
                                == ($pred['score'][$match_id]['score1'] - $pred['score'][$match_id]['score2'])){
                            $diff++;
                        }else
                        if(($matches_res->score1 > $matches_res->score2) && ($pred['score'][$match_id]['score1'] > $pred['score'][$match_id]['score2'])
                                || ($matches_res->score1 < $matches_res->score2) && ($pred['score'][$match_id]['score1'] < $pred['score'][$match_id]['score2'])){
                            $winner_side++;
                        }    
                    }elseif(isset($pred['outcome'][$match_id]['points'])){
                        $points += $pred['outcome'][$match_id]['points'];
                        $filled++;

                        if($pred['outcome'][$match_id]['points'] > 0){
                            $success++;
                        }
                    }
                }
                
                $wpdb->query("UPDATE {$wpdb->jswprediction_round_users}"
                        . " SET points='".$points."', filled = {$filled}, success = {$success}, winner_side = {$winner_side}, score_diff = {$diff}"
                        . "  WHERE id=".$results[$intA]->id);
                        
                    
            }    
        }else{
            $results = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            for($intA=0;$intA<count($results);$intA++){
                $wpdb->query("UPDATE {$wpdb->jswprediction_round_users}"
                         . " SET points='0', filled = 0, success = 0, winner_side = 0, score_diff = 0"
                         . "  WHERE id=".$results[$intA]->id);
            }
        }    
       
      
    }
    
    public static function calculateKnockRound($round_id, $mday_id){
        global $wpdb;

        $calc_complete = false;

        $settings = get_option("joomsport_prediction_settings","");
        if(isset($settings["roundcalc"]) && $settings["roundcalc"] == "1"){
            $calc_complete = true;
        }
        
        $round_complete = false;
        
        $knock_settings = get_post_meta($round_id,'_joomsport_round_knock_points',true);
        if(class_exists("JoomsportTermsMeta")){
            $metas = JoomsportTermsMeta::getTermMeta($mday_id);
        }else{
            $metas = get_option("taxonomy_{$mday_id}_metas");
        }

        $knockoutView = $metas['knockout'];
        $kformat = $metas["knockout_format"];
        $winnerID = isset($metas['winner'])?$metas['winner']:0;
        
        $matrix_stages = array(
            2 => 1,
            4 => 2,
            8 => 3,
            16 => 4,
            32 => 5,
            64 => 6,
            128 => 7
        );
        
        $stages = $matrix_stages[$kformat];
        
        if($winnerID){
            $round_complete = true;
        }
        
        if(!$calc_complete || $round_complete){
            $uResults = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_users} WHERE round_id=".$round_id);
            
            for($intU=0;$intU<count($uResults);$intU++){
                $pts = 0;
                $success = 0;
                $filled = 0;
                
                $prediction = json_decode($uResults[$intU]->prediction,true);
                
                for($intA=0; $intA < intval($kformat/2); $intA++){
                
                for($intB=0; $intB < $stages; $intB ++){
                    if($intA == 0 || ($intA % (pow(2,$intB)))==0){
                        
                        $kvalues = array(
                            "home" => 0,
                            "away" => 0,
                            "score1" => "",
                            "score2" => "",
                            "match_id" => ""
                        );
                        
                        $ceilN = floor($intA/($intB+1) / 2);
                        $od = ($intA/($intB+1) % 2);
                        if($ceilN == 0){
                            $newdt = 0;
                        }else{
                            $newdt = $ceilN * pow(2,($intB+1));
                        }
                        
                        
                        
                        if(isset($knockoutView[$intB][$intA])){
                            $kvalues = array(
                                "home" => $knockoutView[$intB][$intA]["home"],
                                "away" => $knockoutView[$intB][$intA]["away"],
                                "score1" => $knockoutView[$intB][$intA]["score1"],
                                "score2" => $knockoutView[$intB][$intA]["score2"],
                                "match_id" => $knockoutView[$intB][$intA]["match_id"]
                            );
                        }
                        

                        
                        $arrV = isset($prediction['knockpartic_'.$intA.'_'.$intB])?$prediction['knockpartic_'.$intA.'_'.$intB]:array();
                            
                        
                        if(isset($arrV[0]) && $arrV[0]){
                            if(isset($knockoutView[$intB+1][$newdt][$od?"away":"home"])){
                                $arrVNext = isset($prediction['knockpartic_'.$newdt.'_'.($intB+1)])?$prediction['knockpartic_'.$newdt.'_'.($intB+1)]:array();
                                
                                if(isset($arrVNext[$od]) && $arrVNext[$od] && $knockoutView[$intB+1][$newdt][$od?"away":"home"]){

                                    $filled++;
                                    //var_dump($knockoutView[$newdt][$intB+1]);
                                    if($knockoutView[$intB+1][$newdt][$od?"away":"home"] == $arrVNext[$od]){
                                        $success++;
                                        if(isset($knock_settings[$kformat/(pow(2,($intB+1)))])){
                                            $pts += $knock_settings[$kformat/(pow(2,($intB+1)))];
                                        }
                                    } 
                                }
                            }  
                            
                            if($intB == $stages-1 && isset($metas['winner']) && isset($prediction["knockpartic_winner"])){
                                $filled++;
                                if($metas['winner'] == $prediction["knockpartic_winner"]){
                                    $success++;
                                    if(isset($knock_settings[1])){
                                        $pts += $knock_settings[1];
                                    }
                                }
                                
                            }
                            
                            
                        }

                    }
                }

                
            }
            $wpdb->query("UPDATE {$wpdb->jswprediction_round_users}"
                        . " SET points='".$pts."', filled = {$filled}, success = {$success}, winner_side = 0, score_diff = 0"
                        . "  WHERE id=".$uResults[$intU]->id);
            
            }
        }
        
    }    
    
    
    public function rankRound(){
        
    }
}
