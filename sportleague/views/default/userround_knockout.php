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
        <div id="jsprediction_bracket">
            <?php echo $rows->lists['knockout'];?>
        </div>    
    <?php
    if($rows->canSave() && $rows->lists["knockout_editable"]){

    ?>
    <div>
        <input type="button" class="btn btn-success pull-right button" id="jspRoundKnockSave" value="<?php echo __('Submit my predictions','joomsport-prediction');?>" />
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