<?php
/**
 * Copyright (c) 2014 iControlWP <support@icontrolwp.com>
 * All rights reserved.
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

class ICWP_CCBC_Processor_GeoLocation_V1 {

	const CbcDataCountryNameCookie = 'cbc_country_name';
	const CbcDataCountryCodeCookie = 'cbc_country_code';

	protected $oDbCountryData;

	/**
	 * @var boolean
	 */
	protected $fHtmlOffMode = false;

	/**
	 * @var boolean
	 */
	protected $fW3tcCompatibilityMode = false;

	/**
	 * @var boolean
	 */
	protected $fDeveloperMode = false;

	/**
	 * @var string
	 */
	protected $sWpOptionPrefix = '';

	public function __construct() { }

	/**
	 * @param $fHtmlOff
	 */
	public function setModeHtmlOff( $fHtmlOff ) {
		$this->fHtmlOffMode = $fHtmlOff;
	}

	/**
	 * @param $fOn
	 */
	public function setModeW3tcCompatibility( $fOn ) {
		$this->fW3tcCompatibilityMode = $fOn;
	}

	/**
	 * @param $fOn
	 */
	public function setModeDeveloper( $fOn ) {
		$this->fDeveloperMode = $fOn;
	}

	/**
	 * @param $sPrefix
	 */
	public function setWpOptionPrefix( $sPrefix ) {
		$this->sWpOptionPrefix = $sPrefix;
	}

	public function initShortCodes() {

		$aShortCodeMapping = array(
			'CBC'			=> 	'sc_printContentByCountry',
			'CBC_COUNTRY'	=>	'sc_printVisitorCountryName',
			'CBC_CODE'		=>	'sc_printVisitorCountryCode',
			'CBC_IP'		=>	'sc_printVisitorIpAddress',
			'CBC_AMAZON'	=>	'sc_printAmazonLinkByCountry'
//			'CBC_HELP'		=>	'printHelp',
		);

		if ( function_exists( 'add_shortcode' ) && !empty( $aShortCodeMapping ) ) {
			foreach( $aShortCodeMapping as $sShortCode => $sCallbackFunction ) {
				if ( is_callable( array( $this, $sCallbackFunction ) ) ) {
					add_shortcode( $sShortCode, array( $this, $sCallbackFunction ) );
				}
			}
		}
	}

	/**
	 * The Shortcode function for CBC_AMAZON
	 *
	 * @param array $inaAtts
	 * @param string $insContent
	 * @return string
	 */
	public function sc_printAmazonLinkByCountry( $inaAtts = array(), $insContent = '' ) {

		$this->def( $inaAtts, 'item' );
		$this->def( $inaAtts, 'text', $insContent );
		$this->def( $inaAtts, 'asin' );
		$this->def( $inaAtts, 'country' );

		if ( !empty( $inaAtts['asin'] ) ) {
			$sAsinToUse = $inaAtts['asin'];
		}
		else {
			$inaAtts['item'] = strtolower( $inaAtts['item'] );

			if ( array_key_exists( $inaAtts['item'], $this->m_aPreselectedAffItems ) ) {
				$sAsinToUse = $this->m_aPreselectedAffItems[ $inaAtts['item'] ];
			}
			else {
				return ''; //ASIN is undefined or the "item" does not exist.
			}
		}

		if ( empty( $inaAtts['country'] ) ) {
			$sLink = $this->buildAffLinkFromAsinOnly( $sAsinToUse );
		}
		else {
			$sLink = $this->buildAffLinkFromCountryCode( $sAsinToUse, $inaAtts['country'] );
		}

		$sOutputText = '<a class="cbc_amazon_link" href="%s" target="_blank">%s</a>';
		return sprintf( $sOutputText,
			$sLink,
			do_shortcode( $inaAtts['text'] )
		);
	}

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
	 * @param $aParams
	 * @param string $sContent
	 * @return string
	 */
	public function sc_printContentByCountry( $aParams = array(), $sContent = '' ) {

		$this->def( $aParams, 'country', '' );
		$this->def( $aParams, 'show', 'y' );		//defaults to displaying content
		$this->def( $aParams, 'message', '' );		//defaults to no message

		$aParams['country'] = str_replace( ' ', '', strtolower( $aParams['country'] ) );
		if( empty( $aParams['country'] ) ) {
			return do_shortcode( $sContent );
		}

		$aSelectedCountries = explode( ',', $aParams['country'] );
		//FIX for use "iso_code_2" db column instead of "code"
		if ( in_array( 'uk', $aSelectedCountries ) ) {
			$aSelectedCountries[] = 'gb';
		}
		$sVisitorCountryCode = strtolower( $this->getVisitorCountryCode() );

		$fIsVisitorFromSelectedCountries = in_array( $sVisitorCountryCode, $aSelectedCountries );

		// we default to show
		$fDoShowVisitorContentSetting = strtolower( $aParams['show'] ) != 'n';
		$fShowContent = true;
		if ( !$fDoShowVisitorContentSetting && $fIsVisitorFromSelectedCountries ) {
			$fShowContent = false;
		}
		if ( $fDoShowVisitorContentSetting && !$fIsVisitorFromSelectedCountries ) {
			$fShowContent = false;
		}

		$sOutput = do_shortcode( $fShowContent ? $sContent : $aParams['message'] );

		$this->def( $aParams, 'class', 'cbc_content' );
		return $this->printShortcodeHtml( $aParams, $sOutput );

	}

	/**
	 * @param array $aParams
	 * @return string
	 */
	public function sc_printVisitorCountryCode( $aParams = array() ) {
		$this->def( $aParams, 'class', 'cbc_countrycode' );
		return $this->printShortCodeHtml( $aParams, $this->getVisitorCountryCode() );
	}

	/**
	 * @param array $aParams
	 * @return string
	 */
	public function sc_printVisitorCountryName( $aParams = array() ) {
		$this->def( $aParams, 'class', 'cbc_country' );
		return $this->printShortcodeHtml( $aParams, $this->getVisitorCountryName() );
	}

	/**
	 * @param array $aParams
	 * @return string
	 */
	public function sc_printVisitorIpAddress( $aParams = array() ) {
		$oDp = $this->loadDataProcessor();
		$this->def( $aParams, 'class', 'cbc_ip' );
		return $this->printShortcodeHtml( $aParams, $oDp->GetVisitorIpAddress( false ) );
	}

	/**
	 * @param $aParams
	 * @param string $sContent
	 * @return string
	 */
	private function printShortCodeHtml( &$aParams, $sContent ) {
		$this->handleW3tcCompatibiltyMode();

		$this->def( $aParams, 'html', '' );
		$this->def( $aParams, 'id' );
		$this->def( $aParams, 'style' );
		$this->noEmptyElement( $aParams, 'id' );
		$this->noEmptyElement( $aParams, 'style' );
		$this->noEmptyElement( $aParams, 'class' );

		if ( $this->getHtmlIsOff( $aParams['html'] ) || empty( $sContent )  ) {
			$sReturnContent = $sContent;
		}
		else {
			$aParams['html'] = empty($aParams['html'])? 'span' : $aParams['html'];
			$sReturnContent = '<'.$aParams['html']
				.$aParams['style']
				.$aParams['class']
				.$aParams['id'].'>'.$sContent.'</'.$aParams['html'].'>';
		}

		return trim( $sReturnContent );
	}

	/**
	 * @return string
	 */
	public function getVisitorCountryCode() {

		$oDp = $this->loadDataProcessor();

		//Get the CloudFlare country if it's set
		$sCode = $oDp->FetchServer( 'HTTP_CF_IPCOUNTRY' );
		if ( !empty( $sCode ) ) {
			return $sCode;
		}

		// Use Cookies if developer mode is off.
		if ( !$this->fDeveloperMode ) {
			$sCode = $oDp->FetchCookie( self::CbcDataCountryCodeCookie );
			if ( !empty( $sCode ) ) {
				return $sCode;
			}
		}

		if ( $oDp->GetVisitorIpAddress( false ) == '127.0.0.1' ) {
			return 'localhost';
		}

		$oVisitorData = $this->loadVisitorCountryData();
		if ( !empty( $oVisitorData->iso_code_2 ) ) {
			return $oVisitorData->iso_code_2;
		}

		return 'us'; //defaults to US.
	}

	/**
	 * @return null|string
	 */
	public function getVisitorCountryName() {

		$oDp = $this->loadDataProcessor();

		if ( $oDp->GetVisitorIpAddress( false ) == '127.0.0.1' ) {
			return 'localhost';
		}

		if ( !$this->fDeveloperMode ) {
			$sCookieCountry = $oDp->FetchCookie( self::CbcDataCountryNameCookie );
			if ( !empty( $sCookieCountry ) ) {
				return $sCookieCountry;
			}
		}

		$oData = $this->loadVisitorCountryData();
		if ( isset( $oData->country ) ) {
			return $oData->country;
		}
		return null;
	}

	/**
	 * @return object
	 */
	protected function loadVisitorCountryData() {

		if ( isset( $this->oDbCountryData ) ) {
			return $this->oDbCountryData;
		}

		$oDp = $this->loadDataProcessor();
		$sIpAddress = $oDp->GetVisitorIpAddress( false );

		$sSqlQuery = "
			SELECT `c`.`country`, `c`.`code`, `c`.`iso_code_2`
			FROM `ip2nationCountries` AS `c`
			INNER JOIN ip2nation AS `i`
				ON `c`.`code` = `i`.`country`
			WHERE `i`.`ip` < INET_ATON( '%s' )
			ORDER BY `i`.`ip` DESC
			LIMIT 1
		";
		$sSqlQuery = sprintf( $sSqlQuery, $sIpAddress );

		global $wpdb;
		$this->oDbCountryData = $wpdb->get_row( $sSqlQuery );
		return $this->oDbCountryData;
	}

	/**
	 * @param object|null $oCountryData
	 */
	public function setCountryDataCookies( $oCountryData = null ) {

		if ( is_null( $oCountryData ) ) {
			$oCountryData = $this->loadVisitorCountryData();
		}

		$nTimeToExpire = time()+24*3600;
		$oDp = $this->loadDataProcessor();

		//set the cookie for future reference if it hasn't been set yet.
		if ( !$oDp->FetchCookie( self::CbcDataCountryNameCookie ) && isset( $oCountryData->country ) ) {
			setcookie( self::CbcDataCountryNameCookie, $oCountryData->country, $nTimeToExpire, COOKIEPATH, COOKIE_DOMAIN, false );
			$_COOKIE[ self::CbcDataCountryNameCookie ] = $oCountryData->country;
		}

		//set the cookie for future reference if it hasn't been set yet.
		if ( !$oDp->FetchCookie( self::CbcDataCountryCodeCookie ) && isset( $oCountryData->code ) ) {
			setcookie( self::CbcDataCountryCodeCookie, $oCountryData->code, $nTimeToExpire, COOKIEPATH, COOKIE_DOMAIN, false );
			$_COOKIE[ self::CbcDataCountryCodeCookie ] = $oCountryData->code;
		}
	}

	/**
	 * @return ICWP_CCBC_DataProcessor
	 */
	public function loadDataProcessor() {
		if ( !class_exists('ICWP_CCBC_DataProcessor') ) {
			require_once( dirname(__FILE__).'/icwp-data-processor.php' );
		}
		return ICWP_CCBC_DataProcessor::GetInstance();
	}

	/**
	 * @param string $sKey
	 * @return mixed
	 */
	protected function getOption( $sKey ) {
		return get_option( $this->sWpOptionPrefix.$sKey );
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
	 * will return String: style="aSomeArray[sSomeArrayKey]" or empty string.
	 */
	protected function noEmptyElement( &$inaArgs, $insAttrKey, $insElement = '' ) {
		$sAttrValue = $inaArgs[$insAttrKey];
		$insElement = ( $insElement == '' )? $insAttrKey : $insElement;
		$inaArgs[$insAttrKey] = ( empty($sAttrValue) ) ? '' : ' '.$insElement.'="'.$sAttrValue.'"';
	}

	/**
	 */
	private function handleW3tcCompatibiltyMode() {
		if ( $this->fW3tcCompatibilityMode && !defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
	}

	/** AMAZON **/

	/**
	 * @param $sAsin
	 * @return string
	 */
	public function buildAffLinkFromAsinOnly( $sAsin ) {
		//Default country code to US. (amazon.com)
		$sCountryCode = strtolower( $this->getVisitorCountryCode() );
		return $this->buildAffLinkFromCountryCode( $sAsin, $sCountryCode );
	}

	/**
	 * Given the country code and the product ASIN code, returns an Amazon link.
	 *
	 * If the country code isn't found in the country code mapping, 'global' (amazon.com) is used.
	 *
	 * @param $sAsin
	 * @param $sCountryCode
	 * @return string
	 */
	public function buildAffLinkFromCountryCode( $sAsin, $sCountryCode ) {

		$sAmazonSiteCode = 'global';	//the default: amazon.com
		$aAmazonCountryCodeToSiteMap = $this->getAmazonCountryCodeToSiteMap();
		$aAmazonSitesData = $this->getAmazonSitesData();

		if ( array_key_exists( $sCountryCode, $aAmazonCountryCodeToSiteMap ) ) {
			//special country code mapping that has been provisioned for. e.g. ie => uk amazon site
			$sAmazonSiteCode = $aAmazonCountryCodeToSiteMap[$sCountryCode];
		}
		else if ( array_key_exists( $sCountryCode, $aAmazonSitesData ) ) {
			$sAmazonSiteCode = $sCountryCode;
		}

		return $this->buildAffLinkFromAmazonSite( $sAsin, $sAmazonSiteCode );
	}

	/**
	 * Give it an Amazon site (defaults to "global") and an ASIN and it will create it.
	 *
	 * @param string $sAsin
	 * @param string $sAmazonSite
	 * @return string
	 */
	public function buildAffLinkFromAmazonSite( $sAsin = '', $sAmazonSite = 'global' ) {
		$aAmazonSitesData = $this->getAmazonSitesData();

		if ( !array_key_exists( $sAmazonSite, $aAmazonSitesData ) ) {
			$sAmazonSite = 'global';
		}

		list( $sAmazonDomain, $sAssociateIdTag ) = $aAmazonSitesData[$sAmazonSite];
		$sAssociateIdTag = $this->getOption( $sAssociateIdTag );
		return $this->buildAffLinkAmazon( $sAsin, $sAmazonDomain, $sAssociateIdTag );
	}

	/**
	 * The most basic link builder.
	 *
	 * @param string $sAsin
	 * @param string $sAmazonDomain
	 * @param string $sAffIdTag
	 * @return string
	 */
	protected function buildAffLinkAmazon( $sAsin = '', $sAmazonDomain = 'com', $sAffIdTag = '' ) {

		$sLink = 'http://www.amazon.%s/dp/%s/?tag=%s&creativeASIN=%s';
		return sprintf( $sLink,
			$sAmazonDomain,
			$sAsin,
			$sAffIdTag,
			$sAsin
		);
	}

	/**
	 * @param string $sHtmlVar
	 * @return bool
	 */
	private function getHtmlIsOff( $sHtmlVar = '' ) {

		// Basically the local html directive will always override the plugin global setting
		if ( !empty( $sHtmlVar ) ) {
			return ( strtolower( $sHtmlVar ) == 'none' );
		}

		return $this->fHtmlOffMode;
	}

	/**
	 * @return array
	 */
	private function getAmazonCountryCodeToSiteMap() {
		return array(
			//country code	//Amazon site
			'us'			=>	'global',	//US is the default
			'ie'			=>	'uk',
		);
	}

	/**
	 * @return array
	 */
	private function getAmazonSitesData() {
		return array(
			'global'	=>	array( 'com',		'afftag_amazon_region_us'		),
			'ca'		=>	array( 'ca',		'afftag_amazon_region_canada'	),
			'uk'		=>	array( 'co.uk',		'afftag_amazon_region_uk'		),
			'fr'		=>	array( 'fr',		'afftag_amazon_region_france'	),
			'de'		=>	array( 'de',		'afftag_amazon_region_germany'	),
			'it'		=>	array( 'it',		'afftag_amazon_region_italy'	),
			'es'		=>	array( 'es',		'afftag_amazon_region_spain'	),
			'jp'		=>	array( 'co.jp',		'afftag_amazon_region_japan'	),
			'cn'		=>	array( 'cn',		'afftag_amazon_region_china'	)
		);
	}
}

if ( !class_exists('ICWP_CCBC_Processor_GeoLocation') ):
	class ICWP_CCBC_Processor_GeoLocation extends ICWP_CCBC_Processor_GeoLocation_V1 { }
endif;