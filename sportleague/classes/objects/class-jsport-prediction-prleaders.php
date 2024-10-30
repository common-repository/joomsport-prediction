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

class classJsportPrleaders
{
    private $id = null;
    public $season_id = null;
    public $object = null;
    public $league = null;
    public $lists = null;
    public $round_id = null;
    public $privateID = 0;
    
    public function __construct($id = 0)
    {
        if (!$id) {     
            $this->id = get_the_ID();
        } else {
            $this->id = $id;
        }
        if (!$this->id) {
            die('ERROR! LEAGUE ID not DEFINED');
        }
        $this->round_id = isset($_REQUEST['round_id'])?intval($_REQUEST['round_id']):0;
        $this->privateID = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
        
        $this->loadObject();
    }

    private function loadObject()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $settings = get_option("joomsport_prediction_settings","");
        $sort_str = '';
        if(isset($settings["sort"]) && count($settings["sort"])){
            foreach ($settings["sort"] as $key => $value) {
                if($sort_str){
                    $sort_str .= ',';
                }
                $sort_str .= $key . ' ' . ($value?'asc':'desc');
            }
        }
        if(!$sort_str) {
            $sort_str = 'pts desc, filled asc, succavg desc';
        }else{
            $sort_str .= ', user_id desc';
        }
        //$sortfields = $settings["sort"];
        
        //$sort_str = 'pts desc, filled asc, succavg desc';
        $rounds = $allrounds = array();
        $this->league = $this->id;
        
