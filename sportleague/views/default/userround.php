<?php
$joker_match = classJsportUserround::enableJoker();
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
        <?php if (jsHelper::isMobile()): ?>
            <div class="jsMatchPredContainer">
                <?php do_action("jspred_saved_notice");?>
                
                <?php
                for ($intA = 0; $intA < count($lists['matches']); ++$intA) {
                    $match = $lists['matches'][$intA];

                    $m_played = get_post_meta( $match->id, '_joomsport_match_played', true );
                    ?>
                    <div class="jsMatchPredItem">
                        <div class="jsMatchPredHeader">
                            <div class="jsMatchPredDate">
                                <?php
                                $m_date = get_post_meta( $match->id, '_joomsport_match_date', true );
                                $m_time = get_post_meta( $match->id, '_joomsport_match_time', true );
                                $match_date = classJsportDate::getDate($m_date, $m_time);

                                echo $match_date;
                                ?>
                            </div>
                            <div class="jsMatchPredLink">
                                <?php echo classJsportLink::match(__('View Match','joomsport-prediction'), $match->id, false, '', 0); ?>
                            </div>
                        </div>
                        <div class="jsMatchPredMain">
                            <div class="jsMatchPredTeams">
                                <div class="jsMatchPredTeam">
                                    <?php
                                    $partic_home = $match->getParticipantHome();

                                    if(is_object($partic_home)){
                                        echo $partic_home->getEmblem(true, 0, '');
                                        echo jsHelper::nameHTML($partic_home->getName(true));
                                    }
                                    ?>
                                </div>
                                <div class="jsMatchPredTeam">
                                    <?php
                                    $partic_away = $match->getParticipantAway();

                                    if(is_object($partic_away)){
                                        echo $partic_away->getEmblem(true, 0, '');
                                        echo jsHelper::nameHTML($partic_away->getName(true));
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="jsMatchPredScore">
                                <?php 
                                if($m_played == 0) {
                                    echo $rows->getPredict($match->id);
                                } elseif($m_played == 1 || $m_played == -1) {
                                    echo jsHelper::getScoreBigM($match);
                                }
                                ?>
                            </div>
                        </div>

                        <?php if($m_played == 1 || $m_played == -1): ?>
                            <div class="jsMatchPredFooter">
                                <?php
                                echo '<div class="jsMatchPredScorePredicted">'.__('Prediction','joomsport-prediction').': '.$rows->getPredict($match->id).'</div>';

                                if($m_played == 1){
                                    echo '<div class="jsMatchPredScorePoints">'.__('Points','joomsport-prediction').': '.$rows->getMatchPoint($match->id).'</div>';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php else: ?>
                <div class="table-responsive">
                    <?php do_action("jspred_saved_notice");?>
                    <div class="jstable">
                        <div class="jstable-row">
                            <div class="jstable-cell">
                                <?php echo __('Date and time','joomsport-prediction');?>
                            </div>
                            <div class="jstable-cell">
                                <?php echo __('Participant','joomsport-prediction');?>
                            </div>
                            <div class="jstable-cell">
                                <?php echo __('Participant','joomsport-prediction');?>
                            </div>
                            <div class="jstable-cell jsalcenter">
                                <?php echo __('Prediction','joomsport-prediction');?>
                            </div>
                            <div class="jstable-cell jsalcenter">
                                <?php echo __('FT','joomsport-prediction');?>
                                <?php echo __('Score','joomsport-prediction');?>
                            </div>
                            <div class="jstable-cell jsalcenter">
                                <?php echo __('Points','joomsport-prediction');?>
                            </div>
                            <?php
                            if($joker_match){
                                ?>
                                <div class="jstable-cell jsalcenter">
                                    <?php echo __('Joker','joomsport-prediction');?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                        for ($intA = 0; $intA < count($lists['matches']); ++$intA) {
                            $match = $lists['matches'][$intA];
                            ?>
                            <div class="jstable-row">
                                <div class="jstable-cell">
                                    <?php
                                    $m_date = get_post_meta( $match->id, '_joomsport_match_date', true );
                                    $m_time = get_post_meta( $match->id, '_joomsport_match_time', true );
                                    $match_date = classJsportDate::getDate($m_date, $m_time);

                                    echo $match_date;
                                    ?>
                                </div>
                                <div class="jstable-cell">
                                    <div class="jsDivLineEmbl">
                                        <?php
                                        $partic_home = $match->getParticipantHome();

                                        if(is_object($partic_home)){
                                            echo $partic_home->getEmblem(true, 0, '');
                                            echo jsHelper::nameHTML($partic_home->getName(true));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="jstable-cell">
                                    <div class="jsDivLineEmbl">
                                        <?php
                                        $partic_away = $match->getParticipantAway();

                                        if(is_object($partic_away)){
                                            echo $partic_away->getEmblem(true, 0, '');
                                            echo jsHelper::nameHTML($partic_away->getName(true));
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="jstable-cell jsalcenter" style="white-space: nowrap;">
                                    <?php echo $rows->getPredict($match->id); ?>
                                </div>
                                <div class="jstable-cell jsalcenter">
                                    <?php echo jsHelper::getScore($match, '','',0, true); ?>
                                </div>
                                <div class="jstable-cell jsalcenter">
                                    <?php echo $rows->getMatchPoint($match->id); ?>
                                </div>

                                <?php
                                if($joker_match){
                                    echo '<div class="jstable-cell jsalcenter">';
                                    echo $rows->getMatchJoker($match->id);
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            if($rows->canSave()){
                ?>
                <div class="clearfix">
                    <input type="hidden" name="jspJoker" id="jspJoker" value="<?php echo $rows->joker_match;?>" />
                    <input type="button" class="btn btn-success pull-right button" id="jspRoundSave" value="<?php echo __('Submit my predictions','joomsport-prediction');?>" />
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
