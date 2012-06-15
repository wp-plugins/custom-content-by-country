<?php
/*
Plugin Name: Custom Content by Country, from Worpit
Plugin URI: http://worpit.com/
Description: Tool for displaying/hiding custom content based on visitors country/location.
Version: 2.2
Author: Worpit
Author URI: http://worpit.com/
*/

/**
 * Copyright (c) 2012 Worpit <support@worpit.com>
 * All rights reserved.
 *
 * "Custom Content by Country" is
 * distributed under the GNU General Public License, Version 2,
 * June 1991. Copyright (C) 1989, 1991 Free Software Foundation, Inc., 51 Franklin
 * St, Fifth Floor, Boston, MA 02110, USA
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

define( 'DS', DIRECTORY_SEPARATOR );

include_once( dirname(__FILE__).'/src/worpit-plugins-base.php' );

class Worpit_CustomContentByCountry extends Worpit_Plugins_Base {
	
	const Ip2NationDbVersion = '20120603';
	
	const OptionPrefix	= 'cbc_';
	const Ip2NationDbVersionKey = 'ip2nation_version';
	
	protected $m_aAmazonSitesData;
	protected $m_aAmazonCountryCodeToSiteMap;
	
	protected $m_aPluginOptions_EnableSection;
	protected $m_aPluginOptions_AffTagsSection;
	
	protected $m_fIp2NationsDbInstall;
	protected $m_fIp2NationsDbInstallAttempt;
	protected $m_fSubmitCbcMainAttempt;
	
	public function __construct(){
		parent::__construct();

		register_activation_hook( __FILE__, array( &$this, 'onWpActivatePlugin' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'onWpDeactivatePlugin' ) );
	//	register_uninstall_hook( __FILE__, array( &$this, 'onWpUninstallPlugin' ) );

		self::$VERSION		= '2.2'; //SHOULD BE UPDATED UPON EACH NEW RELEASE
		
		self::$PLUGIN_NAME	= basename(__FILE__);
		self::$PLUGIN_PATH	= plugin_basename( dirname(__FILE__) );
		self::$PLUGIN_DIR	= WP_PLUGIN_DIR.DS.self::$PLUGIN_PATH.DS;
		self::$PLUGIN_URL	= WP_PLUGIN_URL.'/'.self::$PLUGIN_PATH.'/';
		self::$OPTION_PREFIX = self::BaseOptionPrefix . self::OptionPrefix;
		
		$this->m_fIp2NationsDbInstall = false;
		$this->m_fIp2NationsDbInstallAttempt = false;
		$this->m_fSubmitCbcMainAttempt = false;
		
		$this->m_sParentMenuIdSuffix = 'cbc';
	}//__construct

	public function onWpInit() {
		parent::onWpInit();

		$this->initShortcodes();
		
		//Don't init Amazon data if the option is turned off.
		if ( $this->getOption( 'enable_amazon_associate' ) === 'Y') {
			$this->initAmazonData();
		}
	}

	public function onWpAdminInit() {
		parent::onWpAdminInit();

		$this->installIp2NationsDb();
	}
	
	protected function createPluginSubMenuItems(){
		$this->m_aPluginMenu = array(
				//Menu Page Title => Menu Item name, page ID (slug), callback function for this page - i.e. what to do/load.
				$this->getSubmenuPageTitle( 'Content by Country' ) => array( 'Content by Country', $this->getSubmenuId('main'), 'onDisplayCbcMain' ),
			);
	}//createPluginSubMenuItems
	
	public function onWpAdminNotices() {
		
		//Do we have admin priviledges?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$this->adminNoticeIp2NationsDb();
		$this->adminNoticeOptionsUpdated();
		$this->adminNoticeVersionUpgrade();
	}
	
	public function onWpDeactivatePlugin() {
		
		if ( !$this->initPluginOptions() ) {
			return;
		}

		$this->deleteAllPluginDbOptions();
	
	}//onWpDeactivatePlugin
	
	public function onWpActivatePlugin() {
	}//onWpActivatePlugin
	
	protected function handlePluginUpgrade() {
		
		//Someone clicked the button to acknowledge the update
		if ( isset( $_POST['worpit_hide_update_notice'] ) && isset( $_POST['worpit_user_id'] ) ) {
			$result = update_user_meta( $_POST['worpit_user_id'], 'worpit_cbc_current_version', self::$VERSION );
			header( "Location: admin.php?page=".$this->getFullParentMenuId() );
		}
	}
	
	/**
	 * Override for specify the plugin's options
	 */
	protected function initPluginOptions() {
		
		$this->m_aPluginOptions_EnableSection = 	array(
				'section_title' => 'Enable Content By Country Plugin Options',
				'section_options' => array(
					array( 'enable_content_by_country',	'',		'N', 		'checkbox',		'Content By Country', 'Enable Content by Country Feature', "Provides the shortcodes for showing/hiding content based on visitor's location." ),
					array( 'enable_amazon_associate',	'',		'N', 		'checkbox',		'Amazon Associates', 'Enable Amazon Associates Feature', "Provides the shortcode to use Amazon Associate links based on visitor's location." ),
			),
		);
		
		$this->m_aPluginOptions_AffTagsSection = 	array(
				'section_title' => 'Amazon Associate Tags by Region',
				'section_options' => array(
					array( 'afftag_amazon_region_us',		'',		'', 		'text',		'US Associate Tag', 'Specify your Amazon.com Associate Tag here:' ),
					array( 'afftag_amazon_region_canada',	'',		'', 		'text',		'Canada Associate Tag', 'Specify your Amazon.ca Associate Tag here:' ),
					array( 'afftag_amazon_region_uk',		'',		'', 		'text',		'U.K. Associate Tag', 'Specify your Amazon.co.uk Associate Tag here:' ),
					array( 'afftag_amazon_region_france',	'',		'', 		'text',		'France Associate Tag', 'Specify your Amazon.fr Associate Tag here:' ),
					array( 'afftag_amazon_region_germany',	'',		'', 		'text',		'Germany Associate Tag', 'Specify your Amazon.de Associate Tag here:' ),
					array( 'afftag_amazon_region_italy',	'',		'', 		'text',		'Italy Associate Tag', 'Specify your Amazon.it Associate Tag here:' ),
					array( 'afftag_amazon_region_spain',	'',		'', 		'text',		'Spain Associate Tag', 'Specify your Amazon.es Associate Tag here:' ),
					array( 'afftag_amazon_region_japan',	'',		'', 		'text',		'Japan Associate Tag', 'Specify your Amazon.co.jp Associate Tag here:' ),
					array( 'afftag_amazon_region_china',	'',		'', 		'text',		'China Associate Tag', 'Specify your Amazon.cn Associate Tag here:' ),
			),
		);

		$this->m_aAllPluginOptions = array( &$this->m_aPluginOptions_EnableSection, &$this->m_aPluginOptions_AffTagsSection);
		
		return true;
		
	}//initPluginOptions
	
	/** BELOW IS SPECIFIC TO THIS PLUGIN **/
	protected function handlePluginFormSubmit() {

		if ( !$this->isWorpitPluginAdminPage() ) {
			return;
		}
	
		//Was a worpit-cbc form submitted?
		if ( !isset( $_POST[self::$OPTION_PREFIX.'all_options_input'] ) ) {
			return;
		}

		//Don't need to run isset() because previous function does this
		switch ( $_GET['page'] ) {
			case $this->getSubmenuId('main'):
				$this->handleSubmit_main( );
				return;
		}
	
	}//handlePluginFormSubmit
	
	protected function handleSubmit_main() {
		
		$this->m_fSubmitCbcMainAttempt = true;
		$this->updatePluginOptionsFromSubmit( $_POST[self::$OPTION_PREFIX.'all_options_input'] );
	}
	
	/**
	 * For each display, if you're creating a form, define the form action page and the form_submit_id
	 * that you can then use as a guard to handling the form submit.
	 */
	public function onDisplayCbcMain() {
		
		//populates plugin options with existing configuration
		$this->readyAllPluginOptions();
		
		//Specify what set of options are available for this page
		$aAvailableOptions = array( &$this->m_aPluginOptions_EnableSection, &$this->m_aPluginOptions_AffTagsSection) ;
		
		$sAllInputOptions = $this->collateAllFormInputsForOptionsSection( $this->m_aPluginOptions_EnableSection );
		$sAllInputOptions .= ','.$this->collateAllFormInputsForOptionsSection( $this->m_aPluginOptions_AffTagsSection );
		
		$aData = array(
			'plugin_url'		=> self::$PLUGIN_URL,
			'var_prefix'		=> self::$OPTION_PREFIX,
			'aAllOptions'		=> $aAvailableOptions,
			'all_options_input'	=> $sAllInputOptions,
			'form_action'		=> 'admin.php?page='.$this->getFullParentMenuId().'-main'
		);
		
		$this->display( 'worpit_cbc_main', $aData );
	}//onDisplayCbcMain
	
	protected function initShortcodes() {
	
		$this->defineShortcodes();
		
		if ( function_exists('add_shortcode') && !empty( $this->m_aShortcodes ) ) {
			foreach( $this->m_aShortcodes as $shortcode => $function_to_call ) {
				add_shortcode($shortcode, array(&$this, $function_to_call) );
			}//foreach
		}
	}//initShortcodes

	/**
	 * Add desired shortcodes to this array.
	 */
	protected function defineShortcodes() {
		
		$this->m_aShortcodes = array();

		if ( $this->getOption( 'enable_content_by_country' ) === 'Y' ) {
			$this->m_aShortcodes = array(
					'CBC'			=> 	'showContentByCountry',
					'CBC_COUNTRY'	=>	'printVisitorCountryName',
					'CBC_CODE'		=>	'printVisitorCountryCode',
					'CBC_IP'		=>	'printVisitorIpAddress',
					'CBC_HELP'		=>	'printHelp'
			);
		}
		
		if ( $this->getOption( 'enable_amazon_associate' ) === 'Y' ) {
			$this->m_aShortcodes['CBC_AMAZON']	=	'printAmazonLinkByCountry';
		}
	}//defineShortcodes
	
	private function installIp2NationsDb() {
		
		//Do we have admin priviledges?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		$sDbVersion = self::getOption( self::Ip2NationDbVersionKey );

		//jump out if the DB version is already up-to-date.
		if ( $sDbVersion === self::Ip2NationDbVersion ) {
			return;
		}
		
		//At this stage, we've determined that the currently installed IP-2-Nation is non-existent or out of date.
		//Is the install flag set?
		if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' ) {
			$this->m_fIp2NationsDbInstallAttempt = true;	//used later for admin notices
			$this->m_fIp2NationsDbInstall = $this->importMysqlFile( dirname(__FILE__).DS.'inc'.DS.'ip2nation'.DS.'ip2nation.sql' );
			self::updateOption( self::Ip2NationDbVersionKey, self::Ip2NationDbVersion );
		}

	}//installIp2NationsDb
	
	private function adminNoticeIp2NationsDb() {
		
		$sDbVersion = self::getOption( self::Ip2NationDbVersionKey );
		$sClass = 'updated';
	
		if ( !isset( $_GET['CBC_INSTALL_DB'] ) && $sDbVersion !== self::Ip2NationDbVersion ) {
			//At this stage, we've determined that the currently installed IP-2-Nation is non-existent or out of date.
			$sNotice = '
					<form method="post" action="index.php?CBC_INSTALL_DB=install" id="cbc_install_db">
						<p><strong>The IP-2-Nations data needs to be updated/installed before you can use the <em>Content By Country</em> plugin.</strong>
						<input type="submit" value="Click here to install now (it may take a few seconds - click only ONCE)"
						name="cbc_submit" id="cbc_submit" class="button-primary" onclick="changeSubmitButton()">
						</p>
					</form>
					<script type="text/javascript">
						function changeSubmitButton() {
							var elem = jQuery("#cbc_submit");
							elem.val("Please wait, attempting to install data. The page will reload when it finishes ...");
							elem.attr("disabled", "disabled");
							var form = jQuery("#cbc_install_db").submit();
						}
					</script>
			';
			$this->getAdminNotice($sNotice, $sClass, true);

		} else if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' && $this->m_fIp2NationsDbInstallAttempt ) {
			
			if ( $this->m_fIp2NationsDbInstall ) {
				$sNotice = '<p><strong>Success</strong>: The IP-2-Nations data was automatically installed successfully for the "Content By Country" plugin.</p>';
				$this->getAdminNotice($sNotice, $sClass, true);
			} else {
				$sNotice = '<p>The IP-2-Nations data was <strong>NOT</strong> successfully installed. For perfomance reasons, only 1 attempt is ever made - you will have to do so manually.</p>';
				$sClass = 'error';
				$this->getAdminNotice($sNotice, $sClass, true);
			}
		}
		
	}//adminNoticeIp2NationsDb
	
	private function adminNoticeOptionsUpdated() {
		
		//Admin notice for Main Options page submit.
		if ( $this->m_fSubmitCbcMainAttempt ) {
			
			if ( $this->m_fUpdateSuccessTracker ) {
				$sNotice = '<p>Updating CBC Plugin Options was a <strong>Success</strong>.</p>';
				$sClass = 'updated';
			} else {
				$sNotice = '<p>Updating CBC Plugin Options <strong>Failed</strong>.</p>';
				$sClass = 'error';
			}
			$this->getAdminNotice($sNotice, $sClass, true);
		}
	}//adminNoticeOptionsUpdated
	
	private function adminNoticeVersionUpgrade() {

		global $current_user;
		$user_id = $current_user->ID;

		$sCurrentVersion = get_user_meta( $user_id, self::$OPTION_PREFIX.'current_version', true );

		if ( $sCurrentVersion !== self::$VERSION ) {
			$sNotice = '
					<form method="post" action="admin.php?page='.$this->getFullParentMenuId().'">
						<p><strong>Custom Content By Country</strong> plugin has been updated. Worth checking out the latest docs.
						<input type="hidden" value="1" name="worpit_hide_update_notice" id="worpit_hide_update_notice">
						<input type="hidden" value="'.$user_id.'" name="worpit_user_id" id="worpit_user_id">
						<input type="submit" value="Okay, show me and hide this notice" name="submit" class="button-primary">
						</p>
					</form>
			';
			
			$this->getAdminNotice( $sNotice, 'updated', true );
		}
		
	}//adminNoticeVersionUpgrade
	

	/**
	 * Meat and Potatoes of the CBC plugin
	 * 
	 * By default, $insContent will be "shown" for whatever countries are specified.
	 * 
	 * Alternatively, set to 'n' if you want to hide.
	 * 
	 * Logic is: if visitor is coming from a country in the 'country' list and show='y', then show the content.
	 * OR
	 * If the visitor is not from a country in the 'country' list and show='n', then show the content.
	 * 
	 * Otherwise display 'message' if defined.
	 * 
	 * 'message' is displayed where the the content isn't displayed.
	 * 
	 * @param $inaAtts
	 * @param $insContent
	 */
	public function showContentByCountry( $inaAtts = array(), $insContent = '' ) {

		$this->def( &$inaAtts, 'id' );
		$this->def( &$inaAtts, 'style' );
		$this->noEmptyElement( $inaAtts, 'id' );
		$this->noEmptyElement( $inaAtts, 'style' );
		
		$this->def( &$inaAtts, 'country', '' );
		$this->def( &$inaAtts, 'show', 'y' );		//defaults to displaying content
		$this->def( &$inaAtts, 'message', '' );		//defaults to no message
		
		if( $inaAtts['country'] == '' ) {
			return do_shortcode( $insContent );
		}

		$inaAtts['country'] = str_replace(' ', '', $inaAtts['country']);
		$aSelectedCountries = explode(',', $inaAtts['country']);
		$aSelectedCountries = array_map( 'strtolower', $aSelectedCountries );
		
		$sVisitorCountryCode = strtolower( $this->getVisitorCountryCode() );

		//Print nothing if the user is in one of the countries specified and you have set show='n'
		$sOutput;
		if( in_array( $sVisitorCountryCode, $aSelectedCountries ) && ( strtolower( $inaAtts['show'] ) == 'y' ) ) {
	 		$sOutput = do_shortcode($insContent);
		} else if( !in_array( $sVisitorCountryCode, $aSelectedCountries ) && ( strtolower( $inaAtts['show'] ) == 'n' ) ) {
	 		$sOutput = do_shortcode($insContent);
		} else {
	 		$sOutput = do_shortcode($inaAtts['message']);
		}

		return '<span class="cbc_content"'
					.$inaAtts['style']
					.$inaAtts['id'].'>'.$sOutput.'</span>';

	}//showContentByCountry
	
	/**
	 * Uses a CloudFlare $_SERVER var if available.
	 */
	public static function getVisitorCountryCode() {
		
		$sCode = 'us';
		
		if ( isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ) {
			$sCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
		} else if ( Worpit_CustomContentByCountry::getVisitorIpAddress() == '127.0.0.1' ) {
			$sCode = 'localhost';
		} else {
			$dbData = Worpit_CustomContentByCountry::getVisitorCountryData();
			if ( !is_null($dbData) ) {
				$sCode = $dbData->code;
			}
		}
		return $sCode;
	}//getVisitorCountryCode
	
	public function printVisitorCountryCode( $inaAtts = array() ) {

		$this->def( &$inaAtts, 'id' );
		$this->def( &$inaAtts, 'style' );
		$this->noEmptyElement( $inaAtts, 'id' );
		$this->noEmptyElement( $inaAtts, 'style' );

		return '<span class="cbc_countrycode"'
					.$inaAtts['style']
					.$inaAtts['id'].'>'.$this->getVisitorCountryCode().'</span>';
	}

	public static function getVisitorCountryName() {
		
		if ( Worpit_CustomContentByCountry::getVisitorIpAddress() == '127.0.0.1' ) {
			$sCountry = 'localhost';
		} else {
			$dbData = Worpit_CustomContentByCountry::getVisitorCountryData();
			$sCountry = $dbData->country;
		}

		return $sCountry;

	}//getVisitorCountryName
	
	public function printVisitorCountryName( $inaAtts = array() ) {

		$this->def( &$inaAtts, 'id' );
		$this->def( &$inaAtts, 'style' );
		$this->noEmptyElement( $inaAtts, 'id' );
		$this->noEmptyElement( $inaAtts, 'style' );

		return '<span class="cbc_country"'
					.$inaAtts['style']
					.$inaAtts['id'].'>'.$this->getVisitorCountryName().'</span>';
	}
	
	public static function getVisitorIpAddress() {
	
		$sIpAddress = empty($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["REMOTE_ADDR"] : $_SERVER["HTTP_X_FORWARDED_FOR"];

		if( strpos($sIpAddress, ',') !== false ) {
			$sIpAddress = explode(',', $sIpAddress);
			$sIpAddress = $sIpAddress[0];
		}

		return $sIpAddress;

	}//getVisitorIpAddress
	
	public function printVisitorIpAddress( $inaAtts = array() ) {

		$this->def( &$inaAtts, 'id' );
		$this->def( &$inaAtts, 'style' );
		$this->noEmptyElement( $inaAtts, 'id' );
		$this->noEmptyElement( $inaAtts, 'style' );

		return '<span class="cbc_ip"'
					.$inaAtts['style']
					.$inaAtts['id'].'>'.$this->getVisitorIpAddress().'</span>';
	}
	
	public static function getVisitorCountryData() {
		
		global $wpdb;

		$sIpAddress = Worpit_CustomContentByCountry::getVisitorIpAddress();
		
		$sSqlQuery = "
			SELECT `c`.`country`, `c`.`code`
			FROM `ip2nationCountries` AS `c`
			INNER JOIN ip2nation AS `i`
				ON `c`.`code` = `i`.`country`
			WHERE `i`.`ip` < INET_ATON( '%s' )
			ORDER BY `i`.`ip` DESC
			LIMIT 1
		";
		$sSqlQuery = sprintf( $sSqlQuery, $sIpAddress );
		$sCountryData = $wpdb->get_row( $sSqlQuery );
		
		return $sCountryData;

	}//getVisitorCountryData
	
	
	/** AMAZON FUNCTIONALITY **/
	
	protected function initAmazonData() {
		
		//Defines all the currently existing Amazon sites, their domain and their database option key
		$this->m_aAmazonSitesData = array(
			'global'	=>	array( 'com',		'afftag_amazon_region_us'		),
			'ca'		=>	array( 'ca',		'afftag_amazon_region_canada'	),
			'uk'		=>	array( 'co.uk',		'afftag_amazon_region_uk'		),
			'fr'		=>	array( 'fr',		'afftag_amazon_region_france'	),
			'de'		=>	array( 'de',		'afftag_amazon_region_germany'	),
			'it'		=>	array( 'it',		'afftag_amazon_region_italy'	),
			'es'		=>	array( 'es',		'afftag_amazon_region_spain'	),
			'jp'		=>	array( 'co.jp',		'afftag_amazon_region_japan'	),
			'cn'		=>	array( 'cn',		'afftag_amazon_region_china'	),
		);
		
		//Map country codes that don't exist to other Amazon Sites 
		$this->m_aAmazonCountryCodeToSiteMap = array(
			//country code	//Amazon site
			'us'			=>	'global',	//US is the default
			'ie'			=>	'uk',
		);
		
	}//initAmazonData
	
	/**
	 * The Shortcode function for CBC_AMAZON
	 * 
	 * @param unknown_type $inaAtts
	 * @param unknown_type $insContent
	 */
	public function printAmazonLinkByCountry( $inaAtts = array(), $insContent = '' ) {
	
		$this->def( &$inaAtts, 'item' );
		$this->def( &$inaAtts, 'text', $insContent );
		$this->def( &$inaAtts, 'asin' );
		$this->def( &$inaAtts, 'country' );
		
		$sAsinToUse;
		
		if ($inaAtts['asin'] != '') {
			$sAsinToUse = $inaAtts['asin'];
		} else {
			$inaAtts['item'] = strtolower($inaAtts['item']);
			if ( array_key_exists($inaAtts['item'], $this->m_aPreselectedAffItems ) ) {
				$sAsinToUse = $this->m_aPreselectedAffItems[ $inaAtts['item'] ];
			} else {
				return ''; //ASIN is undefined or the "item" does not exist.
			}
		}

		if ( empty($inaAtts['country']) ) {
			$sLink = $this->buildAffLinkFromAsinOnly( $sAsinToUse );
		} else {
			$sLink = $this->buildAffLinkFromCountryCode( $sAsinToUse, $inaAtts['country'] );
		}
		
		$sOutputText = '<a class="cbc_amazon_link" href="'.$sLink.'" target="_blank">'.do_shortcode($inaAtts['text']).'</a>';
		
		return $sOutputText;
		
	}//printAmazonLinkByCountry
	
	public function buildAffLinkFromAsinOnly( $insAsin ) {
		
		//Default country code to US. (amazon.com)
		$sCountryCode = strtolower( $this->getVisitorCountryCode() );
		
		return $this->buildAffLinkFromCountryCode( $insAsin, $sCountryCode );

	}//buildAffLinkFromAsinOnly

	/**
	 * Given the country code and the product ASIN code, returns an Amazon link.
	 * 
	 * If the country code isn't found in the country code mapping, 'global' (amazon.com) is used.
	 * 
	 * @param unknown_type $insCountryCode
	 * @param unknown_type $insAsin
	 */
	public function buildAffLinkFromCountryCode( $insAsin, $insCountryCode ) {
		
		$sAmazonSiteCode = 'global';	//the default: amazon.com
		
		if ( array_key_exists($insCountryCode, $this->m_aAmazonCountryCodeToSiteMap) ) {
			
			//special country code mapping that has been provisioned for. e.g. ie => uk amazon site
			$sAmazonSiteCode = $this->m_aAmazonCountryCodeToSiteMap[$insCountryCode];
			
		} else if ( array_key_exists($insCountryCode, $this->m_aAmazonSitesData) ) {
			
			$sAmazonSiteCode = $insCountryCode;
			
		}
				
		return $this->buildAffLinkFromAmazonSite( $insAsin, $sAmazonSiteCode );

	}//buildAffLinkFromCountryCode
	
	/**
	 * Give it an Amazon site (defaults to "global") and an ASIN and it will create it.
	 * 
	 * @param $insAmazonSite
	 * @param $insAsin
	 */
	public function buildAffLinkFromAmazonSite( $insAsin = '', $insAmazonSite = 'global' ) {

		if ( !array_key_exists($insAmazonSite, $this->m_aAmazonSitesData) ) {
			$insAmazonSite = 'global';
		}
		
		list( $sAmazonDomain, $sAssociateIdTag ) = $this->m_aAmazonSitesData[$insAmazonSite];
		
		$sAssociateIdTag = $this->getOption( $sAssociateIdTag );
		
		return $this->buildAffLinkAmazon( $insAsin, $sAmazonDomain, $sAssociateIdTag );
		
	}//buildAffLinkFromAmazonSite
	
	/**
	 * The most basic link builder. 
	 */
	public static function buildAffLinkAmazon( $insAsin = '', $insAmazonDomain = 'com', $insAffIdTag = '' ) {

		$sLink  = 'http://www.amazon.'.$insAmazonDomain;
		$sLink .= '/dp/'.$insAsin.'/?tag='.$insAffIdTag.'&creativeASIN='.$insAsin;
		
		return $sLink;
	}
	
	private function importMysqlFile( $insFilename ) {
		
		global $wpdb;
		
		if (!file_exists($insFilename)) {
			return false;	
		}
		
		$aSqlLines = file( $insFilename );
		if ( !is_array($aSqlLines) ) {
			return false;
		}
		
		$aSqlStartTerms = array('INSERT', 'UPDATE', 'DELETE', 'DROP', 'GRANT', 'REVOKE', 'CREATE', 'ALTER');
		$aQueries = array();
		foreach ( $aSqlLines as $sLine ) {
			$sLine = trim( $sLine );
			if ( preg_match( "/^(".implode( '|', $aSqlStartTerms ).")\s+/i", $sLine ) ) {
				if ( !empty( $sNewQuery ) ) {
					$aQueries[] = $sNewQuery;
					$sNewQuery = '';
				}
				$sNewQuery = $sLine;
			}
			else {
				$sNewQuery .= $sLine;
			}
		}
		
		if ( !empty( $sNewQuery ) ) {
			$aQueries[] = $sNewQuery;
		}
		
		foreach ($aQueries as $to_run) {
			$wpdb->query($to_run);
		}
		
		return true;
	}//mysql_import
	
}//CLASS


new Worpit_CustomContentByCountry( );