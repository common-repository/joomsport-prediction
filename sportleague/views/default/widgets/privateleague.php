<?php
/*
 * Private League Shortcode
 */

require_once JOOMSPORT_PREDICTION_PATH.'sportleague'. DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'class-jsprediction-myleagues.php';

$isHidden = (count($archiveLeagues) || count($invitedLeague) || count($activeLeagues))?' style="display:block;"':' style="display:none;"';
?>
<div id="joomsport-container" class="plsContainer" >
    <nav class="navbar clearfix">
        <div class="nav navbar-nav pull-right">
            <input id="jspNewLeague" class="btn" type="button" value="<?php echo __('Create Private League', 'joomsport-prediction');?>" />
        </div>
    </nav>
    <div>
        <div class="tabs" id="plTabsContainerdiv" <?=$isHidden;?>>


            <div class="tab-content">
                <div class="row">
                    <div id="activeLeagues" class="tab-pane fade in active" >
                        <?php
                        for($intA=0;$intA<count($invitedLeague);$intA++){
                            $row = new jsPredictionLeagueRow($invitedLeague[$intA]->ID);
                            $actions = $row->getActionsListPending();

                            echo '<div class="col-lg-4 col-sm-6 col-xs-12">';
                            echo '<div class="table-responsive">';
                            echo '<div class="jsPrivHeaderBlock">';
                            echo '<a href="'.$row->getLink().'"><div>'.$row->getTitle().'</div><span>'.$row->getBasedLeague().'</span></a>';
                            echo '</div>';
                            echo '<div class="jsPrivMainBlock" data-league="'.$invitedLeague[$intA]->ID.'">';
                            echo '<div class="jsPrivUsers"><div class="row">';
                            echo '<div class="col-xs-7"><i class="js-users" aria-hidden="true"></i>'.$row->getUsersCount().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["join"])?$actions["join"]:"").'</div>';
                            echo '</div></div>';
                            echo '<div class="jsPrivOwner"><div class="row">';
                            echo '<div class="col-xs-7"><i class="fa fa-address-book-o" aria-hidden="true"></i>'.$row->getOwner().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["reject"])?$actions["reject"]:"").'</div>';
                            echo '</div></div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>

                        <?php
                        for($intA=0;$intA<count($activeLeagues);$intA++){
                            $row = new jsPredictionLeagueRow($activeLeagues[$intA]->ID);
                            $actions = $row->getActionsList();

                            echo '<div class="col-lg-4 col-sm-6 col-xs-12">';
                            echo '<div class="table-responsive">';
                            echo '<div class="jsPrivHeaderBlock">';
                            echo '<a href="'.$row->getLink().'"><div>'.$row->getTitle().'</div><span>'.$row->getBasedLeague().'</span></a>';
                            echo '</div>';
                            echo '<div class="jsPrivMainBlock" data-league="'.$activeLeagues[$intA]->ID.'">';
                            echo '<div class="jsPrivUsers"><div class="row">';
                            echo '<div class="col-xs-7"><i class="js-users" aria-hidden="true"></i>'.$row->getUsersCount().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["invite"])?$actions["invite"]:"").(isset($actions["leave"])?$actions["leave"]:"").'</div>';
                            echo '</div></div>';
                            echo '<div class="jsPrivOwner"><div class="row">';
                            echo '<div class="col-xs-7"><i class="js-owner" aria-hidden="true"></i>'.$row->getOwner().'</div>';
                            echo '<div class="col-xs-5 jsright">'.(isset($actions["edit"])?$actions["edit"]:"").'</div>';
                            echo '</div></div>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
<div style="display:none;" id="dialogJSnewLeague" title="<?php echo __('Create New League', 'joomsport-prediction');?>">

    <form id="formJSPNewLeague" name="formJSPNewLeague">
        <fieldset>
            <label for="leaguename"><?php echo __('League name', 'joomsport-prediction');?></label>
            <input type="text" name="leaguename" id="leaguename" value="" class="text ui-widget-content ui-corner-all">
            <label for="base_league"><?php echo __('Based on Competition', 'joomsport-prediction');?></label>
            <?php
            $posts = jsPredictionHelper::getActiveMainLeaguesList();
            $list = jsPredictionHelper::getOptionsFromPostList($posts);
            $leagueID = get_option("jsw_prediction_default_league", 0);
            echo JoomSportHelperSelectBox::Simple("base_league" , $list, $leagueID, '', false);
            ?>
            <label for="import_from"><?php echo __('Import participants from', 'joomsport-prediction');?></label>
            <?php
            $posts = jsPredictionMyLeagues::getMyLeagues();
            $list = jsPredictionHelper::getOptionsFromPostList($posts);
            echo JoomSportHelperSelectBox::Simple("import_from" , $list, 0, '');

            ?>
            <input type="hidden" name="defleague" value="<?=$leagueID;?>" />
        </fieldset>
    </form>
