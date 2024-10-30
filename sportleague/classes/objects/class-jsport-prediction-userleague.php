<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


require_once JOOMSPORT_PATH_MODELS.'model-jsport-player.php';
require_once JOOMSPORT_PATH_ENV_CLASSES.'class-jsport-getplayers.php';
require_once JOOMSPORT_PATH_CLASSES.'class-jsport-matches.php';
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-match.php';

class classJsportUserleague
{
    private $id = null;
    public $season_id = null;
    public $object = null;
    public $league = null;
    public $lists = null;
    public $usrid = null;

    public function __construct($id = 0)
    {
        if (!$id) {     
            $this->id = get_the_ID();
        } else {
            $this->id = $id;
        }
        $this->usrid = (int) filter_input(INPUT_GET,'usrid');
        if(!$this->usrid){
            $this->usrid = get_current_user_id();
        }
        if (!$this->id) {
            die('ERROR! LEAGUE ID not DEFINED');
        }
        $this->loadObject();
    }

    private function loadObject()
    {
        global $wpdb;


        $metaquery = array();
        $metaquery[] = 
                array(
                    'relation' => 'AND',
                        array(
                    'key' => '_joomsport_round_leagueid',
                    'value' => $this->id,
                    'compare' => '='
                    ),

                    
                ) ;
        $rounds = new WP_Query(array(
            'post_type' => 'jswprediction_round',
            'posts_per_page'   => -1,
            'orderby' => 'post_date',
            'order'=>'ASC',
            'meta_query' => $metaquery   
        ));
        for($intA=0;$intA<count($rounds->posts);$intA++){
            $rounds->posts[$intA]->startdate = $this->getStartDate($rounds->posts[$intA]->ID);
        }
        usort($rounds->posts, array($this,'cmp'));
        $this->object = $rounds->posts;

        $uname_str = '';
        if($this->usrid){
            $user = new WP_User($this->usrid);
            
            $uname = $user->data->display_name;
            $uname_str = ' ('.strtolower(__('User')).': ' . $uname . ')';
        }
        $this->lists['options']['title'] = get_the_title($this->id) . $uname_str;
        $this->lists['options']['prleaders'] = $this->id;
    }
    public function cmp($a,$b){


        if ($a->startdate == $b->startdate) {
            return  0;
        }
        return  ($a->startdate < $b->startdate) ? -1 : 1;

    }
    public function getRow()
    {

        return $this;
    }
    public function getRowSimple()
    {
        return $this;
    }
    
    public function getStartDate($round_id){
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
        }elseif($roundtype == '3'){
            $match_date = get_post_meta($round_id, '_joomsport_round_start_match', true);
            $match_date = date("Y-m-d H:i", strtotime($match_date));

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
        
               
        
        return $match_date;
    }
    public function getFilling($round_id){
        global $wpdb;
        //$user_id = classJsportUser::getUserId();
        if(!$this->usrid){
            return '';
        }
        
        $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$this->usrid} AND round_id={$round_id}");
        $pred = $prediction?json_decode($prediction, true):null;
        $filled = 0;
        $matches_count = 0;
        
        $roundtype = get_post_meta($round_id, '_joomsport_round_roundtype', true);
        if($roundtype == '1'){
            $mday = (int) get_post_meta($round_id,'_joomsport_round_knock_mday',true);
            if(class_exists("JoomsportTermsMeta")){
                $metas = JoomsportTermsMeta::getTermMeta($mday);
            }else{
                $metas = get_option("taxonomy_{$mday}_metas");
            }
            $kformat = $metas["knockout_format"];
            
            $matches_count = $kformat - 1;
            if(count($pred)){
                foreach($pred as $key => $value){
                    if(substr($key, 0, 11) == 'knockpartic'){
                        if(is_array($value)){
                            if($value[0]){
                                $filled ++;
                            }
                            if($value[1]){
                                $filled ++;
                            }
                        }elseif($value){
                            $filled ++;
                        }
                    }
                } 
                if($filled > $kformat){
                    $filled = $filled - $kformat;
                }else{
                    $filled = 0;
                }
            }

        }elseif($roundtype == '3'){
            $filled = 0;
            $quest = 3;
            if(isset($pred['cWinner']) && isset($pred['cWinner']['choice']) && intval($pred['cWinner'])){
                $filled++;
            }
            if(isset($pred['nedStage']) && isset($pred['nedStage']['choice']) && intval($pred['nedStage'])){
                $filled++;
            }
            if(isset($pred['topScorer']) && isset($pred['topScorer']['choice']) && intval($pred['topScorer'])){
                $filled++;
            }
            return $filled .' / ' . $quest;
        }else{
            $matches = $wpdb->get_col("SELECT r.match_id "
                    . " FROM {$wpdb->jswprediction_round_matches} as r"
                    . " WHERE r.round_id={$round_id}"
                    );

            $matches_count = count($matches);        

            for($intA=0;$intA<count($matches);$intA++){
                $match_id = $matches[$intA];
                if(isset($pred['score'][$match_id])){
                    if($pred['score'][$match_id]['score1'] !== '' && $pred['score'][$match_id]['score2'] !== ''){
                        $filled++;
                    }
                }elseif(isset($pred['outcome'][$match_id])){
                    if($pred['outcome'][$match_id]['outcome'] !== '' && $pred['outcome'][$match_id]['outcome'] !== ''){
                        $filled++;
                    }
                }
            }
        }
                
