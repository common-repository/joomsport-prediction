<?php
if($rows->usrid){
    $user = new WP_User($rows->usrid);
    $uname = $user->data->display_name;
    echo '<h3 class="jsPredUsrTitle">';
    echo get_avatar( $user->data->user_email, $size = '24');
    echo $uname;
    echo '</h3>';
}
?>
<div>
    <div class="table-responsive">
        <div class="jstable">
            <div class="jstable-row">
                    <div class="jstable-cell">
                        <?php echo __('Rounds','joomsport-prediction');?>

                    </div>
                    <div class="jstable-cell">
                        <?php echo __('First match start date','joomsport-prediction');?>

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo __('Matches, predicted / total','joomsport-prediction');?>
                        

                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo __('Points','joomsport-prediction');?>

                    </div>

                </div>
            <?php
            for ($intA = 0; $intA < count($rows->object); ++$intA) {
                $round = $rows->object[$intA];
                $link = get_permalink($round->ID);
                $link = add_query_arg( 'usrid', $rows->usrid, $link );
                ?>
                <div class="jstable-row">
                    <div class="jstable-cell">
                        <a href="<?php echo $link?>"><?php echo ($round->post_title);?></a>
                    </div>
                    <div class="jstable-cell">
                        <?php 
                        $res = $rows->getRoundStatus($round->ID, $round->startdate);
                        switch($res){
                            case '0':
                                echo __('Predictions closed', 'joomsport-prediction');
                                break;
                            
                            default:
                                echo $round->startdate;
                                break;
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php 
                        
                        
                        echo $rows->getFilling($round->ID);

                        switch($res){

                            case '1':
                                echo '  <a href="'.$link.'"><input type="button" class="btn btn-success" value="'.__('Predict', 'joomsport-prediction').'" /></a>';
                                break;
                            case '2':
                                echo '  <a href="'.$link.'"><input type="button" class="btn btn-default" value="'.__('Change', 'joomsport-prediction').'" /></a>';
                                break;
                            default:

                                break;
                        }
                        ?>
                    </div>
                    <div class="jstable-cell jsalcenter">
                        <?php echo $rows->getPoints($round->ID);?>
                    </div>
                </div>
            <?php
            }
            ?>

        </div>
    </div>    
</div>
