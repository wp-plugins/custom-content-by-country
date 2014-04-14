<?php
include_once( dirname(__FILE__).WORPIT_DS.'worpit_options_helper.php' );
include_once( dirname(__FILE__).WORPIT_DS.'widgets'.WORPIT_DS.'worpit_widgets.php' );
?>
<div class="wrap">
	<div class="bootstrap-wpadmin">

		<?php echo printOptionsPageHeader( 'Main Options' ); ?>

		<div class="row">
			<div class="span9">
				<form method="post" action="<?php echo $worpit_form_action; ?>" class="form-horizontal">
					<?php
						printAllPluginOptionsForm( $worpit_aAllOptions, $worpit_var_prefix, 1 );
					?>
					<div class="form-actions">
						<input type="hidden" name="<?php echo $worpit_var_prefix.'all_options_input'; ?>" value="<?php echo $worpit_all_options_input; ?>" />
						<button type="submit" class="btn btn-primary" name="submit">Save All Settings</button>
					</div>
				</form>
			</div><!-- / span9 -->
			<div class="span3" id="side_widgets">
	  			<?php echo getWidgetIframeHtml( 'cbc-side-widgets' ); ?>
			</div>
		</div>
	</div><!-- / bootstrap-wpadmin -->
	<?php include_once( dirname(__FILE__).'/worpit_options_js.php' ); ?>
</div><!-- / wrap -->
