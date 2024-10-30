<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class jsPredictionHelper
{
    public static function JsHeader($options)
    {

        $kl = '';
        if (classJsportRequest::get('tmpl') != 'component') {
            $kl .= '<div class="">';
            $kl .= '<nav class="navbar navbar-default navbar-static-top" role="navigation">';
            $kl .= '<div class="navbar-header navHeadFull">';

            $brand = '';

            $kl .= '<ul class="nav navbar-nav pull-right navSingle">';
            
            if (isset($options['prleaders']) && $options['prleaders']) {
                $link = get_permalink($options['prleaders']);
                $prl = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
                if($prl){
                    $link = add_query_arg( 'prl', $prl, $link );
                }
                //$link = add_query_arg( 'action', 'calendar', $link );
                $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-stand"></i>'.__('Leaderboard','joomsport-prediction').'</a>';
            }
            
            if (isset($options['userleague']) && $options['userleague']) {
                $link = get_permalink($options['userleague']);
                $link = add_query_arg( 'action', 'rounds', $link );
                $prl = isset($_REQUEST['prl'])?intval($_REQUEST['prl']):0;
                if($prl){
                    $link = add_query_arg( 'prl', $prl, $link );
                }
                $kl .= '<a class="btn btn-default" href="'.$link.'" title=""><i class="js-itlist"></i>'.__('Round list','joomsport-prediction').'</a>';
            }
            
            $kl .= '</ul></div></nav></div>';
        }
        //$kl .= self::JsHistoryBox($options);
        $kl .= self::JsTitleBox($options);
        $kl .= "<div class='jsClear'></div>";

        return $kl;
    }

    public static function JsTitleBox($options)
    {
        $kl = '';
        $kl .= '<div class="heading col-xs-12 col-lg-12">
                    <div class="heading col-xs-6 col-lg-6">
                        <!--h2>
                           
                        </h2-->
                    </div>
                    <div class="selection col-xs-6 col-lg-6 pull-right">
                        <form method="post">
                            <div class="data">
                                '.(isset($options['tourn']) ? $options['tourn'] : '').'
                                <input type="hidden" name="jscurtab" value="" />    
                            </div>
                        </form>
                    </div>
                </div>';

        return $kl;
    }

    public static function JsHistoryBox($options)
    {
        $kl = '<div class="history col-xs-12 col-lg-12">
          <ol class="breadcrumb">
            <li><a href="javascript:void(0);" onclick="history.back(-1);" title="[Back]">
                <i class="fa fa-long-arrow-left"></i>[Back]
            </a></li>
          </ol>
          <div class="div_for_socbut">'.(isset($options['print']) ? '' : '').'<div class="jsClear"></div></div>
        </div>';

        return $kl;
    }

    

    public static function isMobile()
    {
        return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT']);
    }
    
}
