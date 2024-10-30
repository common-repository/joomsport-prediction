<?php

if($rows->usrid){
    $user = new WP_User($rows->usrid);
    $uname = $user->data->user_login;
    // echo '<h3 class="jsPredUsrTitle">';
    // echo get_avatar( $user->data->user_email, $size = '24');
    // echo $uname;
    // echo '</h3>';
}

$userID = get_current_user_id();

?>
<div class="jsPredRoundHeader row clearfix">
    <div class="col-xs-6 col-sm-6">
        <?php
        if($rows->usrid){
            $user = new WP_User($rows->usrid);
            $uname = $user->data->display_name;
            echo '<div class="jsPredUsrTitle">';
            echo get_avatar( $user->data->user_email, $size = '24');
            echo '<span>'. $uname .'</span>';
            echo '</div>';
        }
        ?>
    </div>
    <div class="col-xs-6 col-sm-6">
        <?php
        $ddList = $rows->getRoundDD();
        echo '<div class="jswDDRounds">';
        ?>
        <div class="input-group">
            <?php
            echo $rows->getPrevRound();
            echo $ddList;
            echo $rows->getNextRound();
            ?>
        </div>
        <?php
        echo '</div>';
        ?>
    </div>
</div>
<div>
    <form action="" method="post" name="jspRound" id="jspRound">
        <?php
        do_action("jspred_saved_notice");
        $particsort = get_post_meta($rows->id, '_joomsport_round_partic_sort', true);
        $startDate = get_post_meta($rows->id, '_joomsport_round_start_match', true);
        $roundWinner = $nedStage = $topScorer = 0;
        $predict = $rows->getWinnerPredict();

        if(isset($predict['cWinner']) && intval($predict['cWinner']['choice'])){
            $roundWinner = $predict['cWinner']['choice'];
        }

        if(isset($predict['nedStage']) && intval($predict['nedStage']['choice'])){
            $nedStage = $predict['nedStage']['choice'];
        }

        if(isset($predict['topScorer']) && intval($predict['topScorer']['choice'])){
            $topScorer = $predict['topScorer']['choice'];
        }
        ?>
        <div class="table-responsive">
            <div class="jstable">
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php echo __('Frage','joomsport-prediction');?>
                    </div>

                    <div class="jstable-cell jsalcenter">
                        <?php echo __('Prediction','joomsport-prediction');?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo __('Winner','joomsport-prediction');?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo __('Points','joomsport-prediction');?>
                    </div>
                </div>
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php
                        echo nl2br($rows->getVraag());

                        ?>
                    </div>

                    <div class="jstable-cell jsalcenter">
                        <?php
                        if($rows->canEditWinner() && $rows->canSave()) {
                            if (count($particsort)) {
                                echo '<select name="roundWinner" >';
                                echo "<option value='0' />Select</option>";
                                foreach ($particsort as $p) {
                                    echo "<option value='" . $p . "' " . ($roundWinner && $roundWinner == $p ? 'selected' : '') . " />" . get_the_title($p) . "</option>";

                                }
                                echo '</select>';
                            }
                        }else{
                            if($roundWinner){
                                echo get_the_title($roundWinner);
                            }
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        $roundWinner = get_post_meta($rows->id, '_joomsport_round_winner', true);
                        echo $roundWinner?get_the_title($roundWinner):"";
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        if(isset($predict['cWinner']) && isset($predict['cWinner']['points']) && ($predict['cWinner']['points'] !== '')){
                            echo $predict['cWinner']['points'];
                        }
                        ?>
                    </div>
                </div>
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php
                        echo nl2br($rows->getVraag2());

                        ?>
                    </div>

                    <div class="jstable-cell jsalcenter">
                        <?php
                        $nStages = jsPredictionHelper::getWkNedStages();
                        if($rows->canEditWinner() && $rows->canSave()) {
                            if (count($particsort)) {


                                if(count($nStages)){
                                    echo '<select name="roundNStages" >';
                                    echo "<option value='0' />Select</option>";
                                    foreach ($nStages as $key=>$val){
                                        echo "<option value='".$key."' ".($nedStage && $nedStage == $key?'selected':'')." />".$val."</option>";

                                    }
                                    echo '</select>';
                                }

                            }
                        }else{
                            if($nedStage){
                               echo $nStages[$nedStage];
                            }
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php

                        $nStageResult= get_post_meta($rows->id, '_joomsport_round_ned_stage', true);
                        echo $nStageResult?$nStages[$nStageResult]:"";
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        if(isset($predict['nedStage']) && isset($predict['nedStage']['points']) && ($predict['nedStage']['points'] !== '')){
                            echo $predict['nedStage']['points'];
                        }
                        ?>
                    </div>
                </div>
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php
                        echo nl2br($rows->getVraag3());

                        ?>
                    </div>

                    <div class="jstable-cell jsalcenter">
                        <?php
                        $nStages = jsPredictionHelper::getWkTopScorers();
                        if($rows->canEditWinner() && $rows->canSave()) {
                            if (count($particsort)) {


                                if(count($nStages)){
                                    echo '<select name="roundTScorers" >';
                                    echo "<option value='0' />Select</option>";
                                    foreach ($nStages as $key=>$val){
                                        echo "<option value='".$key."' ".($topScorer && $topScorer == $key?'selected':'')." />".$val."</option>";

                                    }
                                    echo '</select>';
                                }

                            }
                        }else{
                            if($topScorer){
                                echo $nStages[$topScorer];
                            }
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        $topScorerRes = get_post_meta($rows->id, '_joomsport_round_topscorer', true);
                        if($topScorerRes && is_array($topScorerRes)){
                            foreach ($topScorerRes as $ts){
                                echo $nStages[$ts]."<br />";
                            }
                        }
                        //echo $topScorerRes?$nStages[$topScorerRes]:"";
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php
                        if(isset($predict['topScorer']) && isset($predict['topScorer']['points']) && ($predict['topScorer']['points'] !== '')){
                            echo $predict['topScorer']['points'];
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php


        if($rows->canSave() && $rows->canEditWinner()){

            ?>
            <div class="clearfix">
                <input type="button" class="btn btn-success pull-right button" id="jspRoundSaveWR" value="<?php echo __('Submit my predictions','joomsport-prediction');?>" />
            </div>
            <?php
        }
        ?>
        <input type="hidden" name="jspAction" value="saveRound" />
    </form>
</div>
<?php
//classJsportAddtag::addJS(JS_LIVE_ASSETS.'js/jsprediction.js');
//classJsportAddtag::addCSS(JS_LIVE_ASSETS.'css/prediction.css');
?>