</div>
<div style="display:none;" id="dialogJSnewLeagueParticipants" title="<?php echo __('Manage participants', 'joomsport-prediction');?>">

    <form id="formJSPNewLeaguePartic" name="formJSPNewLeaguePartic">
        <fieldset>
            <div class="jspmodalFields">
                <div class="jspsocial-media">
                    <div class="jspmodalHeader">
                        <?php echo __('Invite options', 'joomsport-prediction');?>
                    </div>
                    <div class="jspmodalMainBlock clearfix">
                        <div>
                            <a class="jsp-btn jsp-default" id="raadEmaillink" href="mailto:user@example.com?subject=s&amp;body=b">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                                <span class="social-media-icon__caption"><?php echo __('Send invite by your email client', 'joomsport-prediction');?></span>
                            </a>
                        </div>
                        <div>
                            <div class="jsprDivCopied"><?php echo __('Copied', 'joomsport-prediction');?></div>
                            <div id="jsprInviteLink" class="jsp-btn jsp-primary">
                                <?php echo __('Copy invite link', 'joomsport-prediction');?>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="jspmodalHeader">
                        <?php echo __('Invite site users', 'joomsport-prediction');?>
                    </div>
                    <div class="jspmodalMainBlock">
                        <?php echo '<select multiple name="user_invited[]" class="jswf-data-users-ajax" data-placeholder="'.__('Invite users', 'joomsport-prediction').'" ></select>'; ?>
                    </div>
                </div>
                <div class="jspinvitebyemail">
                    <div class="jspmodalHeader">
                        <?php echo __('Send invite email via our site', 'joomsport-prediction');?>
                    </div>
                    <div class="jspmodalMainBlock">
                        <table class="tblInviteEmail">
                            <tr>
                                <td><input type="text" name="invbyemail_name[]" placeholder="<?=__('Name', 'joomsport-prediction');?>" /></td>
                                <td><input type="email" name="invbyemail_email[]" placeholder="<?=__('Email', 'joomsport-prediction');?>" /></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="invbyemail_name[]" placeholder="<?=__('Name', 'joomsport-prediction');?>" /></td>
                                <td><input type="email" name="invbyemail_email[]" placeholder="<?=__('Email', 'joomsport-prediction');?>" /></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="invbyemail_name[]" placeholder="<?=__('Name', 'joomsport-prediction');?>" /></td>
                                <td><input type="email" name="invbyemail_email[]" placeholder="<?=__('Email', 'joomsport-prediction');?>" /></td>
                            </tr>
                        </table>
                        <input type="button" class="btnAddEmails" value="+" />
                    </div>
                </div>
                <div>
                    <div class="jspmodalHeader">
                        <?php echo __('Participants', 'joomsport-prediction');?>
                    </div>
                    <div class="jspmodalMainBlock">
                        <div id="JSparticList"></div>
                    </div>
                </div>
            </div>
        </fieldset>
    </form>
</div>

<div style="display:none;" id="dialogJSeditLeague" title="<?php echo __('Edit league', 'joomsport-prediction');?>">
    <form id="formJSPEditLeague" name="formJSPEditLeague">
        <label for="leaguename"><?php echo __('League name', 'joomsport-prediction');?></label>
        <input type="text" name="edit_leaguename" id="edit_leaguename" value="" class="text ui-widget-content ui-corner-all">
        <label for="import_from"><?php echo __('Import participants from', 'joomsport-prediction');?></label>
        <?php
        $posts = jsPredictionMyLeagues::getMyLeagues();
        $list = jsPredictionHelper::getOptionsFromPostList($posts);
        echo JoomSportHelperSelectBox::Simple("import_from_edit" , $list, 0, '');
        ?>
    </form>
</div>