        return $filled .' / ' . $matches_count;
    }
    public function getPoints($round_id){
        global $wpdb;
        //$user_id = classJsportUser::getUserId();
        if(!$this->usrid){
            return '';
        }
        return $points = $wpdb->get_var("SELECT points FROM {$wpdb->jswprediction_round_users} WHERE user_id={$this->usrid} AND round_id={$round_id}");
        
    }
    
    public function getRoundStatus($round_id, $start_date){
        global $wpdb;
        $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$this->usrid} AND round_id={$round_id}");
        $pred = $prediction?json_decode($prediction, true):null;
        $filled = 0;
        $matches_count = 0;
        
        $stat = 0;
        
        $user_id = get_current_user_id();
        if($user_id && $this->usrid != $user_id){
            return '3';
        }
        
        
        $roundtype = get_post_meta($round_id, '_joomsport_round_roundtype', true);
        if($roundtype == '1'){
            $mday = (int) get_post_meta($round_id,'_joomsport_round_knock_mday',true);
            if(($start_date <= date("Y-m-d H:i", current_time( 'timestamp', 0 )))){
                return 0;
            }if($pred && count($pred)){
                return 2;
            }else{
                return 1;
            }
        }elseif($roundtype == '3') {
            $start_date = get_post_meta($round_id, '_joomsport_round_close_date', true);
            $startDate = date("Y-m-d", strtotime($start_date));
            $startTime = date("H:i:s", strtotime($start_date));

            if (($startDate > date("Y-m-d", current_time('timestamp', 0))) || ($startDate == date("Y-m-d", current_time('timestamp', 0)) && $startTime > date("H:i", current_time('timestamp', 0)))) {

                if ($pred && count($pred)) {
                    return 2;
                } else {
                    return 1;
                }
            }else{
                return 0;
            }
        }else{
            $matches = $wpdb->get_col("SELECT r.match_id "
                    . " FROM {$wpdb->jswprediction_round_matches} as r"
                    . " WHERE r.round_id={$round_id}"
                    );

            $matches_count = count($matches);        

            
            
            for($intA=0;$intA<count($matches);$intA++){
                $match_id = $matches[$intA];
                $m_date = get_post_meta( $match_id, '_joomsport_match_date', true );
                $m_time = get_post_meta( $match_id, '_joomsport_match_time', true );
                if(($m_date > date("Y-m-d", current_time( 'timestamp', 0 ))) || ($m_date == date("Y-m-d", current_time( 'timestamp', 0 )) && $m_time > date("H:i", current_time( 'timestamp', 0 )))){
                    $stat = 2;
                    if($roundtype == 1) {
                        if (isset($pred['score'][$match_id])) {
                            if ($pred['score'][$match_id]['score1'] === '' && $pred['score'][$match_id]['score2'] === '') {
                                return '1';
                            }
                        }
                        if (!isset($pred['score'])) {
                            return '1';
                        }
                    }elseif ($roundtype == 5){
                        if (isset($pred['outcome'][$match_id])) {
                            if ($pred['outcome'][$match_id]['outcome'] === '') {
                                return '1';
                            }
                        }
                        if (!isset($pred['outcome'])) {
                            return '1';
                        }
                    }
                }
                if($roundtype == 1) {
                    if (isset($pred['score'][$match_id])) {
                        if ($pred['score'][$match_id]['score1'] !== '' && $pred['score'][$match_id]['score2'] !== '') {
                            $filled++;
                        }
                    }elseif ($roundtype == 5){
                        if ($pred['outcome'][$match_id]['outcome'] !== '') {
                            $filled++;
                        }
                    }
                }
            }
            
            
            
            
        }
        
        return $stat;
        
    }

    public function getHeader(){
        do_action("jswprediction_messages",$this->id,"round_list");
    }
    public function getFooter(){
        do_action("jswprediction_messages",$this->id,"round_list", 1);
    }
}
