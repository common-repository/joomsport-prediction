<?php
global $wpdb;

echo '<div class="jswUserStatWidget">';
    echo '<div class="jswUserStatWidgetInfo">';
        echo '<div class="jswUserStatWidgetName">'.$user->display_name.'</div>';
        echo '<div class="jswUserStatWidgetAvatar">'.get_avatar($user->ID).'</div>';
        echo '<div class="jswUserStatWidgetLeagueName">';
            $privateID = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
            remove_filter('the_title', 'jspred_filter_privatetitle');
            if($privateID){
                $query = "SELECT leagueName FROM {$wpdb->jswprediction_private_league} "
                    . " WHERE id = ".$privateID;
                $private_title = $wpdb->get_var($query);
                if($private_title){
                    echo  $private_title . " (<a href='".get_permalink($leagueID)."'>".get_the_title($leagueID)."</a>)";
                }
            }else{
                echo '<a href="'.get_permalink($leagueID).'">'.get_the_title($leagueID).'</a>';
            }
            add_filter('the_title', 'jspred_filter_privatetitle');
        echo '</div>';
    echo '</div>';
    echo '<div class="jswUserStatWidgetLeague">';
        echo '<div class="jswUserStatWidgetLeaguePoints">';
        if(isset($myPos->pts)){
            echo '<div>'.$myPos->pts.'</div>';
            echo '<div>' . __('Points','joomsport-prediction') . '</div>';
        }
        echo '</div>';
        echo '<div class="jswUserStatWidgetLeaguePosition">';
            if(isset($myPos->position)){
                echo '<div>'.$myPos->position.'</div>';
                echo '<div>' . __('Position','joomsport-prediction') . '</div>';
            }
        echo '</div>';
        /*echo '<div class="jswUserStatWidgetLeagueSettings">';
            echo '<a class="btn" href="'.admin_url( 'user-edit.php?user_id=' . $user->ID, 'http' ).'"><input type="button" value="'.__("Settings", 'joomsport-prediction').'" />';
            //echo '<a class="btn" href="'.site_url( '/edit-profile/', 'http' ).'">Settings</a>';
        echo '</div>';*/
    echo '</div>';
echo '</div>';
