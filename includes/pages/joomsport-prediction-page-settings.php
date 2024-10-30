<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomsportPredictionPageSettings{
    public static function action(){
        global $wpdb;
        
        if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
            $sort_columns = $_POST['sort_columns'];
            $pred_livecalc = intval($_POST['pred_livecalc']);
            $private_league = intval($_POST['private_league']);
            $login_link = sanitize_text_field($_POST['login_link']);
            $plrivate_league_shortcode_link = sanitize_text_field($_POST['plrivate_league_shortcode_link']);
            $joker_match = intval($_POST['joker_match']);


            $sort = array();

            for($intA = 0; $intA < count($sort_columns); $intA ++){
                $sort[$sort_columns[$intA]] = intval($_POST[$sort_columns[$intA].'_way']);
            }
            $settings_json = array("sort" => $sort, "roundcalc" => $pred_livecalc, "private_league" => $private_league, "login_link" => $login_link, "plrivate_league_shortcode_link" => $plrivate_league_shortcode_link, "joker_match" => $joker_match);
            update_option("joomsport_prediction_settings", $settings_json);
            
            
            
            //blocks
            $top_predictions = intval($_POST['jsmb_top_predictions']);
            $winner_side = intval($_POST['jsmb_winner_side']);
            $both_score = intval($_POST['jsmb_both_score']);
            $score_over = intval($_POST['jsmb_score_over']);
            $top_prediction_num = intval($_POST['top_prediction_num']);
            $score_over_num = floatval($_POST['score_over_num']);

            $settings_json = array(
                "top_predictions" => $top_predictions,
                "top_prediction_num" => $top_prediction_num,
                "winner_side" => $winner_side,
                "both_score" => $both_score,
                "score_over" => $score_over,
                "score_over_num" => $score_over_num
                );
            update_option("joomsport_prediction_blocks_settings", $settings_json);
            
            $lbColumns = $_POST['lbColumns'];
            update_option("joomsport_prediction_lbcolumns_settings", $lbColumns);
            
            
            $knscore = $_POST['knscore'];
            update_option("joomsport_prediction_knockout_settings", $knscore);

            $mail_json = $_REQUEST["mail_settings"];
            update_option("joomsport_prediction_mail_settings", $mail_json);
            
        }
        
        $lists = array();
        
        $settings = get_option("joomsport_prediction_settings","");
        if(!$settings){
            $sort_default = array("pts" => 0,
                "filled" => 1 ,
                "succavg" => 0);
            $round_calc = "0";
            
            $settings_json = array("sort" => $sort_default, "roundcalc" => $round_calc, "private_league" => 0, "login_link" => '', "plrivate_league_shortcode_link" => '', 'joker_match' => 0);
            update_option("joomsport_prediction_settings", $settings_json);
            
            $settings = $settings_json;
        }


        $mail_settings = get_option("joomsport_prediction_mail_settings","");
        
        $sortfields = $settings["sort"];
        $roundcalc = $settings["roundcalc"];
        $private_league = isset($settings["private_league"])?$settings["private_league"]:0;
        $joker_match = isset($settings["joker_match"])?$settings["joker_match"]:0;

        
        $is_field_yn = array();
        $is_field_yn[] = JoomSportHelperSelectBox::addOption(0, __("No", 'joomsport-prediction'));
        $is_field_yn[] = JoomSportHelperSelectBox::addOption(1, __("Yes", 'joomsport-prediction'));
        
        $is_field_way = array();
        $is_field_way[] = JoomSportHelperSelectBox::addOption(0, __("DESC", 'joomsport-prediction'));
        $is_field_way[] = JoomSportHelperSelectBox::addOption(1, __("ASC", 'joomsport-prediction'));
        
        $sort = array("pts" => __('Points', 'joomsport-prediction'),
            "succavg" => __('Exact guess rate', 'joomsport-prediction') ,
            "filled" => __('Completed predictions', 'joomsport-prediction'));
        
        $blocks_settings = get_option("joomsport_prediction_blocks_settings","");
        
        $knock_settings = get_option("joomsport_prediction_knockout_settings","");
        
        $columns_settings = get_option("joomsport_prediction_lbcolumns_settings","");
        
        
        ?>
    <script  type="text/javascript">
        jQuery( document ).ready(function() {
                    
            jQuery("#jspred_config_sort").sortable(

            );
            jQuery( "#jspred_config_sort" ).disableSelection();

            jQuery("input[name='jsmb_top_predictions']").on("change",function(){
                if(jQuery("input[name='jsmb_top_predictions']:checked").val() == 1){
                    jQuery(".jsdiv_prediction").show();
                }else{
                    jQuery(".jsdiv_prediction").hide();
                }
            });
            if(jQuery("input[name='jsmb_top_predictions']:checked").val() == 1){
                jQuery(".jsdiv_prediction").show();
            }else{
                jQuery(".jsdiv_prediction").hide();
            }
            
            jQuery("input[name='jsmb_score_over']").on("change",function(){
                if(jQuery("input[name='jsmb_top_predictions']:checked").val() == 1){
                    jQuery(".jsdiv_scoreover").show();
                }else{
                    jQuery(".jsdiv_scoreover").hide();
                }
            });
            if(jQuery("input[name='jsmb_score_over']:checked").val() == 1){
                jQuery(".jsdiv_scoreover").show();
            }else{
                jQuery(".jsdiv_scoreover").hide();
            }
            
            
            
        });
    </script>
        <div class="jsSettingsPage">
            <form method="post">
            <div  id="main_conf_div">
                <div class="jsrespdiv10">
                <div class="jsBepanel">
                    <div class="jsBEheader">
                        <?php echo __('General', 'joomsport-prediction');?>
                    </div>
                    <div class="jsBEsettings">
                        <table class="adminlistsNoBorder">
                            
                            <tr>
                                <td>
                                    <?php echo __('Sort leaders table by', 'joomsport-prediction');?>
                                </td>
                                <td>
                                    <table>
                                        <tbody id="jspred_config_sort">
                                        <?php 
                                        if(count($sortfields)){
                                            foreach($sortfields as $key => $value){

                                            ?>
                                            <tr class="ui-state-default">
                                                    <td width="30" style="text-align: center;">
                                                        <span class="ui-icon ui-icon-arrow-4" style="cursor: move;">
                                                            
                                                        </span>
                                                    </td>
                                                    <td style="padding-right:20px;"><?php echo $sort[$key]?></td>
                                                    <td align="right">
                                                        <div class="controls">
                                                            <fieldset class="radio btn-group">
                                                                <?php 
                                                                 echo JoomSportHelperSelectBox::Radio($key.'_way', $is_field_way,$value,'');
                                                                ?>
                                                            </fieldset>
                                                        </div>
                                                        <input type="hidden" name="sort_columns[]" value="<?php echo $key?>" />
                                                    </td>	
                                            </tr>

                                            <?php 

                                            } 
                                        }
                                        ?>
                                    </tbody>
                                    </table>

                                </td>
                            </tr>
                            <tr>
                                <td width="270">
                                    <?php echo __('Calculate points on Leader and Prediction league views once round is completed', 'joomsport-prediction');?>

                                </td>
                                <td>
                                    <?php 
                                        
                                        echo JoomSportHelperSelectBox::Radio('pred_livecalc', $is_field_yn,$roundcalc,'');
                                    ?>

                                </td>

                            </tr>
                            
                            <tr>
                                <td width="270">
                                    <?php echo __('Private leagues', 'joomsport-prediction');?>

                                </td>
                                <td>
                                    <?php 
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>

                                </td>

                            </tr>
                            <tr>
                                <td>
                                    <?php echo __('Login page link', 'joomsport-prediction');?>
                                </td>
                                <td>
                                    <?=get_site_url();?>/<input type="text" name="login_link" value="<?=isset($settings["login_link"])?$settings["login_link"]:"";?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?php echo __('Page with private leagues shortcode', 'joomsport-prediction');?>
                                    <br />
                                    [jsPrivateLeagues]
                                </td>
                                <td>
                                    <?php
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td width="270">
                                    <?php echo __('Joker match with double points', 'joomsport-prediction');?>

                                </td>
                                <td>
                                    <?php
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>

                                </td>

                            </tr>
                            
                        </table>
                    </div>
                </div>
                 
            </div>
            <div style="clear:both;" ></div>
            <?php
            $is_field = array();
            $is_field[] = JoomSportHelperSelectBox::addOption(0, __("No", "joomsport-prediction"));
            $is_field[] = JoomSportHelperSelectBox::addOption(1, __("Yes", "joomsport-prediction"));
            
            $args = array(
                    'offset'           => 0,
                    'orderby'          => 'title',
                    'order'            => 'ASC',
                    'post_type'        => 'jswprediction_league',
                    'post_status'      => 'publish',
                    'posts_per_page'   => -1,
            );
            $bLeagues = get_posts( $args );
            ?>

            <div style="clear:both;" ></div>
            <div class="jsrespdiv10">
                <div class="jsBepanel">
                    <div class="jsBEheader">
                        <?php echo __('Display predictions blocks on JoomSport match page', 'joomsport-prediction');?>
                    </div>
                    <div class="jsBEsettings">
                        <table class="adminlistsNoBorder">
                            
                            <tr>
                                <td><?php echo __('Top predictions', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('By correct winner', 'joomsport-prediction');?></td>
                                <td>
                                    <?php 
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Both teams to score', 'joomsport-prediction');?></td>
                                <td>
                                    <?php 
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Score over', 'joomsport-prediction');?></td>
                                <td>
                                    <?php 
                                    
                                     echo '<div class="jslinktopro"><a href="http://joomsport.com/web-shop/joomsport-addons/product/68-wordpress-sport-predictions.html">Paid version</a> of Predictions plugin required</div>'; 
                                    ?>
                                </td>
                            </tr>
                        </table>   
                    </div>
                </div>    
            </div>
            <div style="clear:both;" ></div>
            <div class="jsrespdiv10">
                <div class="jsBepanel">
                    <div class="jsBEheader">
                        <?php echo __('Leaderboard columns', 'joomsport-prediction');?>
                    </div>
                    <div class="jsBEsettings">
                        <?php
                        $is_fieldA = array();
                        $is_fieldA[] = JoomSportHelperSelectBox::addOption(0, __("Disable", "joomsport-prediction"));
                        $is_fieldA[] = JoomSportHelperSelectBox::addOption(1, __("Enable", "joomsport-prediction"));
                                    
                        ?>
                        <table class="adminlistsNoBorder">
                            
                            <tr>
                                <td><?php echo __('Avatar', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    echo JoomSportHelperSelectBox::Radio('lbColumns[avatar]', $is_fieldA, (isset($columns_settings["avatar"])?$columns_settings["avatar"]:1),'');
                                    
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Completed predictions', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    echo JoomSportHelperSelectBox::Radio('lbColumns[filled]', $is_fieldA, (isset($columns_settings["filled"])?$columns_settings["filled"]:1),'');
                                    
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Exact', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    echo JoomSportHelperSelectBox::Radio('lbColumns[exact]', $is_fieldA, (isset($columns_settings["exact"])?$columns_settings["exact"]:1),'');
                                    
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Score difference', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    echo JoomSportHelperSelectBox::Radio('lbColumns[diff]', $is_fieldA, (isset($columns_settings["diff"])?$columns_settings["diff"]:1),'');
                                    
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Correct winner', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    echo JoomSportHelperSelectBox::Radio('lbColumns[winside]', $is_fieldA, (isset($columns_settings["winside"])?$columns_settings["winside"]:1),'');
                                    
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Failed', 'joomsport-prediction');?></td>
                                <td>
                                    <?php
                                    echo JoomSportHelperSelectBox::Radio('lbColumns[failed]', $is_fieldA, (isset($columns_settings["failed"])?$columns_settings["failed"]:1),'');
                                    
                                    ?>
                                </td>
                            </tr>
                            
                        </table>   
                    </div>
                </div>    
            </div>
            <?php     ?>
            <div style="clear:both;" ></div>
            <div class="jsrespdiv10">
                <div class="jsBepanel">
                    <div class="jsBEheader">
                        <?php echo __('Default knockout points settings', 'joomsport-prediction');?>
                    </div>
                    <div class="jsBEsettings">
                        <table class="adminlistsNoBorder">
                            
                            <tr>
                                <td><?php echo __('Final', 'joomsport-prediction');?></td>
                                <td>
                                    <input type="number" min="0" name="knscore[1]" value="<?php echo isset($knock_settings["1"])?$knock_settings["1"]:0;?>" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Semifinal', 'joomsport-prediction');?></td>
                                <td>
                                    <input type="number" min="0" name="knscore[2]" value="<?php echo isset($knock_settings["2"])?$knock_settings["2"]:0;?>" />
                                </td>
                            </tr>
                            <tr>
                                <td><?php echo __('Quaterfinal', 'joomsport-prediction');?></td>
                                <td>
                                    <input type="number" min="0" name="knscore[4]" value="<?php echo isset($knock_settings["4"])?$knock_settings["4"]:0;?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>1/8</td>
                                <td>
                                    <input type="number" min="0" name="knscore[8]" value="<?php echo isset($knock_settings["8"])?$knock_settings["8"]:0;?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>1/16</td>
                                <td>
                                    <input type="number" min="0" name="knscore[16]" value="<?php echo isset($knock_settings["16"])?$knock_settings["16"]:0;?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>1/32</td>
                                <td>
                                    <input type="number" min="0" name="knscore[32]" value="<?php echo isset($knock_settings["32"])?$knock_settings["32"]:0;?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>1/64</td>
                                <td>
                                    <input type="number" min="0" name="knscore[64]" value="<?php echo isset($knock_settings["64"])?$knock_settings["64"]:0;?>" />
                                </td>
                            </tr>
                        </table>   
                    </div>
                </div>    
            </div>
            <div style="clear:both;" ></div> 
        </div>
                
       
         
            <div>
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
                <input name="save" class="button-primary" type="submit" value="<?php echo __("Save changes",'joomsport-prediction');?>">
            </div>
            </form>
        </div>
        <?php
    }
}