        $private_users = null;
        if($this->privateID){
            $query = "SELECT u.ID FROM {$wpdb->prefix}users as u"
                . " JOIN {$wpdb->jswprediction_private_users} as pm ON pm.userID = u.ID"
                . " WHERE pm.privateID = ".$this->privateID;
            $private_users = $wpdb->get_col($query);
            if(!count($private_users)){
                $private_users = array(0);
            }
            
        }
        
        
        
        
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
        
        
        $roundsPosts = new WP_Query(array(
            'post_type' => 'jswprediction_round',
            'posts_per_page'   => -1,
            'orderby' => 'post_date',
            'order'=>'ASC',
            'meta_query' => $metaquery   
        ));
        for($intA=0;$intA<count($roundsPosts->posts);$intA++){
            
            $round_complete = false;
            
            $roundtype = get_post_meta($roundsPosts->posts[$intA]->ID, '_joomsport_round_roundtype', true);

            if($roundtype == 1){
                $mday = (int) get_post_meta($roundsPosts->posts[$intA]->ID,'_joomsport_round_knock_mday',true);
                if(class_exists("JoomsportTermsMeta")){
                    $metas = JoomsportTermsMeta::getTermMeta($mday);
                }else{
                    $metas = get_option("taxonomy_{$mday}_metas");
                }
                if(isset($metas['winner']) && $metas['winner']){
                    $round_complete = TRUE;
                }
            }else{
                $all_matches = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_round_matches} WHERE round_id={$roundsPosts->posts[$intA]->ID}");
                if(count($all_matches)){
                    //$round_complete = true;
                }
                for($intB = 0 ; $intB < count($all_matches); $intB ++){
                    $m_played = (int) get_post_meta( $all_matches[$intB]->match_id, '_joomsport_match_played', true );
                    if($m_played == '1'){
                        $round_complete = true;
                    }else{
                        //$round_complete = false;
                    }

                }
            }
            if($round_complete){
                $rounds[] = $roundsPosts->posts[$intA]->ID;
            }
            $allrounds[] = $roundsPosts->posts[$intA]->ID;
        }
        
        $previous_places = array();
        
        $this->lists['previuos_places'] = $previous_places;
        $link = get_permalink($this->id);
        if($this->round_id){
            $link = add_query_arg( 'round_id', $this->round_id, $link );
        }else{
            
            
        }
        if($this->privateID){
            $link = add_query_arg( 'prl', $this->privateID, $link );

        }
        $pagination = new classJsportPagination($link);
        $limit = $pagination->getLimit();
        $offset = $pagination->getOffset();
        
        
        if(count($allrounds) >= 1){
            $this->object = $wpdb->get_results('SELECT SUM(u.points) as pts, '
                    . 'u.user_id,SUM(u.filled) as filled, SUM(u.success) as success,'
                    . '  SUM(u.success)/SUM(u.filled) as succavg, SUM(u.winner_side) as winner_side,'
                    . ' SUM(u.score_diff) as score_diff'
                    ." FROM {$wpdb->jswprediction_round_users} as u "
                    ." JOIN {$wpdb->prefix}users as usr ON usr.ID = u.user_id"
                    .' WHERE 1 = 1'
                    .($this->round_id ? ' AND u.round_id='.$this->round_id : ' AND u.round_id IN ('.implode(',',$allrounds).')')
                    .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")
                    .' GROUP BY u.user_id'
                    .' ORDER BY '.$sort_str
                    . ($limit ? ' LIMIT '.$offset.','.$limit:''));

            if($user_id){
                $userRow = $wpdb->get_row('SELECT SUM(u.points) as pts, '
                    . 'u.user_id,SUM(u.filled) as filled, SUM(u.success) as success,'
                    . '  SUM(u.success)/SUM(u.filled) as succavg, SUM(u.winner_side) as winner_side,'
                    . ' SUM(u.score_diff) as score_diff'
                    ." FROM {$wpdb->jswprediction_round_users} as u "
                    ." JOIN {$wpdb->prefix}users as usr ON usr.ID = u.user_id"
                    .' WHERE 1 = 1 AND u.user_id = '.$user_id
                    .($this->round_id ? ' AND u.round_id='.$this->round_id : ' AND u.round_id IN ('.implode(',',$allrounds).')')
                    .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")
                    .' GROUP BY u.user_id');
                if($userRow){
                    $user_position = $wpdb->get_results('SELECT COUNT(*) as cnt, SUM(u.points) as pts, SUM(u.filled) as filled, SUM(u.success)/SUM(u.filled) as succavg'
                        ." FROM {$wpdb->jswprediction_round_users} as u "
                        ." JOIN {$wpdb->prefix}users as usr ON usr.ID = u.user_id"
                        .' WHERE u.user_id != '.$user_id
                        .($this->round_id ? ' AND u.round_id='.$this->round_id : ' AND u.round_id IN ('.implode(',',$allrounds).')')
                        .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")
                        .' GROUP BY u.user_id'
                        .' HAVING pts>'.$userRow->pts.' OR (pts='.$userRow->pts.' AND (filled<'.$userRow->filled.' OR (filled='.$userRow->filled.' AND (succavg>'.floatval($userRow->succavg).' OR ('.($userRow->succavg==null?"succavg IS NULL":"succavg=".floatval($userRow->succavg)."").' AND u.user_id>'.$user_id.')))) ) '
                        .' ORDER BY '.$sort_str
                    );


                    $userRow->position = count($user_position)+1;
                    if($wpdb->last_error !== '') :
                        echo $wpdb->print_error();die();
                    endif;
                }

            }

            $all = $wpdb->get_col('SELECT (u.user_id)'
                ." FROM {$wpdb->jswprediction_round_users} as u "
                ." JOIN {$wpdb->prefix}users as usr ON usr.ID = u.user_id"
                .' WHERE 1=1'
                .($this->round_id ? ' AND u.round_id='.$this->round_id : ' AND u.round_id IN ('.implode(',',$allrounds).')')
                .($private_users && count($private_users)?' AND u.user_id IN ('.implode(",", $private_users).')':"")        
                .' GROUP BY u.user_id');
            $all = count($all);
        }else{
            $all = 0;
        }

        $pagination->setPages($all);
        $this->lists['pagination'] = $pagination;
        
        $html = '<div class="jspred_filterround">';
        //$html .= __('Rounds','joomsport-prediction');
        $html .= '<select class="btn btn-default selectpicker" name="round_id" onchange="this.form.submit();">';
        $html .= '<option value="0">'.esc_attr(__('All Rounds','joomsport-prediction')).'</option>';
        
        for($intA=0;$intA<count($rounds);$intA++){
            
            $rid = $rounds[$intA];
            $rname = get_post($rid);
            $html .= '<option value="'.$rid.'" '.($rid == $this->round_id?' selected':'').'>'.esc_attr($rname->post_title).'</option>';
        }

        $html .= '</select></div>';
        $this->lists['options']['tourn'] = $html;
        

        
        $this->lists['options']['title'] = get_the_title($this->id);
        
        $this->lists['options']['userleague'] = $this->id;
        
        $this->lists['options']['privateleague'] = $this->id;

            //
            $this->lists['mypos'] = isset($userRow)?$userRow:null;
        
        
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
        global $jsDatabase;
        $match = $jsDatabase->selectObject("SELECT m_date,m_time "
                . " FROM #__bl_predround_matches as r"
                . " JOIN #__bl_match as m ON r.match_id = m.id"
                . " WHERE r.round_id={$round_id}"
                . " AND m.m_date != '0000-00-00'"
                . " ORDER BY m.m_date asc,m.m_time asc"
                        . " LIMIT 1");
                
        if(isset($match->m_date)){
            return $match->m_date.' '.$match->m_time;
        }
    }
    /*public function getFilling($round_id){
        global $jsDatabase;
        $user_id = classJsportUser::getUserId();
        if(!$user_id){
            return '';
        }
        $prediction = $jsDatabase->selectValue("SELECT prediction FROM #__bl_predround_users WHERE user_id={$user_id} AND round_id={$round_id}");
        $pred = json_decode($prediction, true);
        
        $matches = $jsDatabase->selectColumn("SELECT r.match_id "
                . " FROM #__bl_predround_matches as r"
                . " WHERE r.round_id={$round_id}"
                );
        
        $filled = 0;
        
        for($intA=0;$intA<count($matches);$intA++){
            $match_id = $matches[$intA];
            if(isset($pred['score'][$match_id])){
                if($pred['score'][$match_id]['score1'] !== '' && $pred['score'][$match_id]['score2'] !== ''){
                    $filled++;
                }
            }
        }
                
        return $filled .' / ' . count($matches);
    }*/
    public function getPoints($round_id){
        global $jsDatabase;
        $user_id = classJsportUser::getUserId();
        if(!$user_id){
            return '';
        }
        return $points = $jsDatabase->selectValue("SELECT points FROM #__bl_predround_users WHERE user_id={$user_id} AND round_id={$round_id}");
        
    }
    public function getHeader(){
        do_action("jswprediction_messages",$this->id,"ranking");
    }
    public function getFooter(){
        do_action("jswprediction_messages",$this->id,"ranking", 1);
    }

}
