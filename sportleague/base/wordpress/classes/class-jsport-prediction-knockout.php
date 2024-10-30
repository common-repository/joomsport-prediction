<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**<!--WPJSSTDDEL--!>
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

class ClassJsportPredictionKnockout{
    private $_mdID = null;
    private $_seasonID = null;
    public function __construct($mdID) {
        $this->_mdID = $mdID;
        $metas = get_option("taxonomy_{$mdID}_metas");
        $this->_seasonID = $metas['season_id'];
    }
    
    public function getView($prediction){
        $metas = get_option("taxonomy_{$this->_mdID}_metas");
        if(isset($metas['knockout'])){
            $knockoutView = $metas['knockout'];
        }else{
            return '';
        }
        
        $prediction = json_decode($prediction, true);
        
        wp_enqueue_style('jscssbracket22',plugin_dir_url( __FILE__ ).'../../../../../joomsport-sports-league-results-management/sportleague/assets/css/drawBracketBE.css');
        wp_enqueue_style('jscssbracket_predcs',plugin_dir_url( __FILE__ ).'/../../../../assets/css/prediction_brackets.css');
        
        $matrix_stages = array(
            2 => 1,
            4 => 2,
            8 => 3,
            16 => 4,
            32 => 5,
            64 => 6,
            128 => 7
        );
        
        //$kformat = 128;
        $kformat = $metas["knockout_format"];
        
        $stages = $matrix_stages[$kformat];
        //echo pow( 64, 1/2);
        $participiants = JoomSportHelperObjects::getParticipiants($this->_seasonID);
        
        $html = '<div class="jsOverXdiv">';
        $html .= '<div class="drawBracketContainerFE">';
        
        $Mterm = get_term($this->_mdID);
        
        if(isset($Mterm->term_id) && $Mterm->term_id){
            $html .= '<h4>'.$Mterm->name.'</h4>';
        }
           
            $html .= '<table border="0" cellpadding="0" cellspacing="0" class="table">';
            

            for($intA=0; $intA < intval($kformat/2); $intA++){
                $html .= '<tr>';
                for($intB=0; $intB < $stages; $intB ++){
                    if($intA == 0 || ($intA % (pow(2,$intB)))==0){
                        
                        $kvalues = array(
                            "home" => 0,
                            "away" => 0,
                            "score1" => "",
                            "score2" => "",
                            "match_id" => ""
                        );
                        if(isset($knockoutView[$intB][$intA])){
                            $kvalues = array(
                                "home" => $knockoutView[$intB][$intA]["home"],
                                "away" => $knockoutView[$intB][$intA]["away"],
                                "score1" => $knockoutView[$intB][$intA]["score1"],
                                "score2" => $knockoutView[$intB][$intA]["score2"],
                                "match_id" => $knockoutView[$intB][$intA]["match_id"]
                            );
                        }
                        
                        $html .= '<td class="even" id="knocktd_'.$intA.'_'.$intB.'" data-game="'.$intA.'" data-level="'.$intB.'" rowspan="'.(pow(2,$intB)).'">';
                        $morefaclass = '';
                        if($intA % (pow(2 ,($intB+1))) == 0 && $intB != $stages-1){
                            $html .= '<div class="jsborderI"></div>';
                            
                        }elseif($intB == $stages-1){
                            $html .= '<div class="jsborderIFin"></div>';
                        }else{
                            $morefaclass = ' facirclebot';
                        }

                        if($kvalues["match_id"]){
                            $match = new classJsportMatch($kvalues["match_id"][0], false);
                            
                            if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                               // $html .=  classJsportLink::match('<i class="fa fa-cog jsmatchconf" aria-hidden="true"></i>', $kvalues["match_id"], false, '');
                            
                            }
                            $partic_home = $match->getParticipantHome();
                            $partic_away = $match->getParticipantAway();
                            //var_dump($partic_home);
                            
                        }
                        
                        $html .= '<div class="knockround"><div class="player knocktop ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName knockplChoose'.($kvalues["home"]>0?' knockHover'.$kvalues["home"]:'').'">';
                        
                            $arrV = isset($prediction['knockpartic_'.$intA.'_'.$intB])?$prediction['knockpartic_'.$intA.'_'.$intB]:array();
                                
                            if($kvalues["home"] > 0){
                                $html .= '<div class="knwinner">';
                                $obj = new classJsportParticipant($this->_seasonID);
                                $part = $obj->getParticipiantObj($kvalues["home"]);
                                if(is_object($part)){
                                   //$html .= $partic_home->getEmblem();
                                   $html .= jsHelper::nameHTML($part->getName(false)); 
                                }else{   
                                   $html .= '<div class="js_div_particName">'.$kvalues["home"].get_the_title($kvalues["home"]).'</div>'; 
                                }
                                
                                $html .= '</div>';
                                if(isset($metas['winner']) && ($intB == $stages-1) && ($metas['winner'] == $kvalues["home"])){
                                    $html .= '<div class="jsknockwinnerDiv"></div>';
                                }
                            }elseif($kvalues["home"] == -1){
                                $html .= '<div class="knwinner"><div class="js_div_particName">'.__('BYE', 'joomsport-sports-league-results-management').'</div></div>';
                               
                            }else{
                                if(isset($arrV[0]) && $arrV[0]){
                                    $html .= '<div class="knwinner"><div class="js_div_particName">'.get_the_title($arrV[0]).'</div>';
                                    if($intA == 0 && $intB == ($stages - 1) && isset($prediction["knockpartic_winner"]) && $prediction["knockpartic_winner"] == $arrV[0]){
                                        $html .= '<div class="jsknockwinnerDiv"></div>';
                                    }
                                    
                                    $html .= '</div>';
                                }
                            }
                        
                              
                        $html .=  '</div>';
                        $html .= '<input type="hidden" id="knockpartic_'.$intA.'_'.$intB.'_0" name="knockpartic_'.$intA.'_'.$intB.'[0]" value="'.(isset($arrV[0])?$arrV[0]:$kvalues["home"]).'" />';
                        /*
                        if(count($kvalues["match_id"]) && $kvalues["match_id"]){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                $match = new classJsportMatch($kmid, false);
                                $html .= '<div class="knockscore">';
                                if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                                   $html .=  classJsportLink::match(($kvalues["score1"][$intZ]!=''?$kvalues["score1"][$intZ]:'&nbsp;'), $kmid, false, '');

                                }else{
                                    $html .= ($kvalues["score1"][$intZ]!=''?$kvalues["score1"][$intZ]:'&nbsp;');
                                }
                                $html .= '</div>';
                                
                            
                                $intZ++;
                            }
                        }   
                         
                         */ 
                        $html .=  '</div></div>';
                        $html .= '<div class="player knockbot ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName knockplChoose'.($kvalues["away"]>0?' knockHover'.$kvalues["away"]:'').'">';
                        
                            if($kvalues["away"] > 0){
                                
                                $html .= '<div class="knwinner">';
                                $obj = new classJsportParticipant($this->_seasonID);
                                $part = $obj->getParticipiantObj($kvalues["away"]);
                                if(is_object($part)){
                                   //$html .= $partic_home->getEmblem();
                                   $html .= jsHelper::nameHTML($part->getName(false)); 
                                }else{
                                   $html .= '<div class="js_div_particName">'.get_the_title($kvalues["away"]).'</div>'; 
                                }
                                
                                $html .= '</div>';
                                if(isset($metas['winner']) && ($intB == $stages-1) && ($metas['winner'] == $kvalues["away"])){
                                    $html .= '<div class="jsknockwinnerDiv"></div>';
                                }
                                
                            }elseif($kvalues["away"] == -1){
                                $html .= '<div class="knwinner"><div class="js_div_particName">'.__('BYE', 'joomsport-sports-league-results-management').'</div></div>';
                                
                            }else{
                                if(isset($arrV[1]) && $arrV[1]){
                                    $html .= '<div class="knwinner"><div class="js_div_particName">'.get_the_title($arrV[1]).'</div>';
                                    if($intA == 0 && $intB == ($stages - 1) && isset($prediction["knockpartic_winner"]) && $prediction["knockpartic_winner"] == $arrV[1]){
                                        $html .= '<div class="jsknockwinnerDiv"></div>';
                                    }
                                    $html .= '</div>';
                                }
                            }
                        
                                
                        $html .= '</div>';
                        $html .= '<input type="hidden" id="knockpartic_'.$intA.'_'.$intB.'_1" name="knockpartic_'.$intA.'_'.$intB.'[1]" value="'.(isset($arrV[1])?$arrV[1]:$kvalues["away"]).'" />';
                        
                        /*
                        if(count($kvalues["match_id"]) && $kvalues["match_id"]){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                
                                $match = new classJsportMatch($kmid, false);
                                $html .= '<div class="knockscore">';
                                //var_dump($match);
                                if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                                   $html .=  classJsportLink::match(($kvalues["score2"][$intZ]!=''?$kvalues["score2"][$intZ]:'&nbsp;'), $kmid, false, '');

                                }else{
                                    $html .= ($kvalues["score2"][$intZ]!=''?$kvalues["score2"][$intZ]:'&nbsp;');
                                }
                                $html .= '</div>';
                                $intZ++;
                            }
                        }  */
                        $html .= '</div></div></div>';
                        $html .= '</td>';
                    }
                }

                $html .= '</tr>';
            }
           
