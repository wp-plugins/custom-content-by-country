<?php
/*
Plugin Name: Custom Content by Country, from Worpit
Plugin URI: http://worpit.com/
Description: Tool for displaying/hiding custom content based on visitors country/location.
Version: 1.1
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

class Worpit_CustomContentByCountry extends Worpit_Plugins_Base {
	
	const Ip2NationDbVersion = '20120210';
	
	const OptionPrefix	= 'worpit_cbc_';
	const Ip2NationDbVersionKey = 'ip2nation_version';
	
	protected $m_fIp2NationsDbInstall;
	protected $m_fIp2NationsDbInstallAttempt;
	
	protected $m_fUpdateSuccessTracker;
	protected $m_aFailedUpdateOptions;
	
	public function __construct(){
		parent::__construct();

		self::$VERSION		= '1.1'; //SHOULD BE UPDATED UPON EACH NEW RELEASE
		
		self::$PLUGIN_NAME	= basename(__FILE__);
		self::$PLUGIN_PATH	= plugin_basename( dirname(__FILE__) );
		self::$PLUGIN_DIR	= WP_PLUGIN_DIR.DS.self::$PLUGIN_PATH.DS;
		self::$PLUGIN_URL	= WP_PLUGIN_URL.'/'.self::$PLUGIN_PATH.'/';
		
		$this->m_fIp2NationsDbInstall = false;
		$this->m_fIp2NationsDbInstallAttempt = false;
	}//__construct

	public function onWpInit() {
		parent::onWpInit();

		$this->initializeShortcodes();
	}

	public function onWpAdminInit() {
		parent::onWpAdminInit();

		$this->installIp2NationsDb();
	}

	public function onWpPluginsLoaded() {
		parent::onWpPluginsLoaded();
	}
	
	public function onWpAdminMenu() {
		parent::onWpAdminMenu();
	//	add_submenu_page( self::ParentMenuId, $this->getSubmenuPageTitle( 'Bootstrap CSS' ), 'Bootstrap CSS', self::ParentPermissions, $this->getSubmenuId( 'bootstrap-css' ), array( &$this, 'onDisplayIndex' ) );
	//	$this->fixSubmenu();
	}
	
	protected function initializeShortcodes() {
	
		$this->createShortcodeArray();
		
		if ( function_exists('add_shortcode') ) {
			foreach( $this->m_aShortcodes as $shortcode => $function_to_call ) {
				add_shortcode($shortcode, array(&$this, $function_to_call) );
			}//foreach
		}
	}//initializeShortcodes

	/**
	 * Add desired shortcodes to this array.
	 */
	protected function createShortcodeArray() {
		$this->m_aShortcodes = array(
				'CBC'			=> 	'showContentByCountry',
				'CBC_COUNTRY'	=>	'printVisitorCountryName',
				'CBC_CODE'		=>	'printVisitorCountryCode',
				'CBC_IP'		=>	'printVisitorIpAddress',
				'CBC_HELP'		=>	'printHelp'
		);
	}
	
	private function installIp2NationsDb() {
		
		//Do we have admin priviledges?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		$sDbVersion = get_option( self::OptionPrefix.self::Ip2NationDbVersionKey );

		//jump out if the DB version is already up-to-date.
		if ( $sDbVersion === self::Ip2NationDbVersion ) {
			return;
		}

		//At this stage, we've determined that the currently installed IP-2-Nation is non-existent or out of date.
		//Is the install flag set?
		if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' ) {
			$this->m_fIp2NationsDbInstallAttempt = true;	//used later for admin notices
			$this->m_fIp2NationsDbInstall = $this->importMysqlFile( dirname(__FILE__).DS.'inc'.DS.'ip2nation'.DS.'ip2nation.sql' );
			update_option( self::OptionPrefix.self::Ip2NationDbVersionKey, self::Ip2NationDbVersion );
		}
			
	}//installIp2NationsDb
	
	public function onWpAdminNotices() {
		
		$this->adminNoticeIp2NationsDb();
		
	}
	
	private function adminNoticeIp2NationsDb() {
		
		//Do we have admin priviledges?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}
		
		$sDbVersion = get_option( self::OptionPrefix.self::Ip2NationDbVersionKey );
	
		if ( !isset( $_GET['CBC_INSTALL_DB'] ) && $sDbVersion !== self::Ip2NationDbVersion ) {
			//At this stage, we've determined that the currently installed IP-2-Nation is non-existent or out of date.
			$sNotice = '
					<form method="post" action="index.php?CBC_INSTALL_DB=install" id="cbc_install_db">
						<p><strong>The IP-2-Nations data needs to be installed before you can use the <em>Content By Country</em> plugin.</strong>
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
			$this->getAdminNotice($sNotice, true);

		} else if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' && $this->m_fIp2NationsDbInstallAttempt ) {
			
			if ( $this->m_fIp2NationsDbInstall ) {
				$sNotice = '<p><strong>Success</strong>: The IP-2-Nations data was automatically installed successfully for the "Content By Country" plugin.</p>';
				$this->getAdminNotice($sNotice, true);
			} else {
				$sNotice = '<p>The IP-2-Nations data was <strong>NOT</strong> successfully installed. For perfomance reasons, only 1 attempt is ever made - you will have to do so manually.</p>';
				$this->getAdminNotice($sNotice, true);
			}
		}
		
	}//adminNoticeIp2NationsDb
	
	private function getAdminNotice( $insNotice = '', $infPrint = false ) {
		
		$sFullNotice = '
			<div id="message" class="updated">
				<style>
					#message form {
						margin: 0px;
					}
				</style>
				'.$insNotice.'
			</div>
		';
		
		if ( $infPrint ) {
			echo $sFullNotice;
			return true;
		} else {
			return $sFullNotice;
		}
	}//printAdminNotice

	/**
	 * Meat and Potatoes of the plugin
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
	 * Uses a CloudFlare $_SERVER var if possible.
	 */
	public static function getVisitorCountryCode() {
		if ( isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ) {
			$sCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
		} else if ( Worpit_CustomContentByCountry::getVisitorIpAddress() == '127.0.0.1' ) {
			$sCode = 'localhost';
		} else {
			$dbData = Worpit_CustomContentByCountry::getVisitorCountryData();
			$sCode = $dbData->code;
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
	
	static public function getOption( $insKey ) {
		return get_option( self::OptionPrefix.$insKey );
	}
	
	static public function addOption( $insKey, $insValue ) {
		return add_option( self::OptionPrefix.$insKey, $insValue );
	}
	
	static public function updateOption( $insKey, $insValue ) {
		if ( self::getOption( $insKey ) == $insValue ) {
			return true;
		}
		$fResult = update_option( self::OptionPrefix.$insKey, $insValue );
		if ( !$fResult ) {
			$this->m_fUpdateSuccessTracker = false;
			$this->m_aFailedUpdateOptions[] = self::OptionPrefix.$insKey;
		}
	}
	
	static public function deleteOption( $insKey ) {
		return delete_option( self::OptionPrefix.$insKey );
	}
	
}//CLASS

class Worpit_Plugins_Base {

	static public $VERSION;

	static public $PLUGIN_NAME;
	static public $PLUGIN_PATH;
	static public $PLUGIN_DIR;
	static public $PLUGIN_URL;
	static public $PLUGIN_BASENAME;

	const ParentTitle		= 'Worpit';
	const ParentName		= 'Worpit';
	const ParentPermissions	= 'manage_options';
	const ParentMenuId		= 'worpit';
	const VariablePrefix	= 'worpit_';

	const ViewExt			= '.php';
	const ViewDir			= 'views';

	public function __construct() {
		add_action( 'plugins_loaded', array( &$this, 'onWpPluginsLoaded' ) );
		add_action( 'init', array( &$this, 'onWpInit' ), 1 );
		add_action( 'admin_init', array( &$this, 'onWpAdminInit' ) );
		add_action( 'admin_notices', array( &$this, 'onWpAdminNotices' ) );
	}

	protected function fixSubmenu() {
		global $submenu;
		if ( isset( $submenu[self::ParentMenuId] ) ) {
			$submenu[self::ParentMenuId][0][0] = 'Dashboard';
		}
	}

	protected function redirect( $insUrl, $innTimeout = 1 ) {
		echo '
		<script type="text/javascript">
		function redirect() {
			window.location = "'.$insUrl.'";
		}
		var oTimer = setTimeout( "redirect()", "'.($innTimeout * 1000).'" );
		</script>';
	}

	protected function display( $insView, $inaData = array() ) {
		$sFile = dirname(__FILE__).DS.'..'.DS.self::ViewDir.DS.$insView.self::ViewExt;

		if ( !is_file( $sFile ) ) {
			echo "View not found: ".$sFile;
			return false;
		}

		if ( count( $inaData ) > 0 ) {
			extract( $inaData, EXTR_PREFIX_ALL, 'wpv' );
		}

		ob_start();
		include( $sFile );
		$sContents = ob_get_contents();
		ob_end_clean();

		echo $sContents;
		return true;
	}

	protected function getImageUrl( $insImage ) {
		return self::$PLUGIN_URL.'images/'.$insImage;
	}

	protected function getSubmenuPageTitle( $insTitle ) {
		return self::ParentTitle.' - '.$insTitle;
	}

	protected function getSubmenuId( $insId ) {
		return self::ParentMenuId.'-'.$insId;
	}

	public function onWpInit() {  }

	public function onWpAdminInit() {
		add_action( 'admin_menu', array( &$this, 'onWpAdminMenu' ) );
		add_action( 'plugin_action_links', array( &$this, 'onWpPluginActionLinks' ), 10, 4 );
	}

	public function onWpPluginsLoaded() { }

	public function onWpAdminMenu() {
	//	add_menu_page( self::ParentTitle, self::ParentName, self::ParentPermissions, self::ParentMenuId, array( $this, 'onDisplayMainMenu' ), $this->getImageUrl( 'toaster_16x16.png' ) );
	}
	
	public function onWpAdminNotices() {	
	}

	public function onDisplayMainMenu() {
		$aData = array(
			'plugin_url'	=> self::$PLUGIN_URL
		);
		$this->display( 'worpit_index', $aData );
	}

	public function onWpPluginActionLinks( $inaLinks, $insFile ) {
		if ( $insFile == self::$PLUGIN_BASENAME ) {
			$sSettingsLink = '<a href="'.admin_url( "admin.php" ).'?page='.self::ParentMenuId.'">' . __( 'Settings', 'worpit' ) . '</a>';
			array_unshift( $inaLinks, $sSettingsLink );
		}
		return $inaLinks;
	}
	
	/**
	 * Takes an array, an array key, and a default value. If key isn't set, sets it to default.
	 */
	protected function def( &$aSrc, $insKey, $insValue = '' ) {
		if ( !isset( $aSrc[$insKey] ) ) {
			$aSrc[$insKey] = $insValue;
		}
	}
	/**
	 * Takes an array, an array key and an element type. If value is empty, sets the html element
	 * string to empty string, otherwise forms a complete html element parameter.
	 * 
	 * E.g. noEmptyElement( aSomeArray, sSomeArrayKey, "style" )
	 * will return String: style="aSomeArray[sSomeArrayKey]"  or empty string.
	 */
	protected function noEmptyElement( &$inaArgs, $insAttrKey, $insElement = '' ) {
		$sAttrValue = $inaArgs[$insAttrKey];
		$insElement = ( $insElement == '' )? $insAttrKey : $insElement;
		$inaArgs[$insAttrKey] = ( empty($sAttrValue) ) ? '' : ' '.$insElement.'="'.$sAttrValue.'"';
	}
}

new Worpit_CustomContentByCountry( true );