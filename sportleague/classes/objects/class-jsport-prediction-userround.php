<?php


require_once JOOMSPORT_PATH_MODELS.'model-jsport-player.php';
require_once JOOMSPORT_PATH_ENV_CLASSES.'class-jsport-getplayers.php';
require_once JOOMSPORT_PATH_CLASSES.'class-jsport-matches.php';
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-match.php';

class classJsportUserround
{
    public $id = null;
    public $season_id = null;
    public $object = null;
    public $league = null;
    public $lists = null;
    public $usrid = null;
    public $roundtype = null;
    public $has_joker = false;
    public $joker_match = null;

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
        $this->roundtype = get_post_meta($this->id, '_joomsport_round_roundtype', true);
        $this->Joker();
        $this->loadObject();

    }

    private function loadObject()
    {
        global $wpdb;
        $user_id = get_current_user_id();
        $leagueid = $this->league = get_post_meta($this->id, '_joomsport_round_leagueid', true);


        if($this->roundtype == '1'){
            
            $this->lists['knockout'] = '';
            if(!$user_id){
                $this->lists['knockout'] =  '<div class="jspred_message_login"><a href="'.wp_login_url( get_permalink() ).'" title="'.__("Login",'joomsport-prediction').'">'.__("Login",'joomsport-prediction').'</a>'.__(" to submit your predictions",'joomsport-prediction').'</div>';
            }
            require_once JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'class-jsport-prediction-knockout.php';
            $mday = (int) get_post_meta($this->id,'_joomsport_round_knock_mday',true);
            if($mday){
                $knObj = new ClassJsportPredictionKnockout($mday);
                $editable = $this->lists['knockout_editable'] = $this->knockoutEditable();
                
                if(classJsportRequest::get('jspAction') == 'saveRound' && $this->canSave() && $editable){
                    
                    $predString = array();
                    $postVars = $_POST;
                    
                    if(count($postVars)){
                        foreach($postVars as $key => $value){
                            
                            if(substr($key, 0, 11) == 'knockpartic'){
                                if(is_array($value)){
                                    $predString[$key] = array_map('strip_tags',$value);
                                }else{
                                    $predString[$key] = strip_tags($value);
                                }
                            }   
                        }
                    }
                    $predString = json_encode($predString);
                    
                    
                    $exist = $wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_round_users} WHERE user_id={$user_id} AND round_id={$this->id}");
                    if($exist){

                        $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET prediction='".addslashes($predString)."' WHERE id={$exist}");
                    }else{

                        $wpdb->query("INSERT INTO {$wpdb->jswprediction_round_users}(user_id,round_id,prediction,filldate)"
                                . " VALUES({$user_id},{$this->id},'".$predString."','".date("Y-m-d H:i:s",current_time( 'timestamp', 0 ))."')");
                    }
                    add_action("jspred_saved_notice", function(){
                        echo '<div class="jspred_success">'.__("Predictions saved",'joomsport-prediction').'</div>';

                    });

                }elseif($editable && !$user_id && !$this->usrid){
                    add_action("jspred_saved_notice", function(){
                        $settings = get_option("joomsport_prediction_settings","");
                        $login_url = '';
                        if(isset($settings["login_link"]) && $settings["login_link"]){
                            $login_url = get_site_url() . DIRECTORY_SEPARATOR . $settings["login_link"];

                            $login_url = add_query_arg( 'redirect_to', urlencode( get_permalink() ), $login_url );
                        }

                        echo '<div class="jspred_message_login">';
                        if($login_url){
                            echo '<a href="'.wp_login_url( get_permalink() ).'" title="'.__("Login",'joomsport-prediction').'">'.__("Login",'joomsport-prediction').'</a>'.__(" to submit your predictions",'joomsport-prediction');

                        }else{
                            echo __("Login",'joomsport-prediction').' '.__(" to submit your predictions",'joomsport-prediction');
                        }
                        echo '</div>';

                    });
                }
                
                $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$this->usrid} AND round_id={$this->id}");
                if($this->canSave() && $editable){
                    $this->lists['knockout'] = $knObj->getView($prediction);
                }elseif(!$editable){
                    $this->lists['knockout'] = $knObj->getViewResult($prediction);
                    
                }
                
                
            }
        }elseif($this->roundtype == '3'){
            if(classJsportRequest::get('jspAction') == 'saveRound' && $this->canSave() && $this->canEditWinner()) {
                $roundWinner = (int)classJsportRequest::get('roundWinner');
                $roundNStages = (int)classJsportRequest::get('roundNStages');
                $roundTScorers = (int)classJsportRequest::get('roundTScorers');
                $data = array(
                    "cWinner" => array("choice" => $roundWinner),
                    "nedStage" => array("choice" => $roundNStages),
                    "topScorer" => array("choice" => $roundTScorers),
                );
                $predString = json_encode($data);

                $exist = $wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_round_users} WHERE user_id={$user_id} AND round_id={$this->id}");
                if ($exist) {

                    $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET prediction='" . addslashes($predString) . "' WHERE id={$exist}");


                } else {

                    $wpdb->query("INSERT INTO {$wpdb->jswprediction_round_users}(user_id,round_id,prediction,filldate)"
                        . " VALUES({$user_id},{$this->id},'" . $predString . "','" . date("Y-m-d H:i:s", current_time('timestamp', 0)) . "')");


                }

            }elseif(!$user_id && !$this->usrid){
                add_action("jspred_saved_notice", function(){
                    $settings = get_option("joomsport_prediction_settings","");
                    $login_url = '';
                    if(isset($settings["login_link"]) && $settings["login_link"]){
                        $login_url = get_site_url() . DIRECTORY_SEPARATOR . $settings["login_link"];

                        $login_url = add_query_arg( 'redirect_to', urlencode( get_permalink() ), $login_url );
                    }

                    echo '<div class="jspred_message_login">';
                    if($login_url){
                        echo '<a href="'.wp_login_url( get_permalink() ).'" title="'.__("Login",'joomsport-prediction').'">'.__("Login",'joomsport-prediction').'</a>'.__(" to submit your predictions",'joomsport-prediction');

                    }else{
                        echo __("Login",'joomsport-prediction').' '.__(" to submit your predictions",'joomsport-prediction');
                    }
                    echo '</div>';

                });


            }


        }else{
            $this->lists['matches'] = array();

            $matches_allready = $wpdb->get_col("SELECT match_id "
                    . " FROM {$wpdb->jswprediction_round_matches}"
                    . " WHERE round_id={$this->id}");

            usort($matches_allready, array($this,'cmp'));

            for($intA=0;$intA<count($matches_allready);$intA++){
                $match = new classJsportMatch($matches_allready[$intA]);
                $this->lists['matches'][] = $match;
            }   

            


            if(classJsportRequest::get('jspAction') == 'saveRound' && $this->canSave()){
                $roundTypeClass = $this->roundtype == 5?'outcome':'score';
                $predictionsDB = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_types} WHERE ptype='".$roundTypeClass."' ORDER BY ordering");


                $data = array();
                $intZ = 0;

                $pred = get_post_meta($this->league,'_jswprediction_league_points',true);
                $array_pred = array();
                
                for($intA = 0; $intA < count($predictionsDB); $intA++){
                    $path = JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
                    $classN = 'JSPT'.$predictionsDB[$intA]->identif;
                    if(is_file($path . $classN.'.php')){
                        require_once $path . $classN.'.php';
                        if(class_exists($classN)){
                            $this->lists['predictions'][$intZ]['object'] = new $classN;
                            if(isset($pred[$predictionsDB[$intA]->id]) && !in_array($predictionsDB[$intA]->ptype, $array_pred)){

                                $data[$predictionsDB[$intA]->ptype] = $this->lists['predictions'][$intZ]['object']->validateData($user_id, $this->id, $jspJoker);

                                $array_pred[] = $predictionsDB[$intA]->ptype;
                            }
                            $intZ++;
                        }
                    }
                }
                $predString = json_encode($data);
                $exist = $wpdb->get_var("SELECT id FROM {$wpdb->jswprediction_round_users} WHERE user_id={$user_id} AND round_id={$this->id}");
                if($exist){

                    $wpdb->query("UPDATE {$wpdb->jswprediction_round_users} SET prediction='".addslashes($predString)."' WHERE id={$exist}");
                }else{

                    $wpdb->query("INSERT INTO {$wpdb->jswprediction_round_users}(user_id,round_id,prediction,filldate)"
                            . " VALUES({$user_id},{$this->id},'".$predString."','".date("Y-m-d H:i:s",current_time( 'timestamp', 0 ))."')");
                }
                if(!has_action("jspred_saved_notice")) {
                    add_action("jspred_saved_notice", function () {
                        echo '<div class="jspred_success">' . __("Predictions saved", 'joomsport-prediction') . '</div>';

                    });
                }
                $this->Joker();

            }elseif(!$user_id && !$this->usrid){
                for($intM=0;$intM<count($this->lists['matches']);$intM++){
                    $match_id = $this->lists['matches'][$intM]->id;
                    $m_date = get_post_meta( $match_id, '_joomsport_match_date', true );
                    $m_time = get_post_meta( $match_id, '_joomsport_match_time', true );
                    $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );

                    if($m_played == '0'){
                     
                        if(($m_date > date("Y-m-d", current_time( 'timestamp', 0 ))) || ($m_date == date("Y-m-d", current_time( 'timestamp', 0 )) && $m_time > date("H:i", current_time( 'timestamp', 0 )))){
                            add_action("jspred_saved_notice", function(){
                                $settings = get_option("joomsport_prediction_settings","");
                                $login_url = '';
                                if(isset($settings["login_link"]) && $settings["login_link"]){
                                    $login_url = get_site_url() . DIRECTORY_SEPARATOR . $settings["login_link"];

                                    $login_url = add_query_arg( 'redirect_to', urlencode( get_permalink() ), $login_url );
                                }

                                echo '<div class="jspred_message_login">';
                                if($login_url){
                                    echo '<a href="'.wp_login_url( get_permalink() ).'" title="'.__("Login",'joomsport-prediction').'">'.__("Login",'joomsport-prediction').'</a>'.__(" to submit your predictions",'joomsport-prediction');

                                }else{
                                    echo __("Login",'joomsport-prediction').' '.__(" to submit your predictions",'joomsport-prediction');
                                }
                                echo '</div>';
                            });
                            break;
                        }
                    }
                }
                    
            }
        }
        
        
        $uname_str = '';
        if($this->usrid){
            $user = new WP_User($this->usrid);
            
            $uname = $user->data->display_name;
            $uname_str = ' ('.strtolower(__('User')).': ' . $uname . ')';
        }

        $this->lists['options']['title'] = get_the_title($leagueid) . $uname_str;
        $this->lists['options']['prleaders'] = $leagueid;
        $this->lists['options']['userleague'] = $leagueid;

        //get rounds
        $this->lists['round_list'] = $this->getRoundList();
    }
    public function cmp($a,$b){

        $a_date = get_post_meta( $a, '_joomsport_match_date', true );
        $a_time = get_post_meta( $a, '_joomsport_match_time', true );
        $b_date = get_post_meta( $b, '_joomsport_match_date', true );
        $b_time = get_post_meta( $b, '_joomsport_match_time', true );

        if ($a_date.' '.$a_time == $b_date.' '.$b_time) {
            return  0;
        }
        return  ($a_date.' '.$a_time < $b_date.' '.$b_time) ? -1 : 1;

    }
    public function getRow()
    {

        return $this;
    }
    public function getRowSimple()
    {
        return $this;
    }
    
    public function getPredict($match_id){
        global $wpdb;
        
        $canEdit = $this->canEditMatch($match_id);
        $canView = $this->canViewPred($match_id);
        
        $this->lists['predictions'] = array();

        $roundTypeClass = $this->roundtype == 5?'outcome':'score';

        $predictionsDB = $wpdb->get_results("SELECT * FROM {$wpdb->jswprediction_types} WHERE ptype='".$roundTypeClass."' ORDER BY ordering");
        $html = '';
        $intZ = 0;
        

        $pred = get_post_meta($this->league,'_jswprediction_league_points',true);
        
        $array_pred = array();
        
        for($intA = 0; $intA < count($predictionsDB); $intA++){
            $path = JOOMSPORT_PREDICTION_PATH.DIRECTORY_SEPARATOR.'sportleague'.DIRECTORY_SEPARATOR.'base'.DIRECTORY_SEPARATOR.'wordpress'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'predictions'.DIRECTORY_SEPARATOR;
            $classN = 'JSPT'.$predictionsDB[$intA]->identif;
            if(is_file($path . $classN.'.php')){
                require_once $path . $classN.'.php';
                if(class_exists($classN)){
                    $this->lists['predictions'][$intZ]['object'] = new $classN;
                    if(isset($pred[$predictionsDB[$intA]->id]) && !in_array($predictionsDB[$intA]->ptype, $array_pred)){
                        $html .= '<div class="jsp_prediction_'.$predictionsDB[$intA]->ptype.'">';
                        if($canView){
                            $html .= $this->lists['predictions'][$intZ]['object']->getView($match_id,$this->id,$this->usrid,$canEdit);
                        }
                        $html .= '</div>';
                        $array_pred[] = $predictionsDB[$intA]->ptype;
                    }
                    $intZ++;
                }
            }
        }
        return $html;
        
    }
    
    public function getMatchPoint($match_id){
        global $wpdb;
        
        //$user_id = classJsportUser::getUserId();
        if($this->usrid){
            $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$this->usrid} AND round_id={$this->id}");

            $pred = $prediction?json_decode($prediction, true):null;
            if(isset($pred['score'][$match_id]['points'])) {
                return $pred['score'][$match_id]['points'];
            }else if(isset($pred['outcome'][$match_id]['points'])){
                return $pred['outcome'][$match_id]['points'];
            }else{
                return '';
            }
        }
    }    
    
    public function canSave(){
        $user_id = get_current_user_id();

        if($user_id && $this->usrid == $user_id){
            return true;
        }else{
            return FALSE;
        }
    }
    
    public function knockoutEditable(){
        $mday = (int) get_post_meta($this->id,'_joomsport_round_knock_mday',true);
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
        
        
        for($intA = 0; $intA < count($matches->posts); $intA ++){

            $match = $matches->posts[$intA];
            
            $m_played = (int) get_post_meta( $match->ID, '_joomsport_match_played', true );
            $m_date = get_post_meta( $match->ID, '_joomsport_match_date', true );
            $m_time = get_post_meta( $match->ID, '_joomsport_match_time', true );

            if($m_played == '1'){
                return false;
            }
            if($m_date){
                if(($m_date > date("Y-m-d", current_time( 'timestamp', 0 ))) || ($m_date == date("Y-m-d", current_time( 'timestamp', 0 )) && $m_time > date("H:i", current_time( 'timestamp', 0 )))){

                }else{
                    return false;
                }
            }
            
        }    
        
        return true;
    }
    
    public function canEditMatch($match_id){
        global $jsDatabase;
        $user_id = get_current_user_id();
        if(!$user_id || $this->usrid != $user_id){
            return FALSE;
        }
        //echo date("H:i", current_time( 'timestamp', 0 ))."<br />";
        $m_date = get_post_meta( $match_id, '_joomsport_match_date', true );
        $m_time = get_post_meta( $match_id, '_joomsport_match_time', true );
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
                                
        if($m_played == '1' || $m_played == '-1'){
            return false;
        }
        if(($m_date > date("Y-m-d", current_time( 'timestamp', 0 ))) || ($m_date == date("Y-m-d", current_time( 'timestamp', 0 )) && $m_time > date("H:i", current_time( 'timestamp', 0 )))){
            return true;
        }
        return false;
    }
    public function canViewPred($match_id){
        global $jsDatabase;
        $user_id = get_current_user_id();
        if($user_id && $this->usrid == $user_id){
            return true;
        }
        
        $m_played = (int) get_post_meta( $match_id, '_joomsport_match_played', true );
        if($m_played == '1'){
            return true;
        }
        
        $m_date = get_post_meta( $match_id, '_joomsport_match_date', true );
        $m_time = get_post_meta( $match_id, '_joomsport_match_time', true );
        if(($m_date < date("Y-m-d", current_time( 'timestamp', 0 ))) || ($m_date == date("Y-m-d", current_time( 'timestamp', 0 )) && $m_time < date("H:i", current_time( 'timestamp', 0 )))){
            return true;
        }
        
        
        return false;
    }
    
    public function getView()
    {
        if($this->roundtype == '1'){
            return 'userround_knockout';
        }elseif($this->roundtype == '3'){
            return 'userround_winner';

        }elseif($this->roundtype == '5'){
            return 'userround_outcome';

        }else{
            return 'userround';
        }
        
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

    public function getRoundList(){
        $metaquery = array();
        $metaquery[] =
            array(
                'relation' => 'AND',
                array(
                    'key' => '_joomsport_round_leagueid',
                    'value' => $this->league,
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
        usort($rounds->posts, array($this,'cmpDate'));

        return $rounds->posts;
    }
    public function cmpDate($a,$b){


        if ($a->startdate == $b->startdate) {
            return  0;
        }
        return  ($a->startdate < $b->startdate) ? -1 : 1;

    }

    public function getRoundDD(){
        $this->lists['prev_round'] = $this->lists['next_round'] = null;
        $rounds = $this->lists['round_list'];

        $link = get_permalink($this->league);
        $link = add_query_arg( 'action', 'rounds', $link );
        $prl = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
        if($prl){
            $link = add_query_arg( 'prl', $prl, $link );
        }

        $html = '';
        $html .= '<select name="roundID" class="form-control" onchange="window.location=this.value">';
        $html .= '<option value="'.$link.'">'.esc_attr(__("All rounds",'joomsport-prediction')).'</option>';
        for($intA=0;$intA<count($rounds);$intA++){
            if($this->id == $rounds[$intA]->ID){
                if(isset($rounds[$intA-1]->ID)){
                    $this->lists['prev_round'] = $rounds[$intA-1]->ID;
                }
                if(isset($rounds[$intA+1]->ID)){
                    $this->lists['next_round'] = $rounds[$intA+1]->ID;
                }
            }
            $link = get_permalink($rounds[$intA]->ID);
            $link = add_query_arg( 'usrid', $this->usrid, $link );
            $html .= '<option value="'.$link.'" '.($this->id == $rounds[$intA]->ID?' selected="true"':"").'>'.esc_attr($rounds[$intA]->post_title).'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    public function getPrevRound(){
        $html = '';

        if($this->lists['prev_round']){
            $link = get_permalink($this->lists['prev_round']);
            $link = add_query_arg( 'usrid', $this->usrid, $link );
            $html .= '<span class="input-group-btn"><a class="btn btn-default" href="'.$link.'">';
        }

        $html .= '<input type="button" value="&lt;" />';
        if($this->lists['prev_round']){
            $html .= '</a></span>';
        }
        return $html;
    }
    public function getNextRound(){
        $html = '';

        if($this->lists['next_round']){
            $link = get_permalink($this->lists['next_round']);
            $link = add_query_arg( 'usrid', $this->usrid, $link );
            $html .= '<span class="input-group-btn"><a class="btn btn-default" href="'.$link.'">';
        }
        $html .= '<input type="button" value="&gt;" />';
        if($this->lists['next_round']){
            $html .= '</a></span>';
        }
        return $html;
    }
    public function getMatchJoker($match_id){
        global $wpdb;
        
    }

    public function Joker(){
        
    }

    public static function enableJoker(){
        $settings = get_option("joomsport_prediction_settings","");
        return isset($settings["joker_match"])?$settings["joker_match"]:0;
    }
    public function getHeader(){
        do_action("jswprediction_messages",$this->league,"round");
    }
    public function getFooter(){
        do_action("jswprediction_messages",$this->league,"round", 1);
    }

    //select winner roundtype 3
    public function canEditWinner(){

        $start_date = get_post_meta($this->id, '_joomsport_round_close_date', true);
        $startDate = date("Y-m-d", strtotime($start_date));
        $startTime = date("H:i:s", strtotime($start_date));

        if(($startDate > date("Y-m-d", current_time( 'timestamp', 0 ))) || ($startDate == date("Y-m-d", current_time( 'timestamp', 0 )) && $startTime > date("H:i", current_time( 'timestamp', 0 )))){
            return true;
        }
        return false;
    }

    public function getWinnerPredict(){
        global $wpdb;
        if($this->usrid){
            $user_id = $this->usrid;
        }else{
            $user_id = get_current_user_id();
        }
        if(!$user_id){
            return null;
        }
        $prediction = $wpdb->get_var("SELECT prediction FROM {$wpdb->jswprediction_round_users} WHERE user_id={$user_id} AND round_id={$this->id}");
        if($prediction){
            $predF = json_decode($prediction, true);
        }else{
            $predF = null;
        }

        return $predF;
    }

    public function getVraag(){

        $vraag = get_post_meta($this->id, '_joomsport_round_vraag', true);
        return $vraag;
    }
    public function getVraag2(){

        $vraag = get_post_meta($this->id, '_joomsport_round_vraag2', true);
        return $vraag;
    }
    public function getVraag3(){

        $vraag = get_post_meta($this->id, '_joomsport_round_vraag3', true);
        return $vraag;
    }
}