            $html .= '</table>';
            $html .= '<input type="hidden" id="knockpartic_winner" name="knockpartic_winner" value="'.(isset($prediction["knockpartic_winner"])?$prediction["knockpartic_winner"]:'').'" />';
                        

        $html .= '</div>';
        $html .= '</div>';
        return $html;
        
    }
    
    public function getViewResult($prediction){
        $metas = get_option("taxonomy_{$this->_mdID}_metas");
        if(isset($metas['knockout'])){
            $knockoutView = $metas['knockout'];
        }else{
            return '';
        }
        
        $prediction = json_decode($prediction, true);
        
        wp_enqueue_style('jscssbracket22',plugin_dir_url( __FILE__ ).'../../../../../joomsport-sports-league-results-management/sportleague/assets/css/drawBracketBE.css');
        wp_enqueue_style('jscssbracket_predcs',plugin_dir_url( __FILE__ ).'/../../../../assets/css/prediction_brackets.css');
        
        $matrix_stages = array(
            2 => 1,
            4 => 2,
            8 => 3,
            16 => 4,
            32 => 5,
            64 => 6,
            128 => 7
        );
        
        //$kformat = 128;
        $kformat = $metas["knockout_format"];
        
        $stages = $matrix_stages[$kformat];
        //echo pow( 64, 1/2);
        $participiants = JoomSportHelperObjects::getParticipiants($this->_seasonID);
        
        $html = '<div class="jsOverXdiv">';
        $html .= '<div class="drawBracketContainerFE userPickTable">';
        
        $Mterm = get_term($this->_mdID);
        
        if(isset($Mterm->term_id) && $Mterm->term_id){
            $html .= '<h4>'.$Mterm->name.'</h4>';
        }
           
            $html .= '<table border="0" cellpadding="0" cellspacing="0" class="table">';

            for($intA=0; $intA < intval($kformat/2); $intA++){
                $html .= '<tr>';
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
                        
                        $html .= '<td class="even" id="knocktd_'.$intA.'_'.$intB.'" data-game="'.$intA.'" data-level="'.$intB.'" rowspan="'.(pow(2,$intB)).'">';
                        $morefaclass = '';
                        if($intA % (pow(2 ,($intB+1))) == 0 && $intB != $stages-1){
                            $html .= '<div class="jsborderI"></div>';
                            
                        }elseif($intB == $stages-1){
                            $html .= '<div class="jsborderIFin"></div>';
                        }else{
                            $morefaclass = ' facirclebot';
                        }

                        if($kvalues["match_id"]){
                            $match = new classJsportMatch($kvalues["match_id"][0], false);
                            
                            if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                               // $html .=  classJsportLink::match('<i class="fa fa-cog jsmatchconf" aria-hidden="true"></i>', $kvalues["match_id"], false, '');
                            
                            }
                            $partic_home = $match->getParticipantHome();
                            $partic_away = $match->getParticipantAway();
                            //var_dump($partic_home);
                            
                        }
                        
                        $jsChooseBorder1 = '';
                        $jsChooseBorder2 = '';
                        $jsChooseBorderNB1 = '';
                        $jsChooseBorderNB2 = '';
                        $jsKnockStar1 = '';
                        $jsKnockStar2 = '';
                        
                        $arrV = isset($prediction['knockpartic_'.$intA.'_'.$intB])?$prediction['knockpartic_'.$intA.'_'.$intB]:array();
                            
                        $html .= '<div class="jspUserPick jspUserPickTop">';
                        
                        if(isset($arrV[0]) && $arrV[0]){
                            if(isset($knockoutView[$intB+1][$newdt][$od?"away":"home"])){
                                $arrVNext = isset($prediction['knockpartic_'.$newdt.'_'.($intB+1)])?$prediction['knockpartic_'.$newdt.'_'.($intB+1)]:array();
                                
                                if(isset($arrVNext[$od]) && $arrVNext[$od] && $knockoutView[$intB+1][$newdt][$od?"away":"home"]){
                                    //var_dump($knockoutView[$newdt][$intB+1]);
                                    if($knockoutView[$intB+1][$newdt][$od?"away":"home"] == $arrVNext[$od]){
                                        
                                        if($kvalues["home"] == $arrVNext[$od]){
                                            $jsChooseBorder1 = ' jspResultBorderWinner';
                                        }else{
                                            $jsChooseBorder2 = ' jspResultBorderWinner';
                                        }
                                    }else{
                                        if($arrV[0] == $arrVNext[$od]){
                                            $jsChooseBorder1 = ' jspResultBorderLoose';
                                        }elseif($arrV[1] == $arrVNext[$od]){
                                            $jsChooseBorder2 = ' jspResultBorderLoose';
                                        }
                                    } 
                                }
                            }  
                            if(isset($knockoutView[$intB][$intA])){
                                $arr = array($knockoutView[$intB][$intA]["home"],$knockoutView[$intB][$intA]["away"]);
                                if(!in_array($arrV[0],$arr) && $knockoutView[$intB][$intA]["home"]){
                                    $jsChooseBorderNB1 = ' jspResultBorderNotPart';
                                }
                                if(!in_array($arrV[1],$arr) && $knockoutView[$intB][$intA]["away"]){
                                    $jsChooseBorderNB2 = ' jspResultBorderNotPart';
                                }
                            }
                            if($intB == $stages-1 && isset($metas['winner']) && isset($prediction["knockpartic_winner"]) && $knockoutView[$intB][$intA]["home"] && $knockoutView[$intB][$intA]["away"]){
                                if($metas['winner'] == $prediction["knockpartic_winner"]){
                                    if($metas['winner'] == $arrV[0]){
                                        $jsChooseBorder1 = ' jspResultBorderWinner';
                                    }else{
                                        $jsChooseBorder2 = ' jspResultBorderWinner';
                                    }
                                }else{
                                    if($prediction["knockpartic_winner"] == $arrV[0]){
                                        $jsChooseBorder1 = ' jspResultBorderLoose';
                                    }else{
                                        $jsChooseBorder2 = ' jspResultBorderLoose';
                                    }
                                }
                                
                            }
                            
                            if($intB == $stages-1 && isset($metas['winner']) && isset($prediction["knockpartic_winner"])){
                                if($prediction["knockpartic_winner"] == $arrV[0]){
                                    $jsKnockStar1 = '<img src="'.JOOMSPORT_PREDICTION_LIVE_URL_IMAGES_DEF.'ystar.png" class="jsPredStar" />';
                                }
                                if($prediction["knockpartic_winner"] == $arrV[1]){
                                    $jsKnockStar2 = '<img src="'.JOOMSPORT_PREDICTION_LIVE_URL_IMAGES_DEF.'ystar.png" class="jsPredStar" />';
                                }
                                
                            }
                            
                            $vName = $arrV[0] != -1 ? get_the_title($arrV[0]) : __('BYE', 'joomsport-sports-league-results-management');
                            $html .= '<div class="jspUserPickInnerTop'.$jsChooseBorderNB1.'"><div class="jspResultBorder'.$jsChooseBorder1.'"></div>'.$jsKnockStar1.$vName.'</div>';
                        }
                        $html .= '</div>';
                        
                        $html .= '<div class="knockround"><div class="player knocktop ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName'.($kvalues["home"]>0?' knockHover'.$kvalues["home"]:'').'">';
                        
                                
                            if($kvalues["home"] > 0){
                                $html .= '<div class="knwinner">';
                                $obj = new classJsportParticipant($this->_seasonID);
                                $part = $obj->getParticipiantObj($kvalues["home"]);
                                if(is_object($part)){
                                   //$html .= $partic_home->getEmblem();
                                   $html .= jsHelper::nameHTML($part->getName(true)); 
                                }else{   
                                   $html .= '<div class="js_div_particName">'.$kvalues["home"].get_the_title($kvalues["home"]).'</div>'; 
                                }
                                
                                $html .= '</div>';
                                if(isset($metas['winner']) && ($intB == $stages-1) && ($metas['winner'] == $kvalues["home"])){
                                    $html .= '<div class="jsknockwinnerDiv"></div>';
                                }
                            }elseif($kvalues["home"] == -1){
                                $html .= '<div class="knwinner"><div class="js_div_particName">'.__('BYE', 'joomsport-sports-league-results-management').'</div></div>';
                               
                            }
                        
                              
                        $html .=  '</div>';
                        
                        if(count($kvalues["match_id"]) && $kvalues["match_id"]){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                $match = new classJsportMatch($kmid, false);
                                $html .= '<div class="knockscore">';
                                if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                                   $html .=  classJsportLink::match(($kvalues["score1"][$intZ]!=''?$kvalues["score1"][$intZ]:'&nbsp;'), $kmid, false, '');

                                }else{
                                    $html .= ($kvalues["score1"][$intZ]!=''?$kvalues["score1"][$intZ]:'&nbsp;');
                                }
                                $html .= '</div>';
                                
                            
                                $intZ++;
                            }
                        }   
                         
                          
                        $html .=  '</div></div>';
                        
                        
                        
                        $html .= '<div class="player knockbot ml9">'
                                . '<div class="kntmprow">'
                                . '<div class="knockplName'.($kvalues["away"]>0?' knockHover'.$kvalues["away"]:'').'">';
                        
                            if($kvalues["away"] > 0){
                                
                                $html .= '<div class="knwinner">';
                                $obj = new classJsportParticipant($this->_seasonID);
                                $part = $obj->getParticipiantObj($kvalues["away"]);
                                if(is_object($part)){
                                   //$html .= $partic_home->getEmblem();
                                   $html .= jsHelper::nameHTML($part->getName(true)); 
                                }else{
                                   $html .= '<div class="js_div_particName">'.get_the_title($kvalues["away"]).'</div>'; 
                                }
                                
                                $html .= '</div>';
                                if(isset($metas['winner']) && ($intB == $stages-1) && ($metas['winner'] == $kvalues["away"])){
                                    $html .= '<div class="jsknockwinnerDiv"></div>';
                                }
                                
                            }elseif($kvalues["away"] == -1){
                                $html .= '<div class="knwinner"><div class="js_div_particName">'.__('BYE', 'joomsport-sports-league-results-management').'</div></div>';
                                
                            }
                        
                                
                        $html .= '</div>';
                        
                        
                        if(count($kvalues["match_id"]) && $kvalues["match_id"]){
                            $intZ=0;
                            foreach ($kvalues["match_id"] as $kmid) {
                                
                                $match = new classJsportMatch($kmid, false);
                                $html .= '<div class="knockscore">';
                                //var_dump($match);
                                if(isset($match->object->ID) && $kvalues["home"] != -1 && $kvalues["away"] != -1){
                                   $html .=  classJsportLink::match(($kvalues["score2"][$intZ]!=''?$kvalues["score2"][$intZ]:'&nbsp;'), $kmid, false, '');

                                }else{
                                    $html .= ($kvalues["score2"][$intZ]!=''?$kvalues["score2"][$intZ]:'&nbsp;');
                                }
                                $html .= '</div>';
                                $intZ++;
                            }
                        } 
                        $html .= '</div></div></div>';
                        
                        $html .= '<div class="jspUserPick">';
                        if(isset($arrV[1]) && $arrV[1]){
                            $vName = $arrV[1] != -1 ? get_the_title($arrV[1]) : __('BYE', 'joomsport-sports-league-results-management');
                            $html .= '<div class="jspUserPickInnerBott'.$jsChooseBorderNB2.'"><div class="jspResultBorder'.$jsChooseBorder2.'"></div>'.$jsKnockStar2.$vName.'</div>';
                        }
                        $html .= '</div>';
                        
                        $html .= '</td>';
                    }
                }

                $html .= '</tr>';
            }
           
            $html .= '</table>';
                        

        $html .= '</div>';
        $html .= '</div>';
        return $html;
        
    }
}