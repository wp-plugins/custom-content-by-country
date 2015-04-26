<?php
/**
 * Copyright (c) 2014 iControlWP <support@icontrolwp.com>
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

include_once(dirname(__FILE__) . '/worpit-plugins-base.php');
include_once(dirname(__FILE__) . '/icwp-data-processor.php');

class ICWP_CustomContentByCountry extends ICWP_Plugins_Base_CBC {
	
	protected $m_aPluginOptions_EnableSection;
	protected $m_aPluginOptions_AffTagsSection;
	
	protected $m_fIp2NationsDbInstall;
	protected $m_fIp2NationsDbInstallAttempt;
	protected $m_fSubmitCbcMainAttempt;
	
	/**
	 * @var ICWP_CCBC_Processor_GeoLocation
	 */
	protected $oProcessorGeoLocation;

	/**
	 * @param ICWP_CustomContentByCountry_Plugin $oPluginVo
	 */
	public function __construct( ICWP_CustomContentByCountry_Plugin $oPluginVo ) {
		parent::__construct( $oPluginVo );

		register_activation_hook( __FILE__, array( $this, 'onWpActivatePlugin' ) );
		register_deactivation_hook( __FILE__, array( $this, 'onWpDeactivatePlugin' ) );

		$this->sPluginUrl = plugins_url( '/', $this->oPluginVo->getRootFile() );
		$this->m_fIp2NationsDbInstall = false;
		$this->m_fIp2NationsDbInstallAttempt = false;
		$this->m_fSubmitCbcMainAttempt = false;
	}

	public function onWpInit() {
		parent::onWpInit();

		$fCbcEnabled = $this->getOption( 'enable_content_by_country' ) === 'Y';
		$fAmazonAssociate = $this->getOption( 'enable_amazon_associate' ) === 'Y';
		if ( $fCbcEnabled || $fAmazonAssociate ) {
			$oGeoProcessor = $this->loadGeoLocationProcessor();
			$oGeoProcessor->initShortCodes();
		}

		if ( $fCbcEnabled && $this->getOption( 'enable_developer_mode' ) !== 'Y' ) {
			$oGeoProcessor = $this->loadGeoLocationProcessor();
			$oGeoProcessor->setCountryDataCookies();
		}
	}

	public function onWpAdminInit() {
		parent::onWpAdminInit();
		if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' ) {
			$this->installIp2NationsDb();
		}
	}
	
	protected function createPluginSubMenuItems(){
		$this->m_aPluginMenu = array(
			//Menu Page Title => Menu Item name, page ID (slug), callback function for this page - i.e. what to do/load.
			$this->getSubmenuPageTitle( 'Content by Country' ) => array( 'Content by Country', $this->getSubmenuId('main'), 'onDisplayCbcMain' ),
		);
	}
	
	public function onWpAdminNotices() {
		
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
	}

	protected function handlePluginUpgrade() {
		$sPrefix = $this->oPluginVo->getOptionStoragePrefix();
		//Someone clicked the button to acknowledge the update
		if ( isset( $_POST[$sPrefix.'hide_update_notice'] ) && isset( $_POST['worpit_user_id'] ) ) {
			$result = update_user_meta( $_POST['worpit_user_id'], $sPrefix.'current_version', $this->oPluginVo->getVersion() );
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
				array( 'enable_developer_mode',		'',		'Y', 		'checkbox',		'Developer Mode', 'Enable Content By Country Developer Mode', "When enabled, the country code data cookie will NOT be set. Useful if developing/testing features and dynamic content." ),
				array( 'enable_html_off_mode',		'',		'N', 		'checkbox',		'HTML Off', 'HTML Off mode turns off HTML printing by default', "When enabled, the HTML that is normally output is disabled.  Normally the output is surrounded by html SPAN tags, but these are then removed." ),
				array( 'enable_w3tc_compatibility_mode',	'',	'N', 	'checkbox',		'W3TC Compatibility Mode', 'Turns off page caching for shortcodes', "When enabled, 'Custom Content by Country' plugin will turn off page caching for pages that use these shortcodes." ),
			)
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
			)
		);

		$this->m_aAllPluginOptions = array( &$this->m_aPluginOptions_EnableSection, &$this->m_aPluginOptions_AffTagsSection );
		return true;
	}
	
	/** BELOW IS SPECIFIC TO THIS PLUGIN **/
	protected function handlePluginFormSubmit() {

		if ( !$this->isWorpitPluginAdminPage() ) {
			return;
		}
	
		//Was a worpit-cbc form submitted?
		if ( !isset( $_POST[$this->oPluginVo->getOptionStoragePrefix().'all_options_input'] ) ) {
			return;
		}

		//Don't need to run isset() because previous function does this
		switch ( $_GET['page'] ) {
			case $this->getSubmenuId('main'):
				$this->handleSubmit_main( );
				return;
		}
	
	}
	
	protected function handleSubmit_main() {
		$this->m_fSubmitCbcMainAttempt = true;
		$this->updatePluginOptionsFromSubmit( $_POST[$this->oPluginVo->getOptionStoragePrefix().'all_options_input'] );
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
			'plugin_url'		=> $this->sPluginUrl,
			'var_prefix'		=> $this->oPluginVo->getOptionStoragePrefix(),
			'aAllOptions'		=> $aAvailableOptions,
			'all_options_input'	=> $sAllInputOptions,
			'form_action'		=> 'admin.php?page='.$this->getFullParentMenuId().'-main'
		);
		
		$this->display( 'worpit_cbc_main', $aData );
	}

	private function installIp2NationsDb() {
		
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		//Is the install database request flag set and it is a SUBMIT?  INSTALL!
		if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' ) {
			if ( isset( $_POST['cbc_install'] ) && $_POST['cbc_install'] == "1" ) {
				$this->m_fIp2NationsDbInstallAttempt = true;	//used later for admin notices
				$sSqlDir = $this->oPluginVo->getRootDir() . 'inc' . ICWP_DS . 'ip2nation' . ICWP_DS;
				$this->m_fIp2NationsDbInstall = $this->importMysqlFile( $sSqlDir.'ip2nation.sql' );
				$this->m_fIp2NationsDbInstall = $this->m_fIp2NationsDbInstall && $this->importMysqlFile( $sSqlDir.'ip2nationCountries.sql' );
				$this->updateOption( $this->oPluginVo->getIp2NationsDbVersionKey(), $this->oPluginVo->getIp2NationsDbVersion() );
			}
			elseif ( isset( $_POST['cbc_dismiss'] ) ) {
				$this->m_fIp2NationsDbInstallAttempt = false;	//used later for admin notices
				$this->updateOption( $this->oPluginVo->getIp2NationsDbVersionKey(), $this->oPluginVo->getIp2NationsDbVersion() );
			}
		}

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
			$mResult = $wpdb->query($to_run);
		}
		return true;
	}

	private function adminNoticeIp2NationsDb() {
		
		$sDbVersion = $this->getOption( $this->oPluginVo->getIp2NationsDbVersionKey() );
		$sClass = 'updated';
	
		if ( isset( $_GET['show_cbcdb_install'] ) || ( !isset( $_GET['CBC_INSTALL_DB'] ) && $sDbVersion !== $this->oPluginVo->getIp2NationsDbVersion() ) ) {
			//At this stage, we've determined that the currently installed IP-2-Nation is non-existent or out of date.
			$sNotice = '
					<form method="post" action="index.php?CBC_INSTALL_DB=install" id="cbc_install_db">
						<p><strong>The IP-2-Nations data needs to be updated/installed before you can use the <em>Content By Country</em> plugin.</strong>
						<input type="hidden" value="0" name="cbc_install" id="cbc_install" >
						<input type="submit" value="Click here to install now (it may take a few seconds - click only ONCE)"
						name="cbc_submit" id="cbc_submit" class="button-primary" onclick="changeSubmitButton()">
						<input type="submit" value="Dismiss this notice."
						name="cbc_dismiss" id="cbc_dismiss" class="">
						</p>
					</form>
					<script type="text/javascript">
						function changeSubmitButton() {
							var elemSubmit = jQuery("#cbc_submit");
							elemSubmit.val("Please wait, attempting to install data. The page will reload when it finishes ...");
							elemSubmit.attr("disabled", "disabled");
							
							var elemInstallFlag = jQuery("#cbc_install");
							elemInstallFlag.val("1");
							
							var form = jQuery("#cbc_install_db").submit();
						}
					</script>
			';
			$this->getAdminNotice($sNotice, $sClass, true);

		}
		else if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' && $this->m_fIp2NationsDbInstallAttempt ) {
			
			if ( $this->m_fIp2NationsDbInstall ) {
				$sNotice = '<p><strong>Success</strong>: The IP-2-Nations data was automatically installed successfully for the "Content By Country" plugin.</p>';
				$this->getAdminNotice($sNotice, $sClass, true);
			}
			else {
				$sNotice = '<p>The IP-2-Nations data was <strong>NOT</strong> successfully installed. For perfomance reasons, only 1 attempt is ever made - you will have to do so manually.</p>';
				$sClass = 'error';
				$this->getAdminNotice($sNotice, $sClass, true);
			}
		}
		else if ( isset( $_GET['CBC_INSTALL_DB'] ) && $_GET['CBC_INSTALL_DB'] == 'install' && isset( $_POST['cbc_dismiss'] )) {
			$sNotice = '<p>The IP-2-Nations database may not have been updated, so you will need to do so manually if you have not already.</p>';
			$this->getAdminNotice($sNotice, $sClass, true);
		}
		
	}
	
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
	}
	
	private function adminNoticeVersionUpgrade() {

		global $current_user;
		$user_id = $current_user->ID;

		$sCurrentVersion = get_user_meta( $user_id, $this->oPluginVo->getOptionStoragePrefix().'current_version', true );

		if ( $sCurrentVersion !== $this->oPluginVo->getVersion() ) {
			$sNotice = '
					<form method="post" action="admin.php?page='.$this->getFullParentMenuId().'">
						<p><strong>Custom Content By Country</strong> plugin has been updated. Worth checking out the latest docs.
						<input type="hidden" value="1" name="'.$this->oPluginVo->getOptionStoragePrefix().'hide_update_notice" id="'.$this->oPluginVo->getOptionStoragePrefix().'hide_update_notice">
						<input type="hidden" value="'.$user_id.'" name="worpit_user_id" id="worpit_user_id">
						<input type="submit" value="Okay, show me and hide this notice" name="submit" class="button-primary">
						</p>
					</form>
			';
			
			$this->getAdminNotice( $sNotice, 'updated', true );
		}
	}

	/**
	 * @return ICWP_CCBC_Processor_GeoLocation
	 */
	protected function loadGeoLocationProcessor() {
		if ( !isset( $this->oProcessorGeoLocation ) ) {
			require_once( dirname( __FILE__ ).ICWP_DS.'icwp-ccbc-processor.php' );
			$this->oProcessorGeoLocation = new ICWP_CCBC_Processor_GeoLocation();
			$this->oProcessorGeoLocation->setModeHtmlOff( $this->getOption( 'enable_html_off_mode' ) == 'Y' );
			$this->oProcessorGeoLocation->setModeW3tcCompatibility( $this->getOption( 'enable_w3tc_compatibility_mode' ) == 'Y' );
			$this->oProcessorGeoLocation->setModeDeveloper( $this->getOption( 'enable_developer_mode' ) === 'Y' );
			$this->oProcessorGeoLocation->setWpOptionPrefix( $this->oPluginVo->getOptionStoragePrefix() );
		}
		return $this->oProcessorGeoLocation;
	}

}