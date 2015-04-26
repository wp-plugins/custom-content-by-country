<?php 
	include_once( dirname(__FILE__).'/widgets/worpit_widgets.php' );
?>

<div class="wrap">
	<div class="bootstrap-wpadmin">

		<?php echo printOptionsPageHeader( 'Dashboard' ); ?>

		<div class="row">
			<div class="span12">
				<div class="alert alert-error">
					<h4 class="alert-heading">Important Notice</h4>
					You need to go to the <a href="admin.php?page=<?php echo $this->getSubmenuId('main') ?>">main plugin Settings page</a> to enable the plugin features as they are no longer enabled by default.</div>
			</div><!-- / span12 -->
		</div><!-- / row -->

		<div class="row">
			<div class="span12">
				<div class="well">
					<div class="row">
						<div class="span6">
							<h3>Do you like the Custom Content By Country plugin?</h3>
							<p>Help <u>spread the word</u> or check out what else we do ...</p>
						</div>
						<div class="span4">
							<a href="https://twitter.com/share" class="twitter-share-button" data-url="wordpress.org/extend/plugins/custom-content-by-country/" data-text="Get Custom Content By Country plugin for #WordPress!" data-via="Worpit" data-size="large" data-hashtags="bootstrap">Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
						</div>
					</div>
					<div class="row">
						<div class="span6">
							<ul>
								<li><a href="http://bit.ly/MB8P9h" target="_blank"><strong>All-new WordPress Admin For Multiple Sites!</strong></a></li>
								<li><a href="http://bit.ly/MB8TG3">Our WordPress Twitter Bootstrap Plugin</a></li>
							</ul>
						</div>
						<div class="span5">
							<ul>
								<li><a href="http://bit.ly/MB8IdX " target="_blank"><strong>Read about our new WordPress backup and restore service: WorpDrive</strong></a>.</li>
								<li><a href="http://wordpress.org/extend/plugins/custom-content-by-country/" target="_blank">Give this plugin a 5 star rating on WordPress.org.</a></li>
								<!-- <li><a href="http://bit.ly/owxOjJ">Get Quality Wordpress Web Hosting</a></li>  -->
							</ul>
						</div>
					</div>
				</div><!-- / well -->
			</div><!-- / span12 -->
		</div><!-- / row -->

		<div class="row" id="worpit_promo">
		  <div class="span12">
		  	<?php echo getWidgetIframeHtml('dashboard-widget-worpit'); ?>
		  </div>
		</div><!-- / row -->

		<div class="row" id="tbs_docs">
		  <div class="span6" id="tbs_docs_shortcodes">
			  <div class="well">
				<h3>Custom Content By Country Shortcodes</h3>
				<p>To learn more about what shortcodes are, <a href="http://www.hostliketoast.com/2011/12/how-extend-wordpress-powerful-shortcodes/">check this link</a></p>
				<p>The following shortcodes are available:</p>
				<ol>
					<li>[ CBC ] <span class="label label-success">new</span></li>
					<li>[ CBC_COUNTRY ] <span class="label label-success">new</span></li>
					<li>[ CBC_IP ] <span class="label label-success">new</span></li>
					<li>[ CBC_CODE ] <span class="label label-success">new</span></li>
					<li>[ CBC_AMAZON ] <span class="label label-success">new</span></li>
				</ol>
				<p>[ CBC_AMAZON ] takes 2 parameters: 'asin' and 'country'. ASIN is simply the Amazon ASIN code for the product you're promoting.</p>
				<p>'country' can take <strong>ONE</strong> of the following:</p>
				<ul>
					<li>us / global : links to Amazon.com</li>
					<li>ca : links to Amazon.ca</li>
					<li>uk / ie : links to Amazon.co.uk</li>
					<li>fr : links to Amazon.fr</li>
					<li>de : links to Amazon.de</li>
					<li>it : links to Amazon.it</li>
					<li>es : links to Amazon.es</li>
					<li>jp : links to Amazon.co.jp</li>
					<li>cn : links to Amazon.cn</li>
				</ul>
				<p>If you do not specify a country, it detects the visitors country and if it's not on the list, 'global' (i.e. Amazon.com) is assumed.
			  </div>
		  </div><!-- / span6 -->
		  <div class="span6" id="tbs_docs_examples">
		  <div class="well">
			<h3>Shortcode Usage Examples</h3>
			<div class="shortcode-usage">
				<p>The following are just some examples of how you can use the shortcodes with the associated HTML output</p>
				<ul>
					<li><span class="code">[CBC show="y" country="es, us"]I only appear in Spain and the U.S.[/CBC]</span>
					<p>will give the following HTML:</p>
					<p class="code">&lt;SPAN class="cbc_content"&gt;I only appear in Spain and the U.S.&lt;/SPAN&gt;</p>
					<p class="code-description">This HTML will only appear when the visitor is found to be in Spain or North America given the country codes used, 'es' and 'us'.</p>
					</li>
				</ul>
			</div>
			<div class="shortcode-usage">
				<ul>
					<li><span class="code">[CBC_AMAZON asin="0470877014" country="us"]My affiliate link[/CBC_AMAZON]</span>
					<p>will give the following HTML:</p>
					<p class="code">&lt;a class="cbc_amazon_link" href="http://www.amazon.com/dp/0470877014/?tag=MYTAG&creativeASIN=0470877014"&gt;My affiliate link&lt;/a&gt;</p>
					<p class="code-description">the 'MYTAG' will be whatever you specify as your Amazon.com associates tag in the options page.</p>
					</li>
				</ul>
			</div>
			<div class="shortcode-usage">
				<p>There will be more documentation forthcoming on the <a href="http://worpit.com/">Worpit</a> website.</p>
			</div>
		  </div>
		  </div><!-- / span6 -->
		</div><!-- / row -->
		
		<div class="row">
		  <div class="span6">
		  </div><!-- / span6 -->
		  <div class="span6">
		  	<p></p>
		  </div><!-- / span6 -->
		</div><!-- / row -->
		
	</div><!-- / bootstrap-wpadmin -->

</div><!-- / wrap -->