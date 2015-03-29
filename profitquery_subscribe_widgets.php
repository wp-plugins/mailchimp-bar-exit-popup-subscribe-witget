<?php
/* 
* +--------------------------------------------------------------------------+
* | Copyright (c) ShemOtechnik Profitquery Team shemotechnik@profitquery.com |
* +--------------------------------------------------------------------------+
* | This program is free software; you can redistribute it and/or modify     |
* | it under the terms of the GNU General Public License as published by     |
* | the Free Software Foundation; either version 2 of the License, or        |
* | (at your option) any later version.                                      |
* |                                                                          |
* | This program is distributed in the hope that it will be useful,          |
* | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
* | GNU General Public License for more details.                             |
* |                                                                          |
* | You should have received a copy of the GNU General Public License        |
* | along with this program; if not, write to the Free Software              |
* | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
* +--------------------------------------------------------------------------+
*/
/**
* Plugin Name: Mailchimp Bar + Exit Popup | Subscribe Witget
* Plugin URI: http://profitquery.com/subscribe_witgets.html
* Description: Simply widgets for growth 3x website subscribers, collect customers email, folllowers in social media and all for free.
* Version: 1.0.1
*
* Author: Profitquery Team <support@profitquery.com>
* Author URI: http://profitquery.com/?utm_campaign=subscribe_widgets_wp
*/


$profitquery = get_option('profitquery');

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_PAGE_NAME'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_PAGE_NAME', 'profitquery_subscribe_widgets');

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_CSS_PATH'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_CSS_PATH', 'css/');

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_JS_PATH'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_JS_PATH', 'js/');

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PATH'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PATH', 'images/');

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PREVIEW_PATH'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PREVIEW_PATH', 'preview/');

$pathParts = pathinfo(__FILE__);
$path = $pathParts['dirname'];

if (!defined('PROFITQUERY_SUBSCRIBE_WIDGETS_FILENAME'))
	define('PROFITQUERY_SUBSCRIBE_WIDGETS_FILENAME', $path.'/profitquery_subscribe_widgets.php');



require_once 'profitquery_subscribe_widgets_class.php';
$ProfitQuerySubscribeWidgetsClass = new ProfitQuerySubscribeWidgetsClass();



add_action('init', 'profitquery_subscribe_widgets_init');



function profitquery_subscribe_widgets_init(){
	global $profitquery;	
	if ( !is_admin() && $profitquery[apiKey] && !$profitquery['errorApiKey'] && !$profitquery['aio_widgets_loaded']){
		wp_register_script('lite_profitquery_lib', plugins_url().'/'.PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME.'/js/lite.profitquery.min.js?apiKey='.$profitquery[apiKey]);		
		wp_enqueue_script('lite_profitquery_lib');		
		add_action('wp_footer', 'profitquery_subscribe_widgets_insert_code');
	}
}

/* Adding action links on plugin list*/
function profitquery_subscribe_wordpress_admin_link($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="options-general.php?page=profitquery_subscribe_widgets">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}


function profitquery_subscribe_widgets_insert_code(){
	global $profitquery;	
	global $ProfitQuerySubscribeWidgetsClass;	
	
	$profitquerySmartWidgetsStructure = array();	
	$profitquerySmartWidgetsStructure['followUsFloatingPopup'] = array(
		'disabled'=>1		
	);	
	
	if(!(int)$profitquery[share_widgets_loaded]){
		$profitquerySmartWidgetsStructure['sharingSideBarOptions'] = array(
			'disabled'=>1		
		);
		$profitquerySmartWidgetsStructure['imageSharer'] = array(
			'disabled'=>1		
		);
	} else {
		//Set standart from memory option
		$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[sharingSideBar]);
		$preparedObject[socnet][typeBlock] = 'pq-social-block '.$preparedObject[design];
		$profitquerySmartWidgetsStructure['sharingSideBarOptions'] = array(
			'typeWindow'=>'pq_icons '.$preparedObject[position],
			'socnetIconsBlock'=>$preparedObject[socnet],
			'disabled'=>(int)$preparedObject[disabled],
			'afterProfitLoader'=>$preparedObject[afterProceed]
		);
		
		$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[imageSharer]);
		$profitquerySmartWidgetsStructure['imageSharer'] = array(
			'typeDesign'=>$preparedObject[design].' '.$preparedObject[position],
			'minWidth'=>(int)$preparedObject[minWidth],
			'disabled'=>(int)$preparedObject[disabled],
			'activeSocnet'=>$preparedObject[socnet],
			'afterProfitLoader'=>stripslashes($preparedObject[afterProceed])
		);	
	}
	if(!(int)$profitquery[feedback_widgets_loaded]){
		$profitquerySmartWidgetsStructure['phoneCollectOptions'] = array(
			'disabled'=>1		
		);
		$profitquerySmartWidgetsStructure['contactUsOptions'] = array(
			'disabled'=>1		
		);
	} else {
		$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[callMe]);
		$profitquerySmartWidgetsStructure['phoneCollectOptions'] = array(
			'disabled'=>(int)$preparedObject[disabled],
			'title'=>stripslashes($preparedObject[title]),
			'sub_title'=>stripslashes($preparedObject[sub_title]),
			'img'=>stripslashes($preparedObject[img]),
			'buttonTitle'=>stripslashes($preparedObject[buttonTitle]),
			'typeBookmark'=>stripslashes($preparedObject[position]).' '.stripslashes($preparedObject[loader_background]),			
			'typeWindow'=>stripslashes($preparedObject[typeWindow]).' '.stripslashes($preparedObject[background]).' '.stripslashes($preparedObject[button_color]),
			'afterProfitLoader'=>$preparedObject[afterProceed],
			'emailOption'=>array(
				'to_email'=>stripslashes($profitquery[adminEmail])			
			)
		);
		
		$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[contactUs]);
		$profitquerySmartWidgetsStructure['contactUsOptions'] = array(
			'disabled'=>(int)$preparedObject[disabled],
			'title'=>stripslashes($preparedObject[title]),
			'sub_title'=>stripslashes($preparedObject[sub_title]),
			'img'=>stripslashes($preparedObject[img]),
			'buttonTitle'=>stripslashes($preparedObject[buttonTitle]),
			'typeBookmark'=>stripslashes($preparedObject[position]).' '.stripslashes($preparedObject[loader_background]),			
			'typeWindow'=>stripslashes($preparedObject[typeWindow]).' '.stripslashes($preparedObject[background]).' '.stripslashes($preparedObject[button_color]),
			'afterProfitLoader'=>$preparedObject[afterProceed],
			'emailOption'=>array(
				'to_email'=>stripslashes($profitquery[adminEmail])			
			)
		);
	}
	
	$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[subscribeBar]);
	$profitquerySmartWidgetsStructure['subscribeBarOptions'] = array(
		'title'=>stripslashes($preparedObject[title]),		
		'disabled'=>(int)$preparedObject[disabled],
		'afterProfitLoader'=>$preparedObject[afterProceed],
		'typeWindow'=>'pq_bar '.stripslashes($preparedObject[position]).' '.stripslashes($preparedObject[background]).' '.stripslashes($preparedObject[button_color]),
		'inputEmailTitle'=>stripslashes($preparedObject[inputEmailTitle]),
		'buttonTitle'=>stripslashes($preparedObject[buttonTitle]),
		'formAction'=>stripslashes($profitquery[subscribeProviderUrl])
	);
	
	$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[subscribeExit]);	
	$profitquerySmartWidgetsStructure['subscribeExitPopupOptions'] = array(
		'title'=>stripslashes($preparedObject[title]),
		'sub_title'=>stripslashes($preparedObject[sub_title]),		
		'img'=>stripslashes($preparedObject[img]),
		'disabled'=>(int)$preparedObject[disabled],
		'afterProfitLoader'=>$preparedObject[afterProceed],
		'typeWindow'=>stripslashes($preparedObject[typeWindow]).' '.stripslashes($preparedObject[background]).' '.stripslashes($preparedObject[button_color]),
		'inputEmailTitle'=>stripslashes($preparedObject[inputEmailTitle]),
		'buttonTitle'=>stripslashes($preparedObject[buttonTitle]),
		'formAction'=>stripslashes($profitquery[subscribeProviderUrl])
	);
	
	$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[thankPopup]);
	$profitquerySmartWidgetsStructure['thankPopupOptions'] = array(
		'title'=>stripslashes($preparedObject[title]),
		'sub_title'=>stripslashes($preparedObject[sub_title]),
		'typeWindow'=>stripslashes($preparedObject[background]),
		'img'=>stripslashes($preparedObject[img]),
		'buttonTitle'=>stripslashes($preparedObject[buttonTitle])
	);
	
	$preparedObject = $ProfitQuerySubscribeWidgetsClass->prepare_sctructure_product($profitquery[follow]);
	$profitquerySmartWidgetsStructure['followUsOptions'] = array(
		'title'=>stripslashes($preparedObject[title]),
		'sub_title'=>stripslashes($preparedObject[sub_title]),
		'typeWindow'=>stripslashes($preparedObject[background]),
		'socnetIconsBlock'=>$preparedObject[follow_socnet]
	);	

	
	print "
	<script>
	profitquery.loadFunc.callAfterPQInit(function(){
		var smartWidgetsBoxObject = ".json_encode($profitquerySmartWidgetsStructure).";	
		profitquery.widgets.smartWidgetsBox(smartWidgetsBoxObject);	
	});
	</script>
	";
}

add_filter('plugin_action_links', 'profitquery_subscribe_wordpress_admin_link', 10, 2);