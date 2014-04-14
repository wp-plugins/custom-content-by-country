<?php

function printOptionsPageHeader( $insSection = '' ) {
	$sLinkedIcwp = '<a href="http://icwp.io/3a" target="_blank">iControlWP</a>';
	echo '<div class="page-header">';
	echo '<h2><a id="pluginlogo_32" class="header-icon32" href="http://icwp.io/2k" target="_blank"></a>';
	$sBaseTitle = sprintf( 'Custom Content By Country (from %s)', $sLinkedIcwp );
	if ( !empty($insSection) ) {
		echo sprintf( '%s :: %s', $insSection, $sBaseTitle );
	}
	else {
		echo $sBaseTitle;
	}
	echo '</h2></div>';
}

function getWidgetIframeHtml($insSnippet) {

	$sSubPageNow = isset( $_GET['page'] )? 'page='.$_GET['page'].'&': '';

	$sWidth = '100%';
	$sBackgroundColor = 'transparent';
	$sIframeName = 'iframe-hlt-bootstrapcss-'.$insSnippet;
	switch ( $insSnippet ) {
		case 'side-widgets':
			$sHeight = '1200px';
			break;

		case 'cbc-side-widgets':
			$sHeight = '1200px';
			break;

		case 'dashboard-widget-worpit':
			$sHeight = '230px';
			$sBackgroundColor = 'whiteSmoke';
			break;

		case 'dashboard-widget-developerchannel':
			$sHeight = '312px';
			break;
	}

	return '<iframe name="'.$sIframeName.'"
			src="http://www.icontrolwp.com/custom/remote/plugins/hlt-bootstrapcss-plugin-widgets.php?'.$sSubPageNow.'snippet='.$insSnippet.'"
			width="'.$sWidth.'" height="'.$sHeight.'" frameborder="0" scrolling="no" style="background-color:'.$sBackgroundColor.';" ></iframe>
		';

}
