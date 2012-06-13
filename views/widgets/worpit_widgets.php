<?php 

	function getWidgetIframeHtml($insSnippet) {

		$sSubPageNow = isset( $_GET['page'] )? 'page='.$_GET['page'].'&': '';

		$sWidth = '100%';
		$sBackgroundColor = "#ffffff";
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
			src="http://www.hostliketoast.com/custom/remote/plugins/hlt-bootstrapcss-plugin-widgets.php?'.$sSubPageNow.'snippet='.$insSnippet.'"
			width="'.$sWidth.'" height="'.$sHeight.'" frameborder="0" scrolling="no" style="background-color:'.$sBackgroundColor.';" ></iframe>
		';
		
	}//getWidgetIframeHtml

?>
