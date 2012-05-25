<?php
/*
Plugin Name: Custom Content by Country, from Worpit
Plugin URI: http://worpit.com/
Description: Tool for displaying/hiding custom content based on visitors country/location.
Version: 1.0
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

class Worpit_ContentByCountry {
	
	const Ip2NationDbVersion = '20120210';
	const CBC_PluginVersion = '1.0';
	
	const OptionPrefix	= 'worpit_cbc_';
	const Ip2NationDbVersionKey = 'ip2nation_version';
	
	protected function initializeMetaData() {
		add_action( 'admin_init', array( &$this, 'installIp2NationsDb' ) );
	}
	
	public function installIp2NationsDb() {
		
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		$sDbVersion = get_option( self::OptionPrefix.self::Ip2NationDbVersionKey );

		if ( $sDbVersion === self::Ip2NationDbVersion ) {
			return;
		}

		//At this stage, we've determined that the currently installed IP-2-Nation is non-existent or out of date.
		
		if ( !isset( $_GET['CBC_INSTALL_DB'] )  ) {

			echo '
				<div id="message" class="updated">
					<style>
						#message form {
							margin: 0px;
						}
					</style>
					<form method="post" action="index.php?CBC_INSTALL_DB=1">
						<p><strong>The IP-2-Nations data needs to be installed before you can use the <em>Content By Country</em> plugin.</strong>
						<input type="submit" value="Click here to install now (it may take a few seconds - click only ONCE)" name="submit" class="button-primary">
						</p>
					</form>
				</div>
			';

		} else {
			
			$fImportSuccess = $this->importMysqlFile( dirname(__FILE__).'/ip2nation.sql' );
			
			if ( $fImportSuccess ) {

				echo '
				<div id="message" class="updated">
					<style>
						#message form {
							margin: 0px;
						}
					</style>
					<p><strong>Success</strong>: The IP-2-Nations data was automatically installed successfully for the "Content By Country" plugin.</p>
				</div>
				';
			} else {
				
				echo '
				<div id="message" class="updated">
					<style>
						#message form {
							margin: 0px;
						}
					</style>
					<p>The IP-2-Nations data was <strong>NOT</strong> successfully installed. For perfomance reasons, only 1 attempt is made - you will have to do so manually.</p>
				</div>
				';
			}
			update_option( self::OptionPrefix.self::Ip2NationDbVersionKey, self::Ip2NationDbVersion );
		}
			
	}//installIp2NationsDb

	/**
	 * Add desired shortcodes to this array.
	 */
	protected function createShortcodeArray() {
		$this->m_aShortcodes = array(
			'CBC'	=> 	'showContentByCountry'
		);
	}

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
		if( in_array( $sVisitorCountryCode, $aSelectedCountries ) && ( strtolower( $inaAtts['show'] ) == 'y' ) ) {
	 		return do_shortcode($insContent);
		} else if( !in_array( $sVisitorCountryCode, $aSelectedCountries ) && ( strtolower( $inaAtts['show'] ) == 'n' ) ) {
	 		return do_shortcode($insContent);
		} else {
	 		return do_shortcode($inaAtts['message']);
		}

	}//showContentByCountry
	
	/**
	 * Uses a CloudFlare $_SERVER var if possible.
	 */
	public static function getVisitorCountryCode() {
		if ( isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ) {
			$sCode = $_SERVER["HTTP_CF_IPCOUNTRY"];
		} else {
			$dbData = Worpit_ContentByCountry::getVisitorCountryData();
			$sCode = $dbData->code;
		}
		return $sCode;
	}//getVisitorCountryCode
	
	public static function getVisitorCountryName() {

		$dbData = Worpit_ContentByCountry::getVisitorCountryData();
		$sCountry = $dbData->country;

		return $sCountry;
	}//getVisitorCountryName
	
	public static function getVisitorIpAddress() {
	
		$sIpAddress = empty($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["REMOTE_ADDR"] : $_SERVER["HTTP_X_FORWARDED_FOR"];

		if( strpos($sIpAddress, ',') !== false ) {
			$sIpAddress = explode(',', $sIpAddress);
			$sIpAddress = $sIpAddress[0];
		}

		return $sIpAddress;

	}//getVisitorIpAddress
	
	public static function getVisitorCountryData() {

		global $wpdb;

		$sIpAddress = Worpit_ContentByCountry::getVisitorIpAddress();
		
		$sql_query = "
			SELECT `c`.`country`, `c`.`code`
			FROM `ip2nationCountries` AS `c`
			INNER JOIN ip2nation AS `i`
				ON `c`.`code` = `i`.`country`
			WHERE `i`.`ip` < INET_ATON( '%s' )
			ORDER BY `i`.`ip` DESC
			LIMIT 1
		";
		$sql_query = sprintf( $sql_query, $sIpAddress );
		$sCountryData = $wpdb->get_row( $sql_query );
		
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
	
}//CLASS