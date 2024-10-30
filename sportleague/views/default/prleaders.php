<?php

$user_id = get_current_user_id();
$columns_settings = get_option("joomsport_prediction_lbcolumns_settings","");
$eColumns = 0;
if(!isset($columns_settings["exact"]) || $columns_settings["exact"]){
    $eColumns++;
}
if(!isset($columns_settings["diff"]) || $columns_settings["diff"]){
    $eColumns++;
}
if(!isset($columns_settings["winside"]) || $columns_settings["winside"]){
    $eColumns++;
}
if(!isset($columns_settings["failed"]) || $columns_settings["failed"]){
    $eColumns++;
}
$offset = isset($rows->lists['pagination']->offset)?$rows->lists['pagination']->offset:0;
?>
<div>
    <form role="form" method="post" lpformnum="1">
    <div class="table-responsive">
        <table class="table table-striped jsPredStatDIvFE">
            <thead>
                <tr>
                    <th rowspan="2" class="jsalcenter">
                        #

                    </th>
                    <th rowspan="2" style="text-align:left;">
                        <?php echo __('User','joomsport-prediction');?>

                    </th>

                    <th rowspan="2" class="jsalcenter">
                        <?php echo __('Points','joomsport-prediction');?>

                    </th>
                    <?php if(!isset($columns_settings["filled"]) || $columns_settings["filled"]){?>
                    <th rowspan="2" class="jsalcenter">
                        <?php echo __('Completed predictions','joomsport-prediction');?>

                    </th>
                    <?php
                    }
                    ?>
                    <?php if($eColumns){?>
                    <th colspan="<?php echo $eColumns;?>" class="jsalcenter">
                        <?php echo __('Guess statistic, num. (percent)','joomsport-prediction');?>

                    </th>
                    <?php
                    }
                    ?>

                </tr>
                <tr>
                    <?php if(!isset($columns_settings["exact"]) || $columns_settings["exact"]){?>
                    
                    <th><?php echo __('Exact','joomsport-prediction');?></th>
                    <?php } ?>
                    <?php if(!isset($columns_settings["diff"]) || $columns_settings["diff"]){?>
                    
                    <th><?php echo __('Score difference','joomsport-prediction');?></th>
                    <?php } ?>
                    <?php if(!isset($columns_settings["winside"]) || $columns_settings["winside"]){?>
                    <th><?php echo __('Correct winner','joomsport-prediction');?></th>
                    <?php } ?>
                    <?php if(!isset($columns_settings["failed"]) || $columns_settings["failed"]){?>
                    <th><?php echo __('Failed','joomsport-prediction');?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            for ($intA = 0; $intA < count($rows->object); ++$intA) {
                $round = $rows->object[$intA];
                
                ?>
                <tr <?php echo ($user_id == $round->user_id)?' class="jspred_highlited"':'';?>>
                    <td style="text-align:left;" nowrap="nowrap">
                        <?php
                        $curPlace = $intA + 1 + $offset;
                        echo $curPlace;
                        ?>
                        <?php
                        
                        ?>
                    </td>
                    <td style="text-align:left;" nowrap="nowrap">
                        <?php
                        $link = get_permalink($rows->league);
                        $link = add_query_arg( 'action', 'rounds', $link );
                        $link = add_query_arg( 'usrid', $round->user_id, $link );
                        $user = new WP_User($round->user_id);
            
                        $uname = $user->data->display_name;
                        
                        ?>
                        
                        <a href="<?php echo $link;?>">
                           
                            <?php 
                            if(!isset($columns_settings["avatar"]) || $columns_settings["avatar"]){
                                echo get_avatar( $user->data->user_email, $size = '24');
                            }
                            ?>
                            <?php echo ($uname);?>
                            
                        </a>
                        
                    </td>

                    <td class="jsalcenter">
                        <?php echo $round->pts;?>
                    </td>
                    <?php if(!isset($columns_settings["filled"]) || $columns_settings["filled"]){?>
                    <td class="jsalcenter">
                        <?php echo $round->filled;?>
                    </td>
                    <?php
                    }
                    ?>
                    <?php if(!isset($columns_settings["exact"]) || $columns_settings["exact"]){?>
                    <td class="jsalcenter">
                        <?php echo $round->success;
                        if($round->success){
                            echo '('.round($round->succavg*100).')';
                        }?>
                    </td>
                    <?php
                    }
                    ?>
                    <?php if(!isset($columns_settings["diff"]) || $columns_settings["diff"]){?>
                    <td class="jsalcenter">
                        <?php 
                        echo $round->score_diff;
                        if($round->score_diff){
                            echo '('.round(100*($round->score_diff/$round->filled)).')';
                        }
                        ?>
                    </td>
                    <?php
                    }
                    ?>
                    <?php if(!isset($columns_settings["winside"]) || $columns_settings["winside"]){?>
                    <td class="jsalcenter">
                        <?php 
                        echo $round->winner_side;
                        if($round->winner_side){
                            echo '('.round(100*($round->winner_side/$round->filled)).')';
                        }
                        ?>
                    </td>
                    <?php
                    }
                    ?>
                    <?php if(!isset($columns_settings["failed"]) || $columns_settings["failed"]){?>
                    <td class="jsalcenter">
                        <?php
                        $failed = $round->filled - $round->success - $round->score_diff - $round->winner_side;
                        echo $failed;
                        if($failed){
                            echo '('.round(100*($failed/$round->filled)).')';
                        }
                        ?>
                    </td>
                    <?php
                    }
                    ?>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    </div>    
    <?php
    if (isset($rows->lists['pagination']) && $rows->lists['pagination']) {
        require_once JOOMSPORT_PATH_VIEWS.'elements'.DIRECTORY_SEPARATOR.'pagination.php';
        echo paginationView($rows->lists['pagination']);
    }
    ?>
    </form>
</div>
<?php

?>