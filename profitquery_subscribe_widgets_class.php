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
* @category Class
* @package  Wordpress_Plugin
* @author   ShemOtechnik Profitquery Team <support@profitquery.com>
* @license  http://www.php.net/license/3_01.txt  PHP License 3.01
* @version  SVN: 2.1.7
*/



class ProfitQuerySubscribeWidgetsClass
{
	/** Profitquery Settings **/
    var $_options;
	function ProfitQuerySubscribeWidgetsClass(){
		$this->__construct();
	}
	/**
     * Initializes the plugin.
     *
     * @param null     
     * @return null
     * */
    function __construct()
    {
		$this->_options = $this->getSettings();		
        add_action('admin_menu', array($this, 'ProfitqueryPluginMenu'));		
		// Deactivation
        register_deactivation_hook(
            PROFITQUERY_SUBSCRIBE_WIDGETS_FILENAME,
            array($this, 'pluginDeactivation')
        );
		// activation
        register_activation_hook(
            PROFITQUERY_SUBSCRIBE_WIDGETS_FILENAME,
            array($this, 'pluginActivation')
        );
    }
	
	/*
		isPLuginPage
		return boolean
	*/
	function isPluginPage(){
		$ret = false;
		if(strstr($_SERVER[REQUEST_URI], 'wp-admin/plugins.php')){
			$ret = true;
		}		
		return $ret;
	}
	
	/**
     * Functions to execute on plugin activation
     * 
     * @return null
     */
    public function pluginActivation()
    {
        if (get_option('profitquery')) {
			$this->_options[subscribe_widgets_loaded] = 1;
			if((int)$this->_options[subscribePluginRateUs][timeActivation] == 0){			
				$this->_options[subscribePluginRateUs][timeActivation] = time();
			}
			update_option('profitquery', $this->_options);
        }
    }
	
	 /**
     * Functions to execute on plugin deactivation
     * 
     * @return null
     */
    public function pluginDeactivation()
    {
        if (get_option('profitquery')) {
			$this->_options[subscribe_widgets_loaded] = 0;
			update_option('profitquery', $this->_options);
        }
    }
	
	
	/*
	 *	parseSubscribeProviderForm
	 *	
	 *	@return array
	 */
	function parseSubscribeProviderForm()
	{		
		if($_POST[subscribeProvider] == 'mailchimp'){
			$return = $this->_parseMailchimpForm();
		}
		if($_POST[subscribeProvider] == 'aweber'){
			$return = $this->_parseAweberForm();
		}
		return $return;
	}
	
	
	function _parseMailchimpForm()
	{
		$txt = trim($_POST[subscribeProviderFormContent]);		
		$array = array();
		$matches = array();
		if($txt){
			$txt = stripslashes($txt);
			$txt = str_replace("\t", ' ', $txt);
			$txt = str_replace("\r", '', $txt);
			$txt = str_replace("\n", '', $txt);
			$txt = str_replace("  ", " ", $txt);
			$txt = str_replace("  ", " ", $txt);			
			preg_match_all('/(\<)(.*)(form)(.*)(action=)(.*)([\"\'])(.*)([\"\'])(.*)(\>)/Ui', $txt, $matches);
			$array[formAction] = trim($matches[8][0]);
			if(!strstr($array[formAction], 'list-manage.com')){
				$array[formAction] = '';
				$array[is_error] = 1;
			}			
		}
		return $array;
	}
	
	function _parseAweberForm()
	{
		$txt = trim($_POST[subscribeProviderFormContent]);		
		$array = array();
		$matches = array();
		$hiddenField = array();
		if($txt){
			$txt = stripslashes($txt);
			$txt = str_replace("\t", ' ', $txt);
			$txt = str_replace("\r", '', $txt);
			$txt = str_replace("\n", '', $txt);
			$txt = str_replace("  ", " ", $txt);
			$txt = str_replace("  ", " ", $txt);			
			preg_match_all('/(\<)(.*)(form)(.*)(action=)(.*)([\"\'])(.*)([\"\'])(.*)(\>)/Ui', $txt, $matches);
			$array[formAction] = trim($matches[8][0]);
			if(!strstr($array[formAction], 'aweber.com')){
				$array[formAction] = '';
				$array[is_error] = 1;
			} else {
				preg_match_all('/(\<)(.*)(input)(.*)(hidden)(.*)(name=)(.*)([\"\'])(.*)([\"\'])(.*)(value=)(.*)([\"\'])(.*)([\"\'])(.*)(\>)/Ui', $txt, $matches);
				foreach((array)$matches[10] as $k => $v){
					$hiddenField[$v] = $matches[16][$k];
				}
				if($hiddenField[meta_web_form_id]){
					$array[hidden] = $hiddenField;
				} else {
					$array[formAction] = '';
					$array[is_error] = 1;
				}
			}
		}
		return $array;
	}
	
	function printr($array)
	{
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	}
	
	function is_follow_enabled_and_not_setup()
	{
		$profitquery = $this->_options;
		$return = false;
		$ifSetFollowAfterProceed = false;
		$isFollowSocnetSetuped = false;
		
		if((int)$profitquery[subscribeExit][disabled] == 0 && (int)$profitquery[subscribeExit][afterProceed][follow] == 1){
			$ifSetFollowAfterProceed = true;
		}
		if((int)$profitquery[subscribeBar][disabled] == 0 && (int)$profitquery[subscribeBar][afterProceed][follow] == 1){
			$ifSetFollowAfterProceed = true;
		}
		
		
		if($ifSetFollowAfterProceed){
			foreach((array)$profitquery[follow][follow_socnet] as $soc_id => $v){
				if($v){
					$isFollowSocnetSetuped = true;
				}
			}
			if(!$isFollowSocnetSetuped){
				$return = true;
			}
		}
			
		return $return;	
	}
	
	function prepare_sctructure_product($data)
	{
		$return = $data;	
		//After Proceed		
		if(isset($data[afterProceed])){		
			unset($return[afterProceed]);
			if((int)$data[afterProceed][follow] == 1 || (int)$data[afterProceed][thank] == 1){
				if((int)$data[afterProceed][follow] == 1){
					$return[afterProceed] = 'follow';
				}
				if((int)$data[afterProceed][thank] == 1){
					$return[afterProceed] = 'thank';
				}
			} else {
				$return[afterProceed] = '';
			}
		}
		//socnet
		if(isset($data[socnet])){
			unset($return[socnet]);
			foreach((array)$data[socnet] as $k => $v){
				if($v){
					$return[socnet][$k] = $v;
				}
			}
			
		}
		
		//socnet
		if(isset($data[follow_socnet])){
			unset($return[follow_socnet]);
			foreach((array)$data[follow_socnet] as $k => $v){
				if($v){
					if($k == 'FB') $return[follow_socnet][$k][url] = 'https://facebook.com/'.$v;
					if($k == 'TW') $return[follow_socnet][$k][url] = 'https://twitter.com/'.$v;
					if($k == 'GP') $return[follow_socnet][$k][url] = 'https://plus.google.com/'.$v;
					if($k == 'PI') $return[follow_socnet][$k][url] = 'https://pinterest.com/'.$v;
					if($k == 'VK') $return[follow_socnet][$k][url] = 'http://vk.com/'.$v;
					if($k == 'RSS') $return[follow_socnet][$k][url] = $v;				
					if($k == 'IG') $return[follow_socnet][$k][url] = 'http://instagram.com/'.$v;						
				}
			}
			
		}
		
		//img imgUrl
		if(isset($data[img]) || isset($data[imgUrl])){
			unset($return[img]);
			unset($return[imgUrl]);
			if($data[img] == 'custom' && $data[imgUrl]){
				$return[img] = $data[imgUrl];
			}elseif($data[img] != 'custom' && $data[img] != ''){
				$return[img] = plugins_url('images/'.$data[img], __FILE__);;
			} else {
				$return[img] = '';
			}
		}
		
		//design
		if(isset($data[design])){
			unset($return[design]);
			if($data[design][form] == 'square' || $data[design][form] == 'pq_square') $data[design][form]='';
			if(!strstr($data[design][form], 'pq_') && trim($data[design][form])) $data[design][form] = 'pq_'.$data[design][form];
			$return[design] = $data[design][size]." ".$data[design][form]." ".$data[design][color]." ".$data[design][shadow];
		}
		
		return $return;
	}
	
	function is_subscribe_enabled(){
		$return = false;
		if((int)$this->_options[subscribeBar][disabled] == 0 || (int)$this->_options[subscribeExit][disabled] == 0){
			$return = true;
		}
		return $return;
	}
	
	/**
     * Adds sub menu page to the WP settings menu
     * 
     * @return null
     */
    function ProfitqueryPluginMenu()
    {		
        add_options_page(
            'Subscribe Widgets', 'Subscribe Widgets', 
            'manage_options', PROFITQUERY_SUBSCRIBE_WIDGETS_PAGE_NAME,
            array($this, 'ProfitqueryOptions')
        );		
    }
	
	 /**
     * Get the plugin's settings page url
     * 
     * @return string
     */
    function getSettingsPageUrl()
    {
        return admin_url("options-general.php?page=" . PROFITQUERY_SUBSCRIBE_WIDGETS_PAGE_NAME);
    }
	
	function setDefaultProductData(){
		//Other default params		
				
		if(!$this->_options[follow]) $this->_options[follow][disabled] = 1;
		
		if(!$this->_options[thankPopup]){
			$this->_options[thankPopup][disabled] = 1;
			$this->_options['thankPopup']['title'] = 'Thank You';
			$this->_options['thankPopup']['buttonTitle'] = 'Close';
			$this->_options['thankPopup']['background'] = 'bg_grey';
			$this->_options['thankPopup']['img'] = 'img_10.png';
			$this->_options[thankPopup][animation] = 'bounceInDown';
			$this->_options[thankPopup][overlay] = 'over_white';
		}
		
		if(!$this->_options[subscribeBar]){
			$this->_options[subscribeBar][disabled] = 1;
			$this->_options[subscribeBar][background] = 'bg_red';
			$this->_options[subscribeBar][button_color] = 'btn_black';			
			$this->_options[subscribeBar][animation] = 'bounce';			
		}
		
		if(!$this->_options[subscribeExit]){
			$this->_options[subscribeExit][disabled] = 1;
			$this->_options[subscribeExit][background] = 'bg_red';
			$this->_options[subscribeExit][button_color] = 'btn_black invert';
			$this->_options[subscribeExit][typeWindow] = 'pq_medium';
			$this->_options[subscribeExit][animation] = 'tada';						
			$this->_options[subscribeExit][overlay] = 'over_black_lt';
		}
		
		
		$this->_options[subscribe_widgets_loaded] = 1;
		update_option('profitquery', $this->_options);
	}	
	
	
	
	/**
     *  Get LitePQ Share Image settings array
     * 
     *  @return string
     */
    function getSettings()
    {
        return get_option('profitquery');
    }
	
	 /**
     * Manages the WP settings page
     * 
     * @return null
     */
    function ProfitqueryOptions()
    {
        if (!current_user_can('manage_options')) {
            wp_die(
                __('You do not have sufficient permissions to access this page.')
            );
        }
		echo "
			<link rel='stylesheet'  href='http://fonts.googleapis.com/css?family=PT+Sans+Narrow:400,700&amp;subset=latin,cyrillic' type='text/css' media='all' />
			<link rel='stylesheet'  href='".plugins_url()."/".PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME."/".PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_CSS_PATH."profitquery_smart_widgets_wordpress_v2.css' type='text/css' media='all' />
			<link rel='stylesheet'  href='".plugins_url()."/".PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME."/".PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_CSS_PATH."icons.css' type='text/css' media='all' />
		<noscript>				
				<p>Please enable JavaScript in your browser.</p>				
		</noscript>
		";				
				
		
		/*POST*/
		
		if($_POST[action] == 'editAdditionalOptions'){						
			if($_POST[additionalOptions][enableGA] == 'on') $this->_options[additionalOptions][enableGA] = 1; else $this->_options[additionalOptions][enableGA] = 0;			
			update_option('profitquery', $this->_options);
		}
						
		if($_POST[action] == 'subscribeProviderSetup'){
			if(isset($_POST[subscribeProvider])){
				unset($this->_options['subscribeProviderUrl']);
				$this->_options['subscribeProvider'] = sanitize_text_field($_POST[subscribeProvider]);
				if($_POST[subscribeProviderFormContent]){
					unset($this->_options['subscribeProviderOption']);					
					$this->_options['subscribeProviderOption'][$this->_options['subscribeProvider']] = $this->parseSubscribeProviderForm();					
				}else{					
					$this->_options['subscribeProviderOption'][$this->_options['subscribeProvider']][is_error] = 1;
				}				
			}
			update_option('profitquery', $this->_options);
		}
		
		
		if($_POST[action] == 'editAdditionalData'){
			//follow
			if($_POST[follow]){				
				if(trim($_POST[follow][title])) $this->_options['follow']['title'] = sanitize_text_field($_POST[follow][title]); else $this->_options['follow']['title'] = '';
				if(trim($_POST[follow][sub_title])) $this->_options['follow']['sub_title'] = sanitize_text_field($_POST[follow][sub_title]); else $this->_options['follow']['sub_title'] = '';
				if(trim($_POST[follow][background])) $this->_options['follow']['background'] = sanitize_text_field($_POST[follow][background]); else $this->_options['follow']['background'] = '';
				if(trim($_POST[follow][animation])) $this->_options['follow']['animation'] = sanitize_text_field($_POST[follow][animation]); else $this->_options['follow']['animation'] = '';
				if(trim($_POST[follow][overlay])) $this->_options['follow']['overlay'] = sanitize_text_field($_POST[follow][overlay]); else $this->_options['follow']['overlay'] = '';
				if($_POST[follow][follow_socnet]){
					if(trim($_POST[follow][follow_socnet][FB]) != '') $this->_options[follow][follow_socnet][FB] = sanitize_text_field($_POST[follow][follow_socnet][FB]); else $this->_options[follow][follow_socnet][FB] = '';
					if(trim($_POST[follow][follow_socnet][TW]) != '') $this->_options[follow][follow_socnet][TW] = sanitize_text_field($_POST[follow][follow_socnet][TW]); else $this->_options[follow][follow_socnet][TW] = '';
					if(trim($_POST[follow][follow_socnet][GP]) != '') $this->_options[follow][follow_socnet][GP] = sanitize_text_field($_POST[follow][follow_socnet][GP]); else $this->_options[follow][follow_socnet][GP] = '';
					if(trim($_POST[follow][follow_socnet][PI]) != '') $this->_options[follow][follow_socnet][PI] = sanitize_text_field($_POST[follow][follow_socnet][PI]); else $this->_options[follow][follow_socnet][PI] = '';
					if(trim($_POST[follow][follow_socnet][VK]) != '') $this->_options[follow][follow_socnet][VK] = sanitize_text_field($_POST[follow][follow_socnet][VK]); else $this->_options[follow][follow_socnet][VK] = '';
					if(trim($_POST[follow][follow_socnet][OD]) != '') $this->_options[follow][follow_socnet][OD] = sanitize_text_field($_POST[follow][follow_socnet][OD]); else $this->_options[follow][follow_socnet][OD] = '';
					if(trim($_POST[follow][follow_socnet][RSS]) != '') $this->_options[follow][follow_socnet][RSS] = sanitize_text_field($_POST[follow][follow_socnet][RSS]); else $this->_options[follow][follow_socnet][RSS] = '';
					if(trim($_POST[follow][follow_socnet][IG]) != '') $this->_options[follow][follow_socnet][IG] = sanitize_text_field($_POST[follow][follow_socnet][IG]); else $this->_options[follow][follow_socnet][IG] = '';
				}
			}
			
			//thankPopup
			if($_POST[thankPopup]){
				if(trim($_POST[thankPopup][title])) $this->_options['thankPopup']['title'] = sanitize_text_field($_POST[thankPopup][title]); else $this->_options['thankPopup']['title'] = '';
				if(trim($_POST[thankPopup][sub_title])) $this->_options['thankPopup']['sub_title'] = sanitize_text_field($_POST[thankPopup][sub_title]); else $this->_options['thankPopup']['sub_title'] = '';
				if(trim($_POST[thankPopup][buttonTitle])) $this->_options['thankPopup']['buttonTitle'] = sanitize_text_field($_POST[thankPopup][buttonTitle]); else $this->_options['thankPopup']['buttonTitle'] = '';
				if(trim($_POST[thankPopup][background])) $this->_options['thankPopup']['background'] = sanitize_text_field($_POST[thankPopup][background]); else $this->_options['thankPopup']['background'] = '';
				if(trim($_POST[thankPopup][img])) $this->_options['thankPopup']['img'] = sanitize_text_field($_POST[thankPopup][img]); else $this->_options['thankPopup']['img'] = '';
				if(trim($_POST[thankPopup][imgUrl])) $this->_options['thankPopup']['imgUrl'] = sanitize_text_field($_POST[thankPopup][imgUrl]); else $this->_options['thankPopup']['imgUrl'] = '';				
				if(trim($_POST[thankPopup][animation])) $this->_options['thankPopup']['animation'] = sanitize_text_field($_POST[thankPopup][animation]); else $this->_options['thankPopup']['animation'] = '';				
				if(trim($_POST[thankPopup][overlay])) $this->_options['thankPopup']['overlay'] = sanitize_text_field($_POST[thankPopup][overlay]); else $this->_options['thankPopup']['overlay'] = '';				
			}												
			
			//subscribeBar
			if($_POST[subscribeBar][afterProceed][follow] == 'on'){
				$this->_options['subscribeBar']['afterProceed']['follow'] = 1;
				$this->_options['subscribeBar']['afterProceed']['thank'] = 0;
			} elseif($_POST[subscribeBar][afterProceed][thank] == 'on'){
				$this->_options['subscribeBar']['afterProceed']['follow'] = 0;
				$this->_options['subscribeBar']['afterProceed']['thank'] = 1;
			} else {
				$this->_options['subscribeBar']['afterProceed']['follow'] = 0;
				$this->_options['subscribeBar']['afterProceed']['thank'] = 0;
			}						
			
			//subscribeExit
			if($_POST[subscribeExit][afterProceed][follow] == 'on'){
				$this->_options['subscribeExit']['afterProceed']['follow'] = 1;
				$this->_options['subscribeExit']['afterProceed']['thank'] = 0;
			} elseif($_POST[subscribeExit][afterProceed][thank] == 'on'){
				$this->_options['subscribeExit']['afterProceed']['follow'] = 0;
				$this->_options['subscribeExit']['afterProceed']['thank'] = 1;
			} else {
				$this->_options['subscribeExit']['afterProceed']['follow'] = 0;
				$this->_options['subscribeExit']['afterProceed']['thank'] = 0;
			}			
			
			update_option('profitquery', $this->_options);
			echo '
			<div id="successPQBlock" style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(151, 255, 0, 0.5); text-align: center;">
					<p style="color: rgb(104, 174, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">Data changed!</p>
			</div>
			<script>
				setTimeout(function(){document.getElementById("successPQBlock").style.display="none";}, 5000);
				</script>
			';
		}
		
		if($_POST[action] == 'edit'){			
			//subscribeBar
			if($_POST[subscribeBar]){
				if($_POST[subscribeBar][enabled] == 'on') $this->_options['subscribeBar']['disabled'] = 0; else $this->_options['subscribeBar']['disabled'] = 1;
				if(trim($_POST[subscribeBar][position])) $this->_options['subscribeBar']['position'] = sanitize_text_field($_POST[subscribeBar][position]); else $this->_options['subscribeBar']['position'] = '';
				if(trim($_POST[subscribeBar][typeWindow])) $this->_options['subscribeBar']['typeWindow'] = sanitize_text_field($_POST[subscribeBar][typeWindow]); else $this->_options['subscribeBar']['typeWindow'] = '';
				if(trim($_POST[subscribeBar][title])) $this->_options['subscribeBar']['title'] = sanitize_text_field($_POST[subscribeBar][title]); else $this->_options['subscribeBar']['title'] = '';
				if(trim($_POST[subscribeBar][mobile_title])) $this->_options['subscribeBar']['mobile_title'] = sanitize_text_field($_POST[subscribeBar][mobile_title]); else $this->_options['subscribeBar']['mobile_title'] = '';
				if(trim($_POST[subscribeBar][inputEmailTitle])) $this->_options['subscribeBar']['inputEmailTitle'] = sanitize_text_field($_POST[subscribeBar][inputEmailTitle]); else $this->_options['subscribeBar']['inputEmailTitle'] = '';
				if(trim($_POST[subscribeBar][inputNameTitle])) $this->_options['subscribeBar']['inputNameTitle'] = sanitize_text_field($_POST[subscribeBar][inputNameTitle]); else $this->_options['subscribeBar']['inputNameTitle'] = '';
				if(trim($_POST[subscribeBar][buttonTitle])) $this->_options['subscribeBar']['buttonTitle'] = sanitize_text_field($_POST[subscribeBar][buttonTitle]); else $this->_options['subscribeBar']['buttonTitle'] = '';
				if(trim($_POST[subscribeBar][background])) $this->_options['subscribeBar']['background'] = sanitize_text_field($_POST[subscribeBar][background]); else $this->_options['subscribeBar']['background'] = '';
				if(trim($_POST[subscribeBar][button_color])) $this->_options['subscribeBar']['button_color'] = sanitize_text_field($_POST[subscribeBar][button_color]); else $this->_options['subscribeBar']['button_color'] = '';
				if(trim($_POST[subscribeBar][size])) $this->_options['subscribeBar']['size'] = sanitize_text_field($_POST[subscribeBar][size]); else $this->_options['subscribeBar']['size'] = '';								
				if(trim($_POST[subscribeBar][animation])) $this->_options['subscribeBar']['animation'] = sanitize_text_field($_POST[subscribeBar][animation]); else $this->_options['subscribeBar']['animation'] = '';
				
				if($_POST[subscribeBar][afterProceed]){
					if($_POST[subscribeBar][afterProceed][follow] == 'on'){
						$this->_options['subscribeBar']['afterProceed']['follow'] = 1;
						$this->_options['subscribeBar']['afterProceed']['thank'] = 0;
					} elseif($_POST[subscribeBar][afterProceed][thank] == 'on'){
						$this->_options['subscribeBar']['afterProceed']['follow'] = 0;
						$this->_options['subscribeBar']['afterProceed']['thank'] = 1;
					} else {
						$this->_options['subscribeBar']['afterProceed']['follow'] = 0;
						$this->_options['subscribeBar']['afterProceed']['thank'] = 0;
					}									
				} else {
					$this->_options['subscribeBar']['afterProceed']['follow'] = 0;
					$this->_options['subscribeBar']['afterProceed']['thank'] = 0;
				}
			}
			
			//subscribeExit
			if($_POST[subscribeExit]){
				if($_POST[subscribeExit][enabled] == 'on') $this->_options['subscribeExit']['disabled'] = 0; else $this->_options['subscribeExit']['disabled'] = 1;
				if(trim($_POST[subscribeExit][position])) $this->_options['subscribeExit']['position'] = sanitize_text_field($_POST[subscribeExit][position]); else $this->_options['subscribeExit']['position'] = '';
				if(trim($_POST[subscribeExit][typeWindow])) $this->_options['subscribeExit']['typeWindow'] = sanitize_text_field($_POST[subscribeExit][typeWindow]); else $this->_options['subscribeExit']['typeWindow'] = '';
				if(trim($_POST[subscribeExit][title])) $this->_options['subscribeExit']['title'] = sanitize_text_field($_POST[subscribeExit][title]); else $this->_options['subscribeExit']['title'] = '';
				if(trim($_POST[subscribeExit][sub_title])) $this->_options['subscribeExit']['sub_title'] = sanitize_text_field($_POST[subscribeExit][sub_title]); else $this->_options['subscribeExit']['sub_title'] = '';
				if(trim($_POST[subscribeExit][inputEmailTitle])) $this->_options['subscribeExit']['inputEmailTitle'] = sanitize_text_field($_POST[subscribeExit][inputEmailTitle]); else $this->_options['subscribeExit']['inputEmailTitle'] = '';
				if(trim($_POST[subscribeExit][inputNameTitle])) $this->_options['subscribeExit']['inputNameTitle'] = sanitize_text_field($_POST[subscribeExit][inputNameTitle]); else $this->_options['subscribeExit']['inputNameTitle'] = '';
				if(trim($_POST[subscribeExit][buttonTitle])) $this->_options['subscribeExit']['buttonTitle'] = sanitize_text_field($_POST[subscribeExit][buttonTitle]); else $this->_options['subscribeExit']['buttonTitle'] = '';
				if(trim($_POST[subscribeExit][background])) $this->_options['subscribeExit']['background'] = sanitize_text_field($_POST[subscribeExit][background]); else $this->_options['subscribeExit']['background'] = '';
				if(trim($_POST[subscribeExit][button_color])) $this->_options['subscribeExit']['button_color'] = sanitize_text_field($_POST[subscribeExit][button_color]); else $this->_options['subscribeExit']['button_color'] = '';
				if(trim($_POST[subscribeExit][img])) $this->_options['subscribeExit']['img'] = sanitize_text_field($_POST[subscribeExit][img]); else $this->_options['subscribeExit']['img'] = '';
				if(trim($_POST[subscribeExit][imgUrl])) $this->_options['subscribeExit']['imgUrl'] = sanitize_text_field($_POST[subscribeExit][imgUrl]); else $this->_options['subscribeExit']['imgUrl'] = '';				
				if(trim($_POST[subscribeExit][animation])) $this->_options['subscribeExit']['animation'] = sanitize_text_field($_POST[subscribeExit][animation]); else $this->_options['subscribeExit']['animation'] = '';				
				if(trim($_POST[subscribeExit][overlay])) $this->_options['subscribeExit']['overlay'] = sanitize_text_field($_POST[subscribeExit][overlay]); else $this->_options['subscribeExit']['overlay'] = '';				
				if($_POST[subscribeExit][afterProceed]){
					if($_POST[subscribeExit][afterProceed][follow] == 'on'){
						$this->_options['subscribeExit']['afterProceed']['follow'] = 1;
						$this->_options['subscribeExit']['afterProceed']['thank'] = 0;
					} elseif($_POST[subscribeExit][afterProceed][thank] == 'on'){
						$this->_options['subscribeExit']['afterProceed']['follow'] = 0;
						$this->_options['subscribeExit']['afterProceed']['thank'] = 1;
					} else {
						$this->_options['subscribeExit']['afterProceed']['follow'] = 0;
						$this->_options['subscribeExit']['afterProceed']['thank'] = 0;
					}									
				}else{
					$this->_options['subscribeExit']['afterProceed']['follow'] = 0;
					$this->_options['subscribeExit']['afterProceed']['thank'] = 0;
				}
			}
						
						
			update_option('profitquery', $this->_options);
			echo '
			<div id="successPQBlock" style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(151, 255, 0, 0.5); text-align: center;">
					<p style="color: rgb(104, 174, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">Data changed!</p>
			</div>
			<script>
				setTimeout(function(){document.getElementById("successPQBlock").style.display="none";}, 5000);
				</script>
			';
		}
		
		//update_option('profitquery', '');
				
		
		//save api key
		if(trim($_POST[apiKey]) != '' || trim($_GET[apiKey]) != ''){						
			if(!trim($this->_options['apiKey'])){				
				//DEFAULT OPTIONS				
				$this ->setDefaultProductData();
			}			
			if(trim($_POST[apiKey]) != '') $this->_options['apiKey'] = sanitize_text_field($_POST[apiKey]);
			if(trim($_GET[apiKey]) != '') $this->_options['apiKey'] = sanitize_text_field($_GET[apiKey]);			
			$this->_options['errorApiKey'] = 0;				
			update_option('profitquery', $this->_options);
			echo '			
				<div id="successPQBlock" style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(151, 255, 0, 0.5); text-align: center;">
					<p style="color: rgb(104, 174, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">API Key Was Saved!</p>
				</div>
				<script>
				setTimeout(function(){document.getElementById("successPQBlock").style.display="none";}, 5000);
				</script>
			';			
		} else {
			echo '			
				<div style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(151, 255, 0, 0.5); text-align: center;">
					<p style="color: rgb(104, 174, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;"><a href="'.$this->getSettingsPageUrl().'&action=changeApiKey">Edit Api Key</a></p>
				</div>				
			';	
		}				
		
		//save api key
		if(!trim($this->_options['apiKey']) || $_GET[action] == 'changeApiKey' || (int)$this->_options['errorApiKey'] == 1){
			$redirect_url = str_replace(".", "%2E", urlencode($this->getSettingsPageUrl().'&action=changeApiKey'));
			if((int)$_GET[is_error] == 1){
				$this->_options['errorApiKey'] = 1;
				update_option('profitquery', $this->_options);
				echo '
					<div id="errorPQBlock" style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(242, 20, 67, 0.5); text-align: center;">
					 <p style="color: rgb(174, 0, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">Wrong Lite Profitquery API Key. <a href="http://litelib.profitquery.com/cms-sign-in/?domain='.$this->getDomain().'&cms=wp&ae='.get_settings('admin_email').'&redirect='.
                     str_replace(".", "%2E", urlencode($this->getSettingsPageUrl())).'" style="text-decoration: none;" target="_getLitePQApiKey">Get API Key</a></p>
					</div>					
					<script>
					setTimeout(function(){document.getElementById("errorPQBlock").style.display="none";}, 10000);
					</script>
				';
			} elseif((int)$this->_options['errorApiKey'] == 1){
				echo '
						<div style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(242, 20, 67, 0.5); text-align: center;">
						 <p style="color: rgb(174, 0, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">Wrong Lite Profitquery API Key.</p>
						</div>						
					';
			}					
			echo '			
			<div style="text-align: center; margin: 0 auto;">			
			<section style="margin: 20px auto 100px; width: 60%; ">
			<div style="overflow: hidden; margin: 0 0 40px;">
			  <h1 class="pq" style="font-family: pt sans narrow; font-size: 30px; color: #7A7A7A; font-weight: normal; display: inline-block; float: left; margin: 0; line-height: 40px;">Start to use AIO Widgets by Profitquery</h1>
			  <p style="font-family: arial; font-size: 16px; color: #929292; display: inline-block; float: right; margin: 0; height: 40px; padding: 10px 0 0; box-sizing: border-box;">Need help? <a style="color: #222222; text-decoration: none;" href="http://profitquery.com/subscribe_witgets.html" target="_pq_image_sharer_wordpress">Check instructions <img src="'.plugins_url('images/icon.png', __FILE__).'" style="margin: 0 0 -5px;" /></a></p>
			 </div>				
				<p style="font-family: arial; font-size: 16px; color: #A9A9A9; margin: 16px 0 50px;">To start using the AIO Widgets By Profitquery, we first need your Profitquery Lite API Key.</p>
				<img src="'.plugins_url('images/logo.png', __FILE__).'" style="display: block; margin: 0px auto;" />
				<form action="'.$this->getSettingsPageUrl().'" method="post" onsubmit="checkApiKey();return true;">
					<label><p style="font-family: arial; font-size: 16px; color: #A9A9A9; margin: 30px 0 5px;">Lite Profitquery API Key</p>
						<input type="text" name="apiKey" id="lPQApiKeyInput" value="'.$this->_options['apiKey'].'"  style="display: block; margin: 0 auto; padding:7px 15px; width: 70%; min-width: 200px;">
					</label>
					<a style="color: rgb(242, 20, 67); font-family: arial; font-size: 16px; display: block;margin: 10px; text-decoration: none;" href="http://litelib.profitquery.com/cms-sign-in/?domain='.$this->getDomain().'&cms=wp&ae='.get_settings('admin_email').'&redirect='.
                     str_replace(".", "%2E", urlencode($this->getSettingsPageUrl())).'" target="_getLitePQApiKey">Get API Key</a>
					<input type="submit" value="Confirm and save" style="font-family: pt sans narrow; color: white; background: #F21443; border: none; font-size: 20px; padding: 10px 40px; margin: 20px auto 0; border-radius: 3px; ">	
					 
				</form>
				<script>
					function checkApiKey(){						
						var	winParamString = "menubar=0,toolbar=0,resizable=1,scrollbars=1,width=400,height=200";											
						var clonWinParamString = winParamString;
						try {
							var e = winParamString.split("width=")[1].split(",")[0],
								f = winParamString.split("height=")[1].split(",")[0],
								g = (screen.width - e) / 2,
								h = (screen.height - f) / 2;
							g < 0 && (g = 0);
							h < 0 && (h = 0);
							clonWinParamString = clonWinParamString + (",top=" + h + ",left=" + g)
						} catch (i) {}
						try {							
							wopen = window.open("http://litelib.profitquery.com/cms-check-key/?domain='.$this->getDomain().'&cms=wp&ae='.get_settings('admin_email').'&redirect='.$redirect_url.'&apiKey="+encodeURIComponent(document.getElementById("lPQApiKeyInput").value), "Lite_Profitquery_API_Key_Check", clonWinParamString);							
						}catch(err){}						
					}
				</script>
			</section>
			</div>
			';	
		} else if((int)$this->_options['errorApiKey'] == 0) {
			if($this->is_subscribe_enabled()){
				if(trim($this->_options[subscribeProvider]) == '' || (int)$this->_options['subscribeProviderOption'][$this->_options['subscribeProvider']][is_error] == 1){
					echo '
						<div style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(242, 20, 67, 0.5); text-align: center;">
						 <p style="color: rgb(174, 0, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">For complete install Subscribe tools please copy/paste correct sign up form from selected provider <a href="'.$this->getSettingsPageUrl().'#setupFormAction" style="text-decoration: none;" >Complete setup</a></p>
						</div>						
					';
				}
			}
			
			if($this->is_follow_enabled_and_not_setup()){
				echo '
						<div style="display: block;width: auto; margin: 0 15px 0 5px; background: rgba(242, 20, 67, 0.5); text-align: center;">
						 <p style="color: rgb(174, 0, 0); font-size: 16px; font-family: arial; padding: 5px; margin: 0px;">For complete install follow popup after proceed, please set up any follow address <a href="'.$this->getSettingsPageUrl().'#setupFollow" style="text-decoration: none;" >Complete setup</a></p>
						</div>						
					';
			}
			?>
			<div style="width: 100%; overflow: hidden;">
				<div class="pq-container-fluid" id="free_profitquery">
				<script>
					var photoPath = "<?php echo plugins_url().'/'.PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME.'/'.PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PATH;?>";
					var previewPath = "<?php echo plugins_url().'/'.PROFITQUERY_SUBSCRIBE_WIDGETS_PLUGIN_NAME.'/'.PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PATH.PROFITQUERY_SUBSCRIBE_WIDGETS_ADMIN_IMG_PREVIEW_PATH;?>";
					function chagnePopupImg(img, id, custom_photo_block_id){						
						try{							
							if(img == 'custom'){								
								document.getElementById(id).style.display = 'none';
								document.getElementById(custom_photo_block_id).style.display = 'block';
							}else if(img != ''){								
								document.getElementById(id).style.display = 'block';
								document.getElementById(id).src = photoPath+img;
								document.getElementById(custom_photo_block_id).style.display = 'none';
							} else {
								document.getElementById(id).style.display = 'none';
								document.getElementById(custom_photo_block_id).style.display = 'none';
							}
						}catch(err){};
					}
				  </script>
				<div style="overflow: hidden; padding: 20px; margin: 10px 0 25px;">
				
					<h5>Thanks for your choose!</h5>
					<p style="padding: 0px 45px"> Latest news and plans of our team. </p><br>
					<div>
						<p><strong>New in Profitquery AIO Widgets 2.1.9</strong></p>
						<p><strong>1.</strong> Add new share provider Evernote, Pocket, Kindle, Flipboard. If you need a new share provider, just email us <a href="mailto:support@profitquery.com">support@profitquery.com.</a> <strong>2.</strong> Add wonderfull features. Now you can share image from sharing sidebar through (Tumblr, Pinterest, VK). All image profitquery collect from your page, if profitquery library not found any photo by click on Tumblr or Pinterest or VK start default sharing. <strong>3.</strong> Try to add Opera mini support (most of kind tools not displaying on this browser)</p><br>						
						
						
						<p>For wordpress community we make a few plugin for demonstration a small part of Profitquery platform features. AIO widgets most popular.
						Now we working for pro version wordpress plugin with new dashboard where you can generate any popup you want, ecom plugin (referral system etc.)
						If you have any question or feedback or some ideas you can email us any time you want <a href="mailto:support@profitquery.com;">support@profitquery.com</a> or visit profitquery <a href="http://profitquery.com/community.html" target="_blank">community page</a></p><br>
						<a href="http://profitquery.com/community.html" target="_blank"><input type="button" class="" value="Community"></a><br><br><br>
						<img id="" src="<?php echo plugins_url('images/stars.png', __FILE__);?>" />
						<p>We work hard 7 days of week for make a best ever growth tools. If you like our work, you can make our team happy, please, rate our plugin.</p>
					</div><br>
									
					
				<a href="https://wordpress.org/support/view/plugin-reviews/mailchimp-bar-exit-popup-subscribe-witget" target="_blank"><input type="button" class="" value="Please Rate Plugin"></a>
				
				
				</div>
					<div class="pq_block" id="v1">
					
						<h4>Subscribe Tools</h4>
						
					<div id="collapseTwo" class="panel-collapse collapse in">
					<form action="<?php echo $this->getSettingsPageUrl();?>#EmailBlock" method="post">
					<input type="hidden" name="action" value="edit">
					  <div class="pq-panel-body">
					  <p>Get more subscribers, simply mailchimp & aweber integration.</p>						
						
						<div class="pq-sm-6">
							<img id="subscribeBar_IMG" src="<?php echo plugins_url('images/bar.png', __FILE__);?>" />
							<h5>Marketing Bar</h5>
							<div class="pq-sm-10">							
							
							<label>								
								<div id="subscribeBarEnabledStyle">
									<input type="checkbox" name="subscribeBar[enabled]" id="subscribeBarEnabledCheckbox" onclick="changeSubscribeBarEnabled();" <?php if((int)$this->_options[subscribeBar][disabled] == 0) echo 'checked';?>>
									<p id="subscribeBarEnabledText"></p>
								</div>								
							</label>
							
							<label>
							<select id="subscribeBar_position" onchange="changeSubscribeBarBlockImg();" name="subscribeBar[position]">
								<option value="pq_top"  <?php if($this->_options[subscribeBar][position] == 'pq_top' || $this->_options[subscribeBar][position] == '') echo 'selected';?>>Top</option>
								<option value="pq_bottom" <?php if($this->_options[subscribeBar][position] == 'pq_bottom') echo 'selected';?>>Bottom</option>
							</select>
							<script>
								function changeSubscribeBarBlockImg(){
									if(document.getElementById('subscribeBar_position').value == 'pq_top'){
										document.getElementById('subscribeBar_IMG').src = previewPath+'bar_top.png';
									}
									if(document.getElementById('subscribeBar_position').value == 'pq_bottom'){
										document.getElementById('subscribeBar_IMG').src = previewPath+'bar_bottom.png';
									}
								}
								changeSubscribeBarBlockImg();
							</script>
							</label>
							<a href="#Marketing_Bar" onclick="document.getElementById('Marketing_Bar').style.display='block';"><button type="button" class="pq-btn-link btn-bg">More Option</button></a>							
							</div>
						</div>
						<div class="pq-sm-6">
							<img id="subscribeExit_IMG" src="<?php echo plugins_url('images/subscribe.png', __FILE__);?>" />
							<h5>Exit Popup</h5>
							<div class="pq-sm-10">							
							<label>								
								<div id="subscribeExitEnabledStyle">
									<input type="checkbox" name="subscribeExit[enabled]" id="subscribeExitEnabledCheckbox" onclick="changeSubscribeExitEnabled();" <?php if((int)$this->_options[subscribeExit][disabled] == 0) echo 'checked';?>>
									<p id="subscribeExitEnabledText"></p>
								</div>
								
							</label>
							
							<label>
							<select id="subscribeExit_typeWindow" onchange="changeSubscribeExitBlockImg();" name="subscribeExit[typeWindow]">
								<option value="pq_large" <?php if($this->_options[subscribeExit][typeWindow] == 'pq_large' || $this->_options[subscribeExit][typeWindow] == '') echo 'selected';?>>Size L</option>
								<option value="pq_medium" <?php if($this->_options[subscribeExit][typeWindow] == 'pq_medium') echo 'selected';?>>Size M</option>								
								<option value="pq_mini" <?php if($this->_options[subscribeExit][typeWindow] == 'pq_mini') echo 'selected';?>>Size S</option>
							</select>
							<script>
								function changeSubscribeExitBlockImg(){
									if(document.getElementById('subscribeExit_typeWindow').value == 'pq_large'){
										document.getElementById('subscribeExit_IMG').src = previewPath+'mail_l.png';
									}
									if(document.getElementById('subscribeExit_typeWindow').value == 'pq_medium'){
										document.getElementById('subscribeExit_IMG').src = previewPath+'mail_m.png';
									}
									if(document.getElementById('subscribeExit_typeWindow').value == 'pq_mini'){
										document.getElementById('subscribeExit_IMG').src = previewPath+'mail_s.png';
									}
								}
								changeSubscribeBarBlockImg();
							</script>
							</label>
							<a href="#Exit_Popup" onclick="document.getElementById('Exit_Popup').style.display='block';"><button type="button" class="pq-btn-link btn-bg">More Option</button></a>							
							</div>							
						</div>																
						</div>					
					<div class="pq-panel-body">
						<a name="Marketing_Bar"></a><div class="pq-sm-10 pq_more" id="Marketing_Bar" style="display:none;">
							<h5>More options Marketing Bar</h5>
							<div class="pq-sm-10" style="width: 83.333333%;">
							
							<label style="display: block;"><p>Heading</p><input type="text" name="subscribeBar[title]" value="<?php echo stripslashes($this->_options[subscribeBar][title]);?>"></label>	
							<label style="display: block;"><p>Heading for Mobile</p><input type="text" name="subscribeBar[mobile_title]" value="<?php echo stripslashes($this->_options[subscribeBar][mobile_title]);?>"></label>	
							<label style="display: block;"><p>Input email text</p><input type="text" name="subscribeBar[inputEmailTitle]" value="<?php echo stripslashes($this->_options[subscribeBar][inputEmailTitle]);?>"></label>
							<label style="display: block;"><p>Input name text (Aweber)</p><input type="text" name="subscribeBar[inputNameTitle]" value="<?php echo stripslashes($this->_options[subscribeBar][inputNameTitle]);?>"></label>
							<label style="display: block;"><p>Button</p><input type="text" name="subscribeBar[buttonTitle]" value="<?php echo stripslashes($this->_options[subscribeBar][buttonTitle]);?>"></label>
							
							<div class="pq-sm-6 icons" style="padding-left: 0; margin: 27px 0 0;">
							<label><select id="subscribeBar_background" onchange="subscribeBarPreview();" name="subscribeBar[background]">
								    <option value="bg_grey" <?php if($this->_options[subscribeBar][background] == 'bg_grey') echo 'selected';?>>Background - Grey</option>
									<option value="" <?php if($this->_options[subscribeBar][background] == '') echo 'selected';?>>Background - White</option>
									<option value="bg_yellow" <?php if($this->_options[subscribeBar][background] == 'bg_yellow') echo 'selected';?>>Background - Yellow</option>
									<option value="bg_wormwood" <?php if($this->_options[subscribeBar][background] == 'bg_wormwood') echo 'selected';?>>Background - Wormwood</option>
									<option value="bg_blue" <?php if($this->_options[subscribeBar][background] == 'bg_blue') echo 'selected';?>>Background - Blue</option>
									<option value="bg_green" <?php if($this->_options[subscribeBar][background] == 'bg_green') echo 'selected';?>>Background - Green</option>
									<option value="bg_beige" <?php if($this->_options[subscribeBar][background] == 'bg_beige') echo 'selected';?>>Background - Beige</option>
									<option value="bg_red" <?php if($this->_options[subscribeBar][background] == 'bg_red') echo 'selected';?>>Background - Red</option>
									<option value="bg_iceblue" <?php if($this->_options[subscribeBar][background] == 'bg_iceblue') echo 'selected';?>>Background - Iceblue</option>
									<option value="bg_black" <?php if($this->_options[subscribeBar][background] == 'bg_black') echo 'selected';?>>Background - Black</option>
									<option value="bg_skyblue" <?php if($this->_options[subscribeBar][background] == 'bg_skyblue') echo 'selected';?>>Background - Skyblue</option>
									<option value="bg_lilac" <?php if($this->_options[subscribeBar][background] == 'bg_lilac') echo 'selected';?>>Background - Lilac</option>
							</select></label>
							</div>
							<div class="pq-sm-6 icons down" style="padding-right: 0; margin: 27px 0 10px;">
							<label><select id="subscribeBar_button_color" onchange="subscribeBarPreview();" name="subscribeBar[button_color]">
								    <option value="btn_lightblue" <?php if($this->_options[subscribeBar][button_color] == 'btn_lightblue') echo 'selected';?>>Button - Lightblue</option>
									<option value="btn_lightblue invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_lightblue invert' || $this->_options[subscribeBar][button_color] == '') echo 'selected';?>>Button - Lightblue Transparent</option>
									<option value="btn_blue" <?php if($this->_options[subscribeBar][button_color] == 'btn_blue') echo 'selected';?>>Button - Blue</option>
									<option value="btn_blue invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_blue invert') echo 'selected';?>>Button - Blue Transparent</option>
									<option value="btn_black" <?php if($this->_options[subscribeBar][button_color] == 'btn_black') echo 'selected';?>>Button - Black</option>
									<option value="btn_black invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_black invert') echo 'selected';?>>Button - Black Transparent</option>
									<option value="btn_green" <?php if($this->_options[subscribeBar][button_color] == 'btn_green') echo 'selected';?>>Button - Green</option>
									<option value="btn_green invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_green invert') echo 'selected';?>>Button - Green Transparent</option>
									<option value="btn_violet" <?php if($this->_options[subscribeBar][button_color] == 'btn_violet') echo 'selected';?>>Button - Violet</option>
									<option value="btn_violet invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_violet invert') echo 'selected';?>>Button - Violet Transparent</option>
									<option value="btn_orange" <?php if($this->_options[subscribeBar][button_color] == 'btn_orange') echo 'selected';?>>Button - Orange</option>
									<option value="btn_orange invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_orange invert') echo 'selected';?>>Button - Orange Transparent</option>
									<option value="btn_red" <?php if($this->_options[subscribeBar][button_color] == 'btn_red') echo 'selected';?>>Button - Red</option>
									<option value="btn_red invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_red invert') echo 'selected';?>>Button - Red Transparent</option>
									<option value="btn_lilac" <?php if($this->_options[subscribeBar][button_color] == 'btn_lilac') echo 'selected';?>>Button - Lilac</option>
									<option value="btn_lilac invert" <?php if($this->_options[subscribeBar][button_color] == 'btn_lilac invert') echo 'selected';?>>Button - Lilac Transparent</option>
							</select></label>
							</div>
							<div class="clear"></div>
							<label>
							<select id="subscribeBar_size" onchange="subscribeBarPreview();" name="subscribeBar[size]">
								<option value="" <?php if($this->_options[subscribeBar][size] == '') echo 'selected';?>>Size M</option>
								<option value="pq_small" <?php if($this->_options[subscribeBar][size] == 'pq_small') echo 'selected';?>>Size S</option>
							</select>
							</label>							
							<div class="clear"></div>							
							<label style="width: 49%; display: inline-block; margin: 5px 0 15px;">
									<input type="radio" id="subscribeBar_animation_bounce" name="subscribeBar[animation]" onclick="subscribeBarPreview();" value="bounce" <?php if($this->_options[subscribeBar][animation] == 'bounce') echo 'checked';?> style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Bounce animation</p>
							</label>
							<label style="width: 49%; display: inline-block; margin: 5px 0 15px;">
									<input type="radio" id="subscribeBar_animation_fade" name="subscribeBar[animation]" onclick="subscribeBarPreview();" value="fade" <?php if($this->_options[subscribeBar][animation] == 'fade' || $this->_options[subscribeBar][animation] == '') echo 'checked';?> style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Fading animation</p>
							</label>
							<div class="clear"></div>
							<label>
							<div class="pq_box">
								<p>Follow Popup After Success</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeBar[afterProceed][follow]" <?php if((int)$this->_options[subscribeBar][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div>
							</label>
							<label>
							<div class="pq_box">
								<p>Thank Popup After Success</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeBar[afterProceed][thank]" <?php if((int)$this->_options[subscribeBar][afterProceed][thank] == 1) echo 'checked';?>></div>
								<div class="pq_tooltip" data-toggle="tooltip" data-placement="left" title="For enable Follow Popup must be Off"></div>
							</div>
							</label>							
							<div class="clear"></div>
							<p style="font-family: pt sans narrow; font-size: 19px; margin: 20px 0 10px;">Only Design Live Demo</p>
							<img src="<?php echo plugins_url('images/browser.png', __FILE__);?>" style="width: 100%; margin-bottom: -6px;" />
							<div style="transform-origin: 0 0; transform: scale(0.55); width: 1024px; height: 400px; box-sizing: border-box; border: 1px solid lightgrey; margin-bottom: -150px;">
							<iframe scrolling="no" id="subscribeBarLiveViewIframe" width="100%" height="400px" src="" style="background: white; margin: 0;"></iframe>
							</div>
							
							<script>								
								function subscribeBarPreview(){	
									if(document.getElementById('subscribeBar_animation_bounce').checked) {
										var animation = 'pq_animated bounce';
									} else {
										var animation = '';
									}									
									var design = document.getElementById('subscribeBar_size').value+' '+document.getElementById('subscribeBar_position').value+' '+document.getElementById('subscribeBar_background').value+' '+document.getElementById('subscribeBar_button_color').value+' '+animation;
									var previewUrl = 'http://profitquery.com/aio_widgets_iframe_demo_v2.html?utm-campaign=wp_aio_widgets&p=subscribeBar&design='+design;									
									document.getElementById('subscribeBarLiveViewIframe').src = previewUrl;
									console.log(previewUrl);
								}
								subscribeBarPreview();
							</script>
							
							</div>
							
						<a href="javascript:void(0)" onclick="document.getElementById('Marketing_Bar').style.display='none';"><div class="pq_close"></div></a>
						</div>
						<a name="Exit_Popup"></a><div class="pq-sm-10 pq_more" id="Exit_Popup" style="display:none;">
							<h5>More options Exit Popup</h5>
							<div class="pq-sm-10" style="width: 83.333333%;">
							
							<label type="text" style="display: block;"><p>Heading</p><input type="text" name="subscribeExit[title]" value="<?php echo stripslashes($this->_options[subscribeExit][title]);?>"></label>
							<label style="display: block;"><p>Text</p><input type="text" name="subscribeExit[sub_title]" value="<?php echo stripslashes($this->_options[subscribeExit][sub_title]);?>"></label>
							<label style="display: block;"><p>Button</p><input type="text" name="subscribeExit[buttonTitle]" value="<?php echo stripslashes($this->_options[subscribeExit][buttonTitle]);?>"></label>
							<label style="display: block;"><p>Input email text</p><input type="text" name="subscribeExit[inputEmailTitle]" value="<?php echo stripslashes($this->_options[subscribeExit][inputEmailTitle]);?>"></label>
							<label style="display: block;"><p>Input name text (Aweber)</p><input type="text" name="subscribeExit[inputNameTitle]" value="<?php echo stripslashes($this->_options[subscribeExit][inputNameTitle]);?>"></label>
							
							<div class="pq-sm-6 icons" style="padding-left: 0; margin: 27px 0 0;">
							<label>
							<select id="subscribeExit_background" onchange="subscribeExitPreview();" name="subscribeExit[background]">
								    <option value="bg_grey" <?php if($this->_options[subscribeExit][background] == 'bg_grey') echo 'selected';?>>Background - Grey</option>
									<option value="" <?php if($this->_options[subscribeExit][background] == '') echo 'selected';?>>Background - White</option>
									<option value="bg_yellow" <?php if($this->_options[subscribeExit][background] == 'bg_yellow') echo 'selected';?>>Background - Yellow</option>
									<option value="bg_wormwood" <?php if($this->_options[subscribeExit][background] == 'bg_wormwood') echo 'selected';?>>Background - Wormwood</option>
									<option value="bg_blue" <?php if($this->_options[subscribeExit][background] == 'bg_blue') echo 'selected';?>>Background - Blue</option>
									<option value="bg_green" <?php if($this->_options[subscribeExit][background] == 'bg_green') echo 'selected';?>>Background - Green</option>
									<option value="bg_beige" <?php if($this->_options[subscribeExit][background] == 'bg_beige') echo 'selected';?>>Background - Beige</option>
									<option value="bg_red" <?php if($this->_options[subscribeExit][background] == 'bg_red') echo 'selected';?>>Background - Red</option>
									<option value="bg_iceblue" <?php if($this->_options[subscribeExit][background] == 'bg_iceblue') echo 'selected';?>>Background - Iceblue</option>
									<option value="bg_black" <?php if($this->_options[subscribeExit][background] == 'bg_black') echo 'selected';?>>Background - Black</option>
									<option value="bg_skyblue" <?php if($this->_options[subscribeExit][background] == 'bg_skyblue') echo 'selected';?>>Background - Skyblue</option>
									<option value="bg_lilac" <?php if($this->_options[subscribeExit][background] == 'bg_lilac') echo 'selected';?>>Background - Lilac</option>
							</select></label>
							</div>
							<div class="pq-sm-6 icons down" style="padding-right: 0; margin: 27px 0 10px;">
							<label>
							<select id="subscribeExit_button_color" onchange="subscribeExitPreview();" name="subscribeExit[button_color]">
								    <option value="btn_lightblue" <?php if($this->_options[subscribeExit][button_color] == 'btn_lightblue') echo 'selected';?>>Button - Lightblue</option>
									<option value="btn_lightblue invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_lightblue invert' || $this->_options[subscribeExit][button_color] == '') echo 'selected';?>>Button - Lightblue Transparent</option>
									<option value="btn_blue" <?php if($this->_options[subscribeExit][button_color] == 'btn_blue') echo 'selected';?>>Button - Blue</option>
									<option value="btn_blue invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_blue invert') echo 'selected';?>>Button - Blue Transparent</option>
									<option value="btn_black" <?php if($this->_options[subscribeExit][button_color] == 'btn_black') echo 'selected';?>>Button - Black</option>
									<option value="btn_black invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_black invert') echo 'selected';?>>Button - Black Transparent</option>
									<option value="btn_green" <?php if($this->_options[subscribeExit][button_color] == 'btn_green') echo 'selected';?>>Button - Green</option>
									<option value="btn_green invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_green invert') echo 'selected';?>>Button - Green Transparent</option>
									<option value="btn_violet" <?php if($this->_options[subscribeExit][button_color] == 'btn_violet') echo 'selected';?>>Button - Violet</option>
									<option value="btn_violet invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_violet invert') echo 'selected';?>>Button - Violet Transparent</option>
									<option value="btn_orange" <?php if($this->_options[subscribeExit][button_color] == 'btn_orange') echo 'selected';?>>Button - Orange</option>
									<option value="btn_orange invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_orange invert') echo 'selected';?>>Button - Orange Transparent</option>
									<option value="btn_red" <?php if($this->_options[subscribeExit][button_color] == 'btn_red') echo 'selected';?>>Button - Red</option>
									<option value="btn_red invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_red invert') echo 'selected';?>>Button - Red Transparent</option>
									<option value="btn_lilac" <?php if($this->_options[subscribeExit][button_color] == 'btn_lilac') echo 'selected';?>>Button - Lilac</option>
									<option value="btn_lilac invert" <?php if($this->_options[subscribeExit][button_color] == 'btn_lilac invert') echo 'selected';?>>Button - Lilac Transparent</option>
							</select></label>
							</div>
							<div class="clear"></div>
							<label style="width: 49%; display: inline-block; margin: 0px 0 10px;">
									<input type="radio" id="subscribeExit_animation_bounce" onclick="subscribeExitPreview()" name="subscribeExit[animation]" value="tada" <?php if($this->_options[subscribeExit][animation] == 'tada') echo 'checked';?> style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Animation</p>
							</label>
							<label style="width: 49%; display: inline-block; margin: 0px 0 10px;">
									<input type="radio" id="subscribeExit_animation_fade" onclick="subscribeExitPreview()" name="subscribeExit[animation]" value="fade" <?php if($this->_options[subscribeExit][animation] == 'fade' || $this->_options[subscribeExit][animation] == '') echo 'checked';?> style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Fading animation</p>
							</label>
							<hr>
							<div class="pq-sm-12 icons" style="padding-left: 0; margin: 27px 0 0;">
							<label><select id="subscribeExit_overlay" onchange="subscribeExitPreview();" name="subscribeExit[overlay]">
								    <option value="over_grey" <?php if($this->_options[subscribeExit][overlay] == 'over_grey') echo 'selected';?>>Color overlay - Grey</option>
									<option value="over_white" <?php if($this->_options[subscribeExit][overlay] == 'over_white' || $this->_options[subscribeExit][overlay] == '') echo 'selected';?>>Color overlay - White</option>
									<option value="over_yellow" <?php if($this->_options[subscribeExit][overlay] == 'over_yellow') echo 'selected';?>>Color overlay - Yellow</option>
									<option value="over_wormwood" <?php if($this->_options[subscribeExit][overlay] == 'over_wormwood') echo 'selected';?>>Color overlay - Wormwood</option>
									<option value="over_blue" <?php if($this->_options[subscribeExit][overlay] == 'over_blue') echo 'selected';?>>Color overlay - Blue</option>
									<option value="over_green" <?php if($this->_options[subscribeExit][overlay] == 'over_green') echo 'selected';?>>Color overlay - Green</option>
									<option value="over_beige" <?php if($this->_options[subscribeExit][overlay] == 'over_beige') echo 'selected';?>>Color overlay - Beige</option>
									<option value="over_red" <?php if($this->_options[subscribeExit][overlay] == 'over_red') echo 'selected';?>>Color overlay - Red</option>
									<option value="over_iceblue" <?php if($this->_options[subscribeExit][overlay] == 'over_iceblue') echo 'selected';?>>Color overlay - Iceblue</option>
									<option value="over_black" <?php if($this->_options[subscribeExit][overlay] == 'over_black') echo 'selected';?>>Color overlay - Black</option>
									<option value="over_skyblue" <?php if($this->_options[subscribeExit][overlay] == 'over_skyblue') echo 'selected';?>>Color overlay - Skyblue</option>
									<option value="over_lilac" <?php if($this->_options[subscribeExit][overlay] == 'over_lilac') echo 'selected';?>>Color overlay - Lilac</option>
									<option value="over_grey_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_grey_lt') echo 'selected';?>>Color overlay - Grey - Light</option>
									<option value="over_white_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_white_lt') echo 'selected';?>>Color overlay - White - Light</option>
									<option value="over_yellow_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_yellow_lt') echo 'selected';?>>Color overlay - Yellow - Light</option>
									<option value="over_wormwood_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_wormwood_lt') echo 'selected';?>>Color overlay - Wormwood - Light</option>
									<option value="over_blue_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_blue_lt') echo 'selected';?>>Color overlay - Blue - Light</option>
									<option value="over_green_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_green_lt') echo 'selected';?>>Color overlay - Green - Light</option>
									<option value="over_beige_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_beige_lt') echo 'selected';?>>Color overlay - Beige - Light</option>
									<option value="over_red_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_red_lt') echo 'selected';?>>Color overlay - Red - Light</option>
									<option value="over_iceblue_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_iceblue_lt') echo 'selected';?>>Color overlay - Iceblue - Light</option>
									<option value="over_black_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_black_lt') echo 'selected';?>>Color overlay - Black - Light</option>
									<option value="over_skyblue_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_skyblue_lt') echo 'selected';?>>Color overlay - Skyblue - Light</option>
									<option value="over_lilac_lt" <?php if($this->_options[subscribeExit][overlay] == 'over_lilac_lt') echo 'selected';?>>Color overlay - Lilac - Light</option>
									<option value="over_grey_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_grey_solid') echo 'selected';?>>Color overlay - Grey - Solid</option>
									<option value="over_white_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_white_solid') echo 'selected';?>>Color overlay - White - Solid</option>
									<option value="over_yellow_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_yellow_solid') echo 'selected';?>>Color overlay - Yellow - Solid</option>
									<option value="over_wormwood_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_wormwood_solid') echo 'selected';?>>Color overlay - Wormwood - Solid</option>
									<option value="over_blue_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_blue_solid') echo 'selected';?>>Color overlay - Blue - Solid</option>
									<option value="over_green_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_green_solid') echo 'selected';?>>Color overlay - Green - Solid</option>
									<option value="over_beige_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_beige_solid') echo 'selected';?>>Color overlay - Beige - Solid</option>
									<option value="over_red_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_red_solid') echo 'selected';?>>Color overlay - Red - Solid</option>
									<option value="over_iceblue_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_iceblue_solid') echo 'selected';?>>Color overlay - Iceblue - Solid</option>
									<option value="over_black_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_black_solid') echo 'selected';?>>Color overlay - Black - Solid</option>
									<option value="over_skyblue_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_skyblue_solid') echo 'selected';?>>Color overlay - Skyblue - Solid</option>
									<option value="over_lilac_solid" <?php if($this->_options[subscribeExit][overlay] == 'over_lilac_solid') echo 'selected';?>>Color overlay - Lilac - Solid</option>
							</select></label>
							</div>
							
							<div class="clear"></div>
							<label>
							<select id="subscribeExit_img"  name="subscribeExit[img]" onchange="chagnePopupImg(this.value, 'subscribeExitFotoBlock', 'subscribeExitCustomFotoBlock');subscribeExitPreview();">
								<option value="" selected >No picture</option>
								<option value="img_01.png" <?php if($this->_options[subscribeExit][img] == 'img_01.png') echo 'selected';?>>Question</option>
								<option value="img_02.png" <?php if($this->_options[subscribeExit][img] == 'img_02.png') echo 'selected';?>>Attention</option>
								<option value="img_03.png" <?php if($this->_options[subscribeExit][img] == 'img_03.png') echo 'selected';?>>Info</option>
								<option value="img_04.png" <?php if($this->_options[subscribeExit][img] == 'img_04.png') echo 'selected';?>>Knowledge</option>
								<option value="img_05.png" <?php if($this->_options[subscribeExit][img] == 'img_05.png') echo 'selected';?>>Idea</option>
								<option value="img_06.png" <?php if($this->_options[subscribeExit][img] == 'img_06.png') echo 'selected';?>>Talk</option>
								<option value="img_07.png" <?php if($this->_options[subscribeExit][img] == 'img_07.png') echo 'selected';?>>News</option>
								<option value="img_08.png" <?php if($this->_options[subscribeExit][img] == 'img_08.png') echo 'selected';?>>Megaphone</option>
								<option value="img_09.png" <?php if($this->_options[subscribeExit][img] == 'img_09.png') echo 'selected';?>>Gift</option>
								<option value="img_10.png" <?php if($this->_options[subscribeExit][img] == 'img_10.png') echo 'selected';?>>Success</option>
								<option value="custom" <?php if($this->_options[subscribeExit][img] == 'custom') echo 'selected';?>>Your custom image ...</option>
							</select></label>
							<label style="margin-top: 20px;"><div class="img"><img id="subscribeExitFotoBlock" />
							<input type="text" name="subscribeExit[imgUrl]" onkeyup="subscribeExitPreview();"  style="display:none;" id="subscribeExitCustomFotoBlock" placeholder="Enter your image URL" value="<?php echo stripslashes($this->_options[subscribeExit][imgUrl]);?>">
							</div></label>							
							<label><div class="pq_box">
								<p>Follow Popup After Success</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeExit[afterProceed][follow]" <?php if((int)$this->_options[subscribeExit][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>Thank Popup After Success</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeExit[afterProceed][thank]" <?php if((int)$this->_options[subscribeExit][afterProceed][thank] == 1) echo 'checked';?>></div>
								<div class="pq_tooltip" data-toggle="tooltip" data-placement="left" title="For enable Follow Popup must be Off"></div>
							</div></label>
							<?php
								echo "
								<script>
									chagnePopupImg('".$this->_options[subscribeExit][img]."', 'subscribeExitFotoBlock', 'subscribeExitCustomFotoBlock');
								</script>
								";
							?>							
							<div class="clear"></div>
							<p style="font-family: pt sans narrow; font-size: 19px; margin: 20px 0 10px;">Only Design Live Demo</p>
							<img src="<?php echo plugins_url('images/browser.png', __FILE__);?>" style="width: 100%; margin-bottom: -6px;" />
							<div style="transform-origin: 0 0; transform: scale(0.8); width: 125%; height: 300px; box-sizing: border-box; border: 1px solid lightgrey;">
							<iframe scrolling="no" id="subscribeExitLiveViewIframe" width="100%" height="300px" src="" style="background: white; margin: 0;"></iframe>
							</div>
							<script>
								function subscribeExitPreview(){									
									
									var img = document.getElementById('subscribeExit_img').value;
									var imgUrl = document.getElementById('subscribeExitCustomFotoBlock').value;	
									if(document.getElementById('subscribeExit_animation_bounce').checked) {
										var animation = 'pq_animated bounceInDown';
									} else {
										var animation = '';
									}
									var design = document.getElementById('subscribeExit_typeWindow').value+' pq_top '+document.getElementById('subscribeExit_background').value+' '+document.getElementById('subscribeExit_button_color').value+' '+animation;
									var overlay = document.getElementById('subscribeExit_overlay').value;									
									var previewUrl = 'http://profitquery.com/aio_widgets_iframe_demo_v2.html?utm-campaign=wp_aio_widgets&p=subscribeExit&design='+design+'&overlay='+encodeURIComponent(overlay)+'&img='+encodeURIComponent(img)+'&imgUrl='+encodeURIComponent(imgUrl);									
									document.getElementById('subscribeExitLiveViewIframe').src = previewUrl;									
									
								}
								subscribeExitPreview();
							</script>
							</div>
						
						<a href="javascript:void(0)" onclick="document.getElementById('Exit_Popup').style.display='none';"><div class="pq_close"></div></a>
						</div>
					</div>
					<input type="submit" class="btn_m_red" value="Save changes">
					<a href="mailto:support@profitquery.com" target="_blank" class="pq_help">Need help?</a>
					</form>
					<a name="setupFormAction"></a>
					<form action="<?php echo $this->getSettingsPageUrl();?>#setupFormAction" method="post">
					<input type="hidden" name="action" value="subscribeProviderSetup">					
					<div class="pq-panel-body">
						<div class="pq-sm-10" id="mailchimpBlockID"  style="overflow: hidden; padding: 0 20px; margin: 0 auto 10px; background: #F3F3F3;display:none; ">						
						<h5>Subscribe Provider Setup</h5>
						<div class="pq-panel-body" style="background: #F3F3F3; padding: 20px 0 0px; margin: 0 15px;">
							<div class="pq-sm-12">
								<div class="pq-sm-10 icons" style="margin: 0 auto; float: none;">
									<label><select onchange="changeSubscribeProviderHelpUrl(1);" id="subscribeProvider" name="subscribeProvider" style="width: 100%; box-sizing: border-box; padding: 4px; margin: 10px 0 0;">
										<option value="mailchimp" <?php if($this->_options[subscribeProvider] == '' || $this->_options[subscribeProvider] == 'mailchimp') echo "selected";?>>MailChimp</option>
										<option value="aweber" <?php if($this->_options[subscribeProvider] == 'aweber') echo "selected";?>>AWeber</option>										
									</select></label>
									<div class="pq_mch">									
										<div id="subscribeProviderFormID" class="pq_ent <?php if($this->_options['subscribeProviderOption'][$this->_options[subscribeProvider]]['is_error']) echo 'pq_error';?>" <?php if((int)$this->_options['subscribeProviderOption'][$this->_options[subscribeProvider]]['is_error'] == 1 || !$this->_options['subscribeProviderOption'][$this->_options[subscribeProvider]][formAction]) echo 'style="display:block;";'; else echo 'style="display:none;";';?> />
											<a href="http://profitquery.com/mailchimp.html" id="subscribeProviderHelpUrl" target="_blank">How to get code</a>
											<label><p>Paste your code here:</p>
												<textarea name="subscribeProviderFormContent" rows="5"></textarea>												
												<input type="submit" value="Save" />												
											</label>
										</div>									
										<div id="subscribeProviderEditLinkID" class="pq_result" onclick="enableSubsribeForm();" <?php if($this->_options['subscribeProviderOption'][$this->_options[subscribeProvider]]['is_error']) echo 'pq_error';?>" <?php if((int)$this->_options['subscribeProviderOption'][$this->_options[subscribeProvider]]['is_error'] == 1 || !$this->_options['subscribeProviderOption'][$this->_options[subscribeProvider]][formAction]) echo 'style="display:none;";'; else echo 'style="display:block;";';?> />
											<img src="<?php echo plugins_url('images/ok.png', __FILE__);?>" />
											<a href="javascript:void(0)" onclick="return false;">Change settings</a>
										</div>
										<script>
											function changeSubscribeProviderHelpUrl(withCheckCurrent){
												var currentSubscribeProvider = '<?php echo $this->_options[subscribeProvider];?>'
												if(document.getElementById('subscribeProvider').value == 'mailchimp'){
													document.getElementById('subscribeProviderHelpUrl').href = 'http://profitquery.com/mailchimp.html';
												}
												if(document.getElementById('subscribeProvider').value == 'aweber'){
													document.getElementById('subscribeProviderHelpUrl').href = 'http://profitquery.com/aweber.html';
												}
												if(withCheckCurrent == '1'){
													if(currentSubscribeProvider){
														if(currentSubscribeProvider == document.getElementById('subscribeProvider').value){
															document.getElementById('subscribeProviderFormID').style.display = 'none';												
															document.getElementById('subscribeProviderEditLinkID').style.display = 'block';
														} else {
															document.getElementById('subscribeProviderFormID').style.display = 'block';												
															document.getElementById('subscribeProviderEditLinkID').style.display = 'none';
														}
													}
												}
											}											
											function enableSubsribeForm(){												
												document.getElementById('subscribeProviderFormID').style.display = 'block';												
												document.getElementById('subscribeProviderEditLinkID').style.display = 'none';												
											}
											
											changeSubscribeProviderHelpUrl();
										</script>
									</div>									
								</div>
							</div>
						</div>						
						</div></div>						
					</div>
				  </div>
				  </form>				 
				<div class="pq_block" id="v4">					
						<h4>After Success</h4>
						
					<div id="collapseThree" class="panel-collapse collapse in">
					<form action="<?php echo $this->getSettingsPageUrl();?>#AfterSuccessBlock" method="post">
					<input type="hidden" name="action" value="editAdditionalData">
					  <div class="pq-panel-body">
					   <p>Get more social network follower's as after proceed bonus.</p>
						
						
						<div class="pq-sm-6">
							<img id="follow_IMG" src="<?php echo plugins_url('images/follow.png', __FILE__);?>" />
							<div class="pq-sm-10">
							<h5>Follow Popup</h5>							
							<label><select id="follow_background" onchange="changeFollowBlockImg()" name="follow[background]">
								    <option value="bg_grey" <?php if($this->_options[follow][background] == 'bg_grey') echo 'selected';?>>Background - Grey</option>
									<option value="" <?php if($this->_options[follow][background] == '') echo 'selected';?>>Background - White</option>
									<option value="bg_yellow" <?php if($this->_options[follow][background] == 'bg_yellow') echo 'selected';?>>Background - Yellow</option>
									<option value="bg_wormwood" <?php if($this->_options[follow][background] == 'bg_wormwood') echo 'selected';?>>Background - Wormwood</option>
									<option value="bg_blue" <?php if($this->_options[follow][background] == 'bg_blue') echo 'selected';?>>Background - Blue</option>
									<option value="bg_green" <?php if($this->_options[follow][background] == 'bg_green') echo 'selected';?>>Background - Green</option>
									<option value="bg_beige" <?php if($this->_options[follow][background] == 'bg_beige') echo 'selected';?>>Background - Beige</option>
									<option value="bg_red" <?php if($this->_options[follow][background] == 'bg_red') echo 'selected';?>>Background - Red</option>
									<option value="bg_iceblue" <?php if($this->_options[follow][background] == 'bg_iceblue') echo 'selected';?>>Background - Iceblue</option>
									<option value="bg_black" <?php if($this->_options[follow][background] == 'bg_black') echo 'selected';?>>Background - Black</option>
									<option value="bg_skyblue" <?php if($this->_options[follow][background] == 'bg_skyblue') echo 'selected';?>>Background - Skyblue</option>
									<option value="bg_lilac" <?php if($this->_options[follow][background] == 'bg_lilac') echo 'selected';?>>Background - Lilac</option>
							</select></label>
							<script>
								function changeFollowBlockImg(){
									if(document.getElementById('follow_background').value == 'bg_grey'){
										document.getElementById('follow_IMG').src = previewPath+'follow_7_m.png';
									}
									if(document.getElementById('follow_background').value == ''){
										document.getElementById('follow_IMG').src = previewPath+'follow_1_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_yellow'){
										document.getElementById('follow_IMG').src = previewPath+'follow_6_m.png';
									}									
									if(document.getElementById('follow_background').value == 'bg_wormwood'){
										document.getElementById('follow_IMG').src = previewPath+'follow_5_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_blue'){
										document.getElementById('follow_IMG').src = previewPath+'follow_10_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_green'){
										document.getElementById('follow_IMG').src = previewPath+'follow_11_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_beige'){
										document.getElementById('follow_IMG').src = previewPath+'follow_3_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_red'){
										document.getElementById('follow_IMG').src = previewPath+'follow_8_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_iceblue'){
										document.getElementById('follow_IMG').src = previewPath+'follow_2_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_black'){
										document.getElementById('follow_IMG').src = previewPath+'follow_12_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_skyblue'){
										document.getElementById('follow_IMG').src = previewPath+'follow_9_m.png';
									}
									if(document.getElementById('follow_background').value == 'bg_lilac'){
										document.getElementById('follow_IMG').src = previewPath+'follow_4_m.png';
									}
								}
								changeFollowBlockImg();
							</script>
							<a href="#After_Sharing" onclick="document.getElementById('After_Sharing').style.display='block';"><button type="button" class="pq-btn-link btn-bg">More Option</button></a>							
							</div>
						</div>
						<div class="pq-sm-6">
							<img id="thank_IMG" src="<?php echo plugins_url('images/thank.png', __FILE__);?>" />
							<div class="pq-sm-10">
							<h5>Thankyou Popup</h5>							
							
							<label><select id="thankPopup_background" onchange="changeThankBlockImg();" name="thankPopup[background]">
								    <option value="bg_grey" <?php if($this->_options[thankPopup][background] == 'bg_grey') echo 'selected';?>>Background - Grey</option>
									<option value="" <?php if($this->_options[thankPopup][background] == '') echo 'selected';?>>Background - White</option>
									<option value="bg_yellow" <?php if($this->_options[thankPopup][background] == 'bg_yellow') echo 'selected';?>>Background - Yellow</option>
									<option value="bg_wormwood" <?php if($this->_options[thankPopup][background] == 'bg_wormwood') echo 'selected';?>>Background - Wormwood</option>
									<option value="bg_blue" <?php if($this->_options[thankPopup][background] == 'bg_blue') echo 'selected';?>>Background - Blue</option>
									<option value="bg_green" <?php if($this->_options[thankPopup][background] == 'bg_green') echo 'selected';?>>Background - Green</option>
									<option value="bg_beige" <?php if($this->_options[thankPopup][background] == 'bg_beige') echo 'selected';?>>Background - Beige</option>
									<option value="bg_red" <?php if($this->_options[thankPopup][background] == 'bg_red') echo 'selected';?>>Background - Red</option>
									<option value="bg_iceblue" <?php if($this->_options[thankPopup][background] == 'bg_iceblue') echo 'selected';?>>Background - Iceblue</option>
									<option value="bg_black" <?php if($this->_options[thankPopup][background] == 'bg_black') echo 'selected';?>>Background - Black</option>
									<option value="bg_skyblue" <?php if($this->_options[thankPopup][background] == 'bg_skyblue') echo 'selected';?>>Background - Skyblue</option>
									<option value="bg_lilac" <?php if($this->_options[thankPopup][background] == 'bg_lilac') echo 'selected';?>>Background - Lilac</option>
							</select></label>
							<script>
								function changeThankBlockImg(){
									if(document.getElementById('thankPopup_background').value == 'bg_grey'){
										document.getElementById('thank_IMG').src = previewPath+'thank_7_m.png';
									}
									if(document.getElementById('thankPopup_background').value == ''){
										document.getElementById('thank_IMG').src = previewPath+'thank_1_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_yellow'){
										document.getElementById('thank_IMG').src = previewPath+'thank_6_m.png';
									}									
									if(document.getElementById('thankPopup_background').value == 'bg_wormwood'){
										document.getElementById('thank_IMG').src = previewPath+'thank_5_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_blue'){
										document.getElementById('thank_IMG').src = previewPath+'thank_10_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_green'){
										document.getElementById('thank_IMG').src = previewPath+'thank_11_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_beige'){
										document.getElementById('thank_IMG').src = previewPath+'thank_3_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_red'){
										document.getElementById('thank_IMG').src = previewPath+'thank_8_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_iceblue'){
										document.getElementById('thank_IMG').src = previewPath+'thank_2_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_black'){
										document.getElementById('thank_IMG').src = previewPath+'thank_12_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_skyblue'){
										document.getElementById('thank_IMG').src = previewPath+'thank_9_m.png';
									}
									if(document.getElementById('thankPopup_background').value == 'bg_lilac'){
										document.getElementById('thank_IMG').src = previewPath+'thank_4_m.png';
									}
								}
								changeThankBlockImg();
							</script>							
							
							<a href="#Thankyou_Popup" onclick="document.getElementById('Thankyou_Popup').style.display='block';"><button type="button" class="pq-btn-link btn-bg">More Option</button></a>							
							</div>							
						</div>						
					</div>
					
					
					
					<div class="pq-panel-body">
						<a name="After_Sharing"></a><div class="pq-sm-10 pq_more" id="After_Sharing" style="display:none;">
							<h5>More options Follow Us After Sharing</h5>
							<div class="pq-sm-10" style="width: 83.333333%;">
							
							<label style="display: block;"><p>Heading</p><input type="text" name="follow[title]" value="<?php echo stripslashes($this->_options[follow][title])?>"></label>					
							<label style="display: block;"><p>Text</p><input type="text" name="follow[sub_title]" value="<?php echo stripslashes($this->_options[follow][sub_title])?>"></label>					
							<div class="pq_services" style="overflow: hidden; padding: 20px 0 10px;" id="pq_input">							
							<label style="display: block;"><div class="x30">
								<div class="pq_fb"></div>
									<p>facebook.com/</p><input type="text" name="follow[follow_socnet][FB]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][FB]);?>">
							</div></label>
							<label style="display: block;"><div class="x30">
								<div class="pq_tw"></div>
									<p>twitter.com/</p><input type="text" name="follow[follow_socnet][TW]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][TW]);?>">
										
							</div></label>
							<div id="MoreFollowSocialNetworks" style="display:none;">
							<label style="display: block;"><div class="x30">
								<div class="pq_gp"></div>
									<p>plus.google.com/</p><input type="text" name="follow[follow_socnet][GP]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][GP]);?>">
										
							</div></label>
							<label style="display: block;"><div class="x30">
								<div class="pq_pi"></div>
									<p>pinterest.com/</p><input type="text" name="follow[follow_socnet][PI]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][PI]);?>">
										
							</div></label>
							<label style="display: block;"><div class="x30">
								<div class="pq_vk"></div>
									<p>vk.com/</p><input type="text" name="follow[follow_socnet][VK]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][VK]);?>">
										
							</div></label>
							<label style="display: block;"><div class="x30">
								<div class="pq_od"></div>
									<p>ok.ru/</p><input type="text" name="follow[follow_socnet][OD]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][OD]);?>">
							</div></label>
							<label style="display: block;"><div class="x30">
								<div class="pq_ig"></div>
									<p>instagram.com/</p><input type="text" name="follow[follow_socnet][IG]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][IG]);?>">
							</div></label>
							<label style="display: block;"><div class="x30">
								<div class="pq_rs"></div>
									<p>RSS Url Address</p><input type="text" name="follow[follow_socnet][RSS]" value="<?php echo stripslashes($this->_options[follow][follow_socnet][RSS]);?>">
							</div></label>
							</div>
							<button type="button" class="pq-btn-link btn-bg" onclick="document.getElementById('MoreFollowSocialNetworks').style.display='block';" >More Services</button>
						</div>
						<div class="clear"></div>
							<label style="width: 49%; display: inline-block; margin: 5px 0 0px;">
									<input type="radio" name="follow[animation]" value="bounceInDown" <?php if($this->_options[follow][animation] == 'bounceInDown') echo 'checked';?>  style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Bounce animation</p>
							</label>
							<label style="width: 49%; display: inline-block; margin: 5px 0 0px;">
									<input type="radio" name="follow[animation]" value="fade" <?php if($this->_options[follow][animation] == 'fade' || $this->_options[follow][animation] == '') echo 'checked';?>  style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Fading animation</p>
							</label>
						<hr>
							<div class="pq-sm-12 icons" style="padding-left: 0; margin: 27px 0 0;">
							<label><select id="follow_overlay" name="follow[overlay]">
								    <option value="over_grey" <?php if($this->_options[follow][overlay] == 'over_grey') echo 'selected';?>>Color overlay - Grey</option>
									<option value="over_white" <?php if($this->_options[follow][overlay] == 'over_white' || $this->_options[follow][overlay] == '') echo 'selected';?>>Color overlay - White</option>
									<option value="over_yellow" <?php if($this->_options[follow][overlay] == 'over_yellow') echo 'selected';?>>Color overlay - Yellow</option>
									<option value="over_wormwood" <?php if($this->_options[follow][overlay] == 'over_wormwood') echo 'selected';?>>Color overlay - Wormwood</option>
									<option value="over_blue" <?php if($this->_options[follow][overlay] == 'over_blue') echo 'selected';?>>Color overlay - Blue</option>
									<option value="over_green" <?php if($this->_options[follow][overlay] == 'over_green') echo 'selected';?>>Color overlay - Green</option>
									<option value="over_beige" <?php if($this->_options[follow][overlay] == 'over_beige') echo 'selected';?>>Color overlay - Beige</option>
									<option value="over_red" <?php if($this->_options[follow][overlay] == 'over_red') echo 'selected';?>>Color overlay - Red</option>
									<option value="over_iceblue" <?php if($this->_options[follow][overlay] == 'over_iceblue') echo 'selected';?>>Color overlay - Iceblue</option>
									<option value="over_black" <?php if($this->_options[follow][overlay] == 'over_black') echo 'selected';?>>Color overlay - Black</option>
									<option value="over_skyblue" <?php if($this->_options[follow][overlay] == 'over_skyblue') echo 'selected';?>>Color overlay - Skyblue</option>
									<option value="over_lilac" <?php if($this->_options[follow][overlay] == 'over_lilac') echo 'selected';?>>Color overlay - Lilac</option>
									<option value="over_grey_lt" <?php if($this->_options[follow][overlay] == 'over_grey_lt') echo 'selected';?>>Color overlay - Grey - Light</option>
									<option value="over_white_lt" <?php if($this->_options[follow][overlay] == 'over_white_lt') echo 'selected';?>>Color overlay - White - Light</option>
									<option value="over_yellow_lt" <?php if($this->_options[follow][overlay] == 'over_yellow_lt') echo 'selected';?>>Color overlay - Yellow - Light</option>
									<option value="over_wormwood_lt" <?php if($this->_options[follow][overlay] == 'over_wormwood_lt') echo 'selected';?>>Color overlay - Wormwood - Light</option>
									<option value="over_blue_lt" <?php if($this->_options[follow][overlay] == 'over_blue_lt') echo 'selected';?>>Color overlay - Blue - Light</option>
									<option value="over_green_lt" <?php if($this->_options[follow][overlay] == 'over_green_lt') echo 'selected';?>>Color overlay - Green - Light</option>
									<option value="over_beige_lt" <?php if($this->_options[follow][overlay] == 'over_beige_lt') echo 'selected';?>>Color overlay - Beige - Light</option>
									<option value="over_red_lt" <?php if($this->_options[follow][overlay] == 'over_red_lt') echo 'selected';?>>Color overlay - Red - Light</option>
									<option value="over_iceblue_lt" <?php if($this->_options[follow][overlay] == 'over_iceblue_lt') echo 'selected';?>>Color overlay - Iceblue - Light</option>
									<option value="over_black_lt" <?php if($this->_options[follow][overlay] == 'over_black_lt') echo 'selected';?>>Color overlay - Black - Light</option>
									<option value="over_skyblue_lt" <?php if($this->_options[follow][overlay] == 'over_skyblue_lt') echo 'selected';?>>Color overlay - Skyblue - Light</option>
									<option value="over_lilac_lt" <?php if($this->_options[follow][overlay] == 'over_lilac_lt') echo 'selected';?>>Color overlay - Lilac - Light</option>
									<option value="over_grey_solid" <?php if($this->_options[follow][overlay] == 'over_grey_solid') echo 'selected';?>>Color overlay - Grey - Solid</option>
									<option value="over_white_solid" <?php if($this->_options[follow][overlay] == 'over_white_solid') echo 'selected';?>>Color overlay - White - Solid</option>
									<option value="over_yellow_solid" <?php if($this->_options[follow][overlay] == 'over_yellow_solid') echo 'selected';?>>Color overlay - Yellow - Solid</option>
									<option value="over_wormwood_solid" <?php if($this->_options[follow][overlay] == 'over_wormwood_solid') echo 'selected';?>>Color overlay - Wormwood - Solid</option>
									<option value="over_blue_solid" <?php if($this->_options[follow][overlay] == 'over_blue_solid') echo 'selected';?>>Color overlay - Blue - Solid</option>
									<option value="over_green_solid" <?php if($this->_options[follow][overlay] == 'over_green_solid') echo 'selected';?>>Color overlay - Green - Solid</option>
									<option value="over_beige_solid" <?php if($this->_options[follow][overlay] == 'over_beige_solid') echo 'selected';?>>Color overlay - Beige - Solid</option>
									<option value="over_red_solid" <?php if($this->_options[follow][overlay] == 'over_red_solid') echo 'selected';?>>Color overlay - Red - Solid</option>
									<option value="over_iceblue_solid" <?php if($this->_options[follow][overlay] == 'over_iceblue_solid') echo 'selected';?>>Color overlay - Iceblue - Solid</option>
									<option value="over_black_solid" <?php if($this->_options[follow][overlay] == 'over_black_solid') echo 'selected';?>>Color overlay - Black - Solid</option>
									<option value="over_skyblue_solid" <?php if($this->_options[follow][overlay] == 'over_skyblue_solid') echo 'selected';?>>Color overlay - Skyblue - Solid</option>
									<option value="over_lilac_solid" <?php if($this->_options[follow][overlay] == 'over_lilac_solid') echo 'selected';?>>Color overlay - Lilac - Solid</option>
							</select></label>
							</div>
							
							<div class="clear"></div>
							<div class="pq-sm-6 icons" style="padding-left: 0; margin: 20px 0;">
							<label><div class="pq_box">
								<p>After Sharing Sidebar</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="sharingSideBar[afterProceed][follow]" <?php if((int)$this->_options[sharingSideBar][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Image Sharer</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="imageSharer[afterProceed][follow]" <?php if((int)$this->_options[imageSharer][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Marketing Bar</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeBar[afterProceed][follow]" <?php if((int)$this->_options[subscribeBar][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div></label>
							</div>
							<div class="pq-sm-6 icons" style="padding-right: 0; margin: 20px 0;">
							<label><div class="pq_box">
								<p>After Exit Popup</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeExit[afterProceed][follow]" <?php if((int)$this->_options[subscribeExit][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Contact Form</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="contactUs[afterProceed][follow]" <?php if((int)$this->_options[contactUs][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Call Me Back</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="callMe[afterProceed][follow]" <?php if((int)$this->_options[callMe][afterProceed][follow] == 1) echo 'checked';?>></div>
							</div><label>
							</div>							
							<div style="clear: both;"></div>
																				
							</div>
						<a href="javascript:void(0)" onclick="document.getElementById('After_Sharing').style.display='none';"><div class="pq_close"></div></a>
						</div>
						<a name="Thankyou_Popup"></a><div class="pq-sm-10 pq_more" id="Thankyou_Popup" style="display:none;">
							<h5>More options Thankyou Popup</h5>
							<div class="pq-sm-10" style="width: 83.333333%;">
							<label style="display: block;"><p>Heading</p><input type="text" name="thankPopup[title]" value="<?php echo stripslashes($this->_options[thankPopup][title])?>"></label>
							<label style="display: block;"><p>Text</p><input type="text" name="thankPopup[sub_title]" value="<?php echo stripslashes($this->_options[thankPopup][sub_title])?>"></label>							
							<label style="display: block;"><p>Button Title</p><input type="text" name="thankPopup[buttonTitle]" value="<?php echo stripslashes($this->_options[thankPopup][buttonTitle])?>"></label>							
							<div class="clear"></div>							
							<label style="margin: 10px 0;">
							<select id="thankPopup_img" name="thankPopup[img]" onchange="chagnePopupImg(this.value, 'thankPopupFotoBlock', 'thankPopupCustomFotoBlock');">
								<option value="img_01.png" <?php if($this->_options[thankPopup][img] == 'img_01.png') echo 'selected';?>>Question</option>
								<option value="img_02.png" <?php if($this->_options[thankPopup][img] == 'img_02.png') echo 'selected';?>>Attention</option>
								<option value="img_03.png" <?php if($this->_options[thankPopup][img] == 'img_03.png') echo 'selected';?>>Info</option>
								<option value="img_04.png" <?php if($this->_options[thankPopup][img] == 'img_04.png') echo 'selected';?>>Knowledge</option>
								<option value="img_05.png" <?php if($this->_options[thankPopup][img] == 'img_05.png') echo 'selected';?>>Idea</option>
								<option value="img_06.png" <?php if($this->_options[thankPopup][img] == 'img_06.png') echo 'selected';?>>Talk</option>
								<option value="img_07.png" <?php if($this->_options[thankPopup][img] == 'img_07.png') echo 'selected';?>>News</option>
								<option value="img_08.png" <?php if($this->_options[thankPopup][img] == 'img_08.png') echo 'selected';?>>Megaphone</option>
								<option value="img_09.png" <?php if($this->_options[thankPopup][img] == 'img_09.png') echo 'selected';?>>Gift</option>
								<option value="img_10.png" <?php if($this->_options[thankPopup][img] == 'img_10.png') echo 'selected';?>>Success</option>
								<option value="custom" <?php if($this->_options[thankPopup][img] == 'custom') echo 'selected';?>>Your custom image ...</option>
							</select>
							</label>
							<label style="margin: 10px 0;">
							<div class="img">
								<img id="thankPopupFotoBlock" src="" />
							<input type="text" name="thankPopup[imgUrl]" style="display:none; margin-top: 10px;" id="thankPopupCustomFotoBlock" placeholder="Enter your image URL" value="<?php echo stripslashes($this->_options[thankPopup][imgUrl])?>">
							</div></label>
							<?php
								echo "
								<script>
									chagnePopupImg('".$this->_options[thankPopup][img]."', 'thankPopupFotoBlock', 'thankPopupCustomFotoBlock');
								</script>
								";
							?>
							<div class="clear"></div>
							<label style="width: 49%; display: inline-block; margin: 5px 0 10px;">
									<input type="radio" name="thankPopup[animation]" value="bounceInDown" <?php if($this->_options[thankPopup][animation] == 'bounceInDown') echo 'checked';?> style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Bounce animation</p>
							</label>
							<label style="width: 49%; display: inline-block; margin: 5px 0 10px;">
									<input type="radio" name="thankPopup[animation]" value="fade" <?php if($this->_options[thankPopup][animation] == 'fade' || $this->_options[thankPopup][animation] == '') echo 'checked';?> style="display: inline-block; float: left; margin: 3px 10px 3px 0;">
									<p>Fading animation</p>
							</label>
							<hr>
							<div class="pq-sm-12 icons" style="padding-left: 0; margin: 27px 0 0;">
							<label><select id="subscribeBar_overlay" onchange="subscribeBarPreview();" name="subscribeBar[overlay]">
								    <option value="over_grey" <?php if($this->_options[subscribeBar][overlay] == 'over_grey') echo 'selected';?>>Color overlay - Grey</option>
									<option value="over_white" <?php if($this->_options[subscribeBar][overlay] == 'over_white' || $this->_options[subscribeBar][overlay] == '') echo 'selected';?>>Color overlay - White</option>
									<option value="over_yellow" <?php if($this->_options[subscribeBar][overlay] == 'over_yellow') echo 'selected';?>>Color overlay - Yellow</option>
									<option value="over_wormwood" <?php if($this->_options[subscribeBar][overlay] == 'over_wormwood') echo 'selected';?>>Color overlay - Wormwood</option>
									<option value="over_blue" <?php if($this->_options[subscribeBar][overlay] == 'over_blue') echo 'selected';?>>Color overlay - Blue</option>
									<option value="over_green" <?php if($this->_options[subscribeBar][overlay] == 'over_green') echo 'selected';?>>Color overlay - Green</option>
									<option value="over_beige" <?php if($this->_options[subscribeBar][overlay] == 'over_beige') echo 'selected';?>>Color overlay - Beige</option>
									<option value="over_red" <?php if($this->_options[subscribeBar][overlay] == 'over_red') echo 'selected';?>>Color overlay - Red</option>
									<option value="over_iceblue" <?php if($this->_options[subscribeBar][overlay] == 'over_iceblue') echo 'selected';?>>Color overlay - Iceblue</option>
									<option value="over_black" <?php if($this->_options[subscribeBar][overlay] == 'over_black') echo 'selected';?>>Color overlay - Black</option>
									<option value="over_skyblue" <?php if($this->_options[subscribeBar][overlay] == 'over_skyblue') echo 'selected';?>>Color overlay - Skyblue</option>
									<option value="over_lilac" <?php if($this->_options[subscribeBar][overlay] == 'over_lilac') echo 'selected';?>>Color overlay - Lilac</option>
									<option value="over_grey_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_grey_lt') echo 'selected';?>>Color overlay - Grey - Light</option>
									<option value="over_white_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_white_lt') echo 'selected';?>>Color overlay - White - Light</option>
									<option value="over_yellow_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_yellow_lt') echo 'selected';?>>Color overlay - Yellow - Light</option>
									<option value="over_wormwood_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_wormwood_lt') echo 'selected';?>>Color overlay - Wormwood - Light</option>
									<option value="over_blue_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_blue_lt') echo 'selected';?>>Color overlay - Blue - Light</option>
									<option value="over_green_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_green_lt') echo 'selected';?>>Color overlay - Green - Light</option>
									<option value="over_beige_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_beige_lt') echo 'selected';?>>Color overlay - Beige - Light</option>
									<option value="over_red_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_red_lt') echo 'selected';?>>Color overlay - Red - Light</option>
									<option value="over_iceblue_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_iceblue_lt') echo 'selected';?>>Color overlay - Iceblue - Light</option>
									<option value="over_black_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_black_lt') echo 'selected';?>>Color overlay - Black - Light</option>
									<option value="over_skyblue_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_skyblue_lt') echo 'selected';?>>Color overlay - Skyblue - Light</option>
									<option value="over_lilac_lt" <?php if($this->_options[subscribeBar][overlay] == 'over_lilac_lt') echo 'selected';?>>Color overlay - Lilac - Light</option>
									<option value="over_grey_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_grey_solid') echo 'selected';?>>Color overlay - Grey - Solid</option>
									<option value="over_white_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_white_solid') echo 'selected';?>>Color overlay - White - Solid</option>
									<option value="over_yellow_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_yellow_solid') echo 'selected';?>>Color overlay - Yellow - Solid</option>
									<option value="over_wormwood_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_wormwood_solid') echo 'selected';?>>Color overlay - Wormwood - Solid</option>
									<option value="over_blue_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_blue_solid') echo 'selected';?>>Color overlay - Blue - Solid</option>
									<option value="over_green_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_green_solid') echo 'selected';?>>Color overlay - Green - Solid</option>
									<option value="over_beige_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_beige_solid') echo 'selected';?>>Color overlay - Beige - Solid</option>
									<option value="over_red_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_red_solid') echo 'selected';?>>Color overlay - Red - Solid</option>
									<option value="over_iceblue_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_iceblue_solid') echo 'selected';?>>Color overlay - Iceblue - Solid</option>
									<option value="over_black_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_black_solid') echo 'selected';?>>Color overlay - Black - Solid</option>
									<option value="over_skyblue_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_skyblue_solid') echo 'selected';?>>Color overlay - Skyblue - Solid</option>
									<option value="over_lilac_solid" <?php if($this->_options[subscribeBar][overlay] == 'over_lilac_solid') echo 'selected';?>>Color overlay - Lilac - Solid</option>
							</select></label>
							</div>
							
							<div class="clear"></div>
							<div class="pq-sm-6 icons" style="padding-left: 0; margin: 20px 0;">
							<label><div class="pq_box">
								<p>After Sharing Sidebar</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="sharingSideBar[afterProceed][thank]" <?php if((int)$this->_options[sharingSideBar][afterProceed][thank] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Image Sharer</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="imageSharer[afterProceed][thank]" <?php if((int)$this->_options[imageSharer][afterProceed][thank] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Marketing Bar</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeBar[afterProceed][thank]" <?php if((int)$this->_options[subscribeBar][afterProceed][thank] == 1) echo 'checked';?>></div>
							</div></label>
							</div>
							<div class="pq-sm-6 icons" style="padding-right: 0; margin: 20px 0;">
							<label><div class="pq_box">
								<p>After Exit Popup</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="subscribeExit[afterProceed][thank]" <?php if((int)$this->_options[subscribeExit][afterProceed][thank] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Contact Form</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="contactUs[afterProceed][thank]" <?php if((int)$this->_options[contactUs][afterProceed][thank] == 1) echo 'checked';?>></div>
							</div></label>
							<label><div class="pq_box">
								<p>After Call Me Back</p><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-on bootstrap-switch-id-switch-size bootstrap-switch-animate bootstrap-switch-mini bootstrap-switch-success">
								<input type="checkbox" name="callMe[afterProceed][thank]" <?php if((int)$this->_options[callMe][afterProceed][thank] == 1) echo 'checked';?>></div>
							</div><label>
							</div>							
							<div class="clear"></div>							
							</div>
						<a href="javascript:void(0)" onclick="document.getElementById('Thankyou_Popup').style.display='none';"><div class="pq_close"></div></a>
						</div>
					</div>
					
					  <input type="submit" class="btn_m_red" value="Save changes">
					  <a href="mailto:support@profitquery.com" target="_blank" class="pq_help">Need help?</a>
					  </form>
					</div>
				  </div>
				<a name="AdditionalOptions">
				<div class="pq_uga">
					<h5>Additional Options</h5>
					<form action="<?php echo $this->getSettingsPageUrl();?>#AdditionalOptions" method="post">
					<input type="hidden" name="action" value="editAdditionalOptions">
							<input type="checkbox" name="additionalOptions[enableGA]" <?php if((int)$this->_options[additionalOptions][enableGA] == 1) echo 'checked';?> ><p>Use google analytics</p>
							<input type="submit" value="Save">
					</form>
				</div>
			</div>
<div class="pq-container-fluid" id="free_profitquery" style="padding: 90px 0; margin-top: 80px;">
	<div class="pq-sm-10" style="overflow: hidden; padding: 20px; margin: 30px 0 155px; background: white;">
		<h5>For developer</h5>
			<p>You can bind any enabled profitquery popup for any event on your website.This is wonderfull opportunity to make your website smarter. You can use Thank popup , Share popup, Follow us even Subscribe popup anywhere you want</p>
		<a href="http://profitquery.com/developer.html" target="_blank"><input type="button" class="btn_m_white" value="Learn More"></a>
		</div>
	<div class="pq-sm-12">
		<h4>More Tools from Profitquery</h4>
		<div class="pq-sm-10" style="overflow: hidden; padding: 20px; margin: 30px 0 25px; background: white;">
			<img src="<?php echo plugins_url('images/aio.png', __FILE__);?>" />
			
			<h5>Share + Subscribe + Contact in one Plugin</h5>
			<a href="https://wordpress.org/plugins/share-subscribe-contact-aio-widget/" target="_blank"><input type="button" class="btn_m_white" value="Learn more"></a>
		</div>
		<div class="pq-sm-12 pq-items">
		<div style="overflow: hidden; width: 100%; max-width: 740px; margin: 0 auto;">
			<a href="http://profitquery.com/referral_system.html" target="_blank"><div class="pq-sm-6">
					<img src="<?php echo plugins_url('images/referral_system.png', __FILE__);?>" />
					<h5>Refferal System</h3>
					<a href="http://profitquery.com/referral_system.html" target="_blank"><input type="button" class="btn_m_red" style="width: initial; margin: 12px auto 8px;" value="Learn more"></a>
			</div></a>
			<a href="http://profitquery.com/social_login.html" target="_blank"><div class="pq-sm-6" id="odd">
					<img src="<?php echo plugins_url('images/social_login.png', __FILE__);?>" />
					<h5>Social Login</h5>
					<a href="http://profitquery.com/social_login.html" target="_blank"><input type="button" class="btn_m_red" style="width: initial; margin: 12px auto 8px;" value="Learn more"></a>
			</div></a>
			<a href="http://profitquery.com/trigger_mail.html" target="_blank"><div class="pq-sm-6">
					<img src="<?php echo plugins_url('images/trigger_mail.png', __FILE__);?>" />
					<h5>Trigger Mail</h3>
					<a href="http://profitquery.com/trigger_mail.html" target="_blank"><input type="button" class="btn_m_red" style="width: initial; margin: 12px auto 8px;" value="Learn more"></a>
			</div></a>
			<a href="http://profitquery.com/product_discount.html" target="_blank"><div class="pq-sm-6" id="odd">
					<img src="<?php echo plugins_url('images/product_discount.png', __FILE__);?>" />
					<h5>Product Discount</h5>
					<a href="http://profitquery.com/product_discount.html" target="_blank"><input type="button" class="btn_m_red" style="width: initial; margin: 12px auto 8px;" value="Learn more"></a>
			</div></a>
		</div>	
		</div>
		<div class="pq-sm-10" style="overflow: hidden; padding: 20px; margin: 30px 0 25px; background: white;">
			<img src="<?php echo plugins_url('images/ecom.png', __FILE__);?>" />
			
			<h5>Free Profitquery Widgets for Ecommerce</h5>
			<a href="http://profitquery.com/ecom.html" target="_blank"><input type="button" class="btn_m_white" value="Learn more"></a>
		</div>
		<div class="pq-sm-10" style="overflow: hidden; padding: 20px; margin: 70px 0 20px; background: #f8dde3;">
			<h5 style="color: white; background: #008AFF; width: 100px; margin: 0 auto; line-height: 35px; font-size: 26px;">PRO</h5>
			<h5>Get Profitquery Pro version</h5>
			<a href="http://profitquery.com/promo.html" target="_blank"><input type="button" class="btn_m_red" style="width: initial; margin: 20px auto 8px;" value="Learn more"></a>
		</div>
		<div class="pq-sm-10" style="overflow: hidden; padding: 20px; margin: 30px 0 25px; background: white;">				
			<h5>Write your article. Promote your blog.</h5>
			<p>Write your article. Promote your blog.You can write any article about Profitquery for your customers, friends and <a href="http://profitquery.com/blog.html#send" target="_blank">send </a> for us your link or content. We paste your work on our <a href="http://profitquery.com/blog.html" target="_blank">blog</a>. Use your native language.</p>
		<a href="http://profitquery.com/blog.html#send" target="_blank"><input type="button" class="btn_m_white" value="Send your article"></a>
		</div>
	</div>
</div>
</div>
		<script>
			function changeSubscribeBarEnabled(){											
				if(document.getElementById('subscribeBarEnabledCheckbox').checked){
					document.getElementById('subscribeBarEnabledStyle').className = 'pq-switch-bg pq-on';
					document.getElementById('subscribeBarEnabledText').innerHTML = 'On';					
				} else {
					document.getElementById('subscribeBarEnabledStyle').className = 'pq-switch-bg pq-off';
					document.getElementById('subscribeBarEnabledText').innerHTML = 'Off';
				}
				
				if(!document.getElementById('subscribeBarEnabledCheckbox').checked && !document.getElementById('subscribeExitEnabledCheckbox').checked){
					document.getElementById('mailchimpBlockID').style.display = 'none';
				} else {
					document.getElementById('mailchimpBlockID').style.display = 'block';
				}
			}			
			function changeSubscribeExitEnabled(){											
				if(document.getElementById('subscribeExitEnabledCheckbox').checked){
					document.getElementById('subscribeExitEnabledStyle').className = 'pq-switch-bg pq-on';
					document.getElementById('subscribeExitEnabledText').innerHTML = 'On';																
				} else {
					document.getElementById('subscribeExitEnabledStyle').className = 'pq-switch-bg pq-off';
					document.getElementById('subscribeExitEnabledText').innerHTML = 'Off';											
				}																				
				
				if(!document.getElementById('subscribeBarEnabledCheckbox').checked && !document.getElementById('subscribeExitEnabledCheckbox').checked){
					document.getElementById('mailchimpBlockID').style.display = 'none';
				} else {
					document.getElementById('mailchimpBlockID').style.display = 'block';
				}
			}
			changeSubscribeExitEnabled();								
			changeSubscribeBarEnabled();
		</script>
			<?php
		}       
    }
	
	/**
     * Get the wp domain
     * 
     * @return string
     */
    function getDomain()
    {
        $url     = get_option('siteurl');
        $urlobj  = parse_url($url);
        $domain  = $urlobj['host'];
        $domain  = str_replace('www.', '', $domain);
        return $domain;
    }
}