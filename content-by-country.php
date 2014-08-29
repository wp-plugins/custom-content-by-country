<?php
/*
Plugin Name: Custom Content by Country (from iControlWP)
Plugin URI: http://icwp.io/4p
Description: Tool for displaying/hiding custom content based on visitors country/location.
Version: 2.15.20140816-1
Author: iControlWP
Author URI: http://icwp.io/home
*/

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

require_once( dirname(__FILE__).'/src/icwp-base.php' );
class ICWP_CustomContentByCountry_Plugin extends ICWP_CCBC_Wordpress_Plugin_V1 {

	const Ip2NationDbVersion = '20140816A';

	const Ip2NationDbVersionKey = 'ip2nation_version';

	/**
	 * @var ICWP_CustomContentByCountry_Plugin
	 */
	public static $oInstance;

	/**
	 * @return ICWP_CustomContentByCountry_Plugin
	 */
	public static function GetInstance() {
		if ( !isset( self::$oInstance ) ) {
			self::$oInstance = new self();
		}
		return self::$oInstance;
	}

	/**
	 */
	protected function __construct() {
		if ( empty( self::$sRootFile ) ) {
			self::$sRootFile = __FILE__;
		}
		self::$aFeatures = array(
			'plugin',
			'css',
			'less'
		);
		self::$sParentSlug = 'worpit';
		self::$sVersion = '2.15.20140816-1';
		self::$sPluginSlug = 'cbc';
		self::$sHumanName = 'Custom Content By Country';
		self::$sMenuTitleName = 'Content By Country';
		self::$sTextDomain = 'custom-content-by-country';
		self::$fLoggingEnabled = false;
	}

	/**
	 * @return string
	 */
	public function getIp2NationsDbVersion() {
		return self::Ip2NationDbVersion;
	}

	/**
	 * @return string
	 */
	public function getIp2NationsDbVersionKey() {
		return self::Ip2NationDbVersionKey;
	}
}

include_once( dirname(__FILE__).'/src/icwp-ccbc-main.php' );
$oICWP_CBC = new ICWP_CustomContentByCountry( ICWP_CustomContentByCountry_Plugin::GetInstance() );