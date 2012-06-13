=== Custom Content by Country, from Worpit ===
Contributors: paultgoodchild, dlgoodchild
Donate link: http://worpit.com/
Tags: ip2nation, custom content, location, geolocation
Requires at least: 3.2.0
Tested up to: 3.4
Stable tag: 2.0

== Description ==

Custom Content by Country WordPress plugin from [Worpit](http://worpit.com/ "Worpit: Fast, Centralized WordPress Admin") 
offers you the option to show/hide content to users based on their location (where provided).

With a simple shortcode you can specify, using a list of country codes whether to 
display or hide a block of text/content.

As of version 2.0 I have included functionality so you can dynamically generate Amazon Associate/Affiliate links based on the
visitor's country. You simply specify your amazon associate tags and the plugin will do the rest.

To learn how to use the plugin, see the [comprehensive FAQ](http://wordpress.org/extend/plugins/custom-content-by-country/faq/)  

== Frequently Asked Questions ==

= What is the Shortcode to use? =

[CBC] [/CBC]

= What options are available in the shortcode? =

Currently there are 3 options/parameters: *country*, *show*, *message*

country: is a comma-separated list of country codes, e.g. country="us, es, uk"

show: is a simple yes ('y') or no ('n'). e.g. to hide content, show="n"

message: is an optional piece of text you can display when the content that you're showing/hiding from a group of people isn't shown.
Instead of displaying absolutely nothing, you can display a message. e.g message="Sorry, this content isn't available in your region."

= How do I use the shortcode? =

To show the text "abcdefg" ONLY to visitors from the US and France, I would use the following shortcode:

[CBC country="us, fr" show="y"]abcdefg[/CBC]

To then hide the text "mnopqrst" ONLY from visitors in Spain, I would use the following shortcode:

[CBC country="es" show="n"]mnopqrst[/CBC]

= What happens if I leave out the option "show"? =

Then 'show' will default to 'y' and proceed accordingly.

= What happens if I leave out the option "country" =

Nothing, it will just print the content to everyone.

= What is CloudFlare and how does it relate to this plugin? =

If you're site isn't using CloudFlare, you really should consider it.

Separately, if you are using it on your site, you have a slight optimization where I use a parameter that
CloudFlare sends that gives the users location saving me an SQL query.

= Where does the plugin pull its IP location data? =

The plugin makes use of the location data provided by IP 2 Nation that is freely available. You don't need
to pay for this.

= Will this plugin slow my site down by making external queries to 3rd parties? =

No. The plugin, upon activation, will ask you to install the necessary data into your own WordPress database.
If you don't install this data, you cannot use the plugin.

= There's no plugin options page - why? =

Currently you don't need it. This plugin is as slim as it needs to be, with no frills. If you have any requests
for related functionality, please let me know.

= Are the any other shortcodes available in this plugin? =

Yes, I have provided 2 extra shortcodes. They are:

[CBC_COUNTRY /]  takes no parameters and is used as-is. This will print the visitor's full country name.

[CBC_IP /]  takes no parameters and is used as-is. This will print the visitor's full IP address (or their proxy server).

= Can I nest shortcodes - i.e. put a shortcode within the custom content? =

Yes. I'm still baffled why other plugin authors find this a challenge.

= I want to use CSS to style the output, is there way I can do that? =

Yes. Again, I'm still baffled why other plugin authors find this a challenge.

Any output from the plugin is wrapped in html SPANs with corresponding classes:

CBC has class 'cbc_content'
CBC_COUNTRY has class 'cbc_country'
CBC_IP has class 'cbc_ip'

There is also the option within the each shortcode itself to specify ID and STYLE just like you would HTML elements.

e.g. [CBC country="gb" show="y" id="my_cbc_id" style="color:yellow;"]Custom Content for GB[/CBC]

= Do you make any other plugins? =

Yes, we created the only [Twitter Bootstrap WordPress](http://worpit.com/wordpress-twitter-bootstrap-css-plugin-home/ "Twitter Bootstrap WordPress Plugin")
plugin with over 10,000 downloads so far.

We also created the [Worpit Administration Dashboard](http://worpit.com/?wordpress) for people with multiple WordPress sites to manage.

= Is there an option to remove the data that was added to the DB by the plugin? =

Currently, no (but eventually, yes). You can do so manually by firing up your phpMyAdmin for your site and dropping the following 2 tables:

ip2nation
ip2nationCountries

== Changelog ==

= 2.0 =

* UPDATED: the IP 2 Nation database to the version released 3rd June 2012. You will be prompted to run the database upgrade after the plugin is installed.
* ADDED: Plugin options/settings page - you must enable any of the 2 main features to use anything from the plugin. This is in order to maximum plugin
performance so only the absolutely necessary code is used.
* ADDED: Automatic Amazon Affiliate links using shortcode: [CBC_AMAZON] . You can also specify Amazon associate tags for each Amazon website 
and the plugin will automatically use it with the appropriate site and generate an affiliate link for your product ASIN depending on where the visitor is from.
* ADDED: Plugin now conforms to Worpit standard plugin structure. Faster, stable and automatically generates options pages.

= 1.1 =

* ADDED: Shortcode [CBC_CODE/] - which will print your country code.
* ADDED: Special case for local testing, where if your IP Address is detected as 127.0.0.1, country and country code will be detected as 'localhost'.
* Tidied up the code A LOT.
* Improved the Admin Notices and DB update process, and is now using the correct WordPress action hooks.
* Added special case for local testing, where if your IP Address is detected as 127.0.0.1, country and country code will be 'localhost'.
* Began coding for adding some nice features later.

= 1.0 =

* First Release

== Upgrade Notice ==

= 2.0 =

* UPDATED: the IP 2 Nation database to the version released 3rd June 2012. You will be prompted to run the database upgrade after the plugin is installed.
* ADDED: Plugin options/settings page - you must enable any of the 2 main features to use anything from the plugin. This is in order to maximum plugin
performance so only the absolutely necessary code is used.
* ADDED: Automatic Amazon Affiliate links using shortcode: [CBC_AMAZON] . You can also specify Amazon associate tags for each Amazon website 
and the plugin will automatically use it with the appropriate site and generate an affiliate link for your product ASIN depending on where the visitor is from.
* ADDED: Plugin now conforms to Worpit standard plugin structure. Faster, stable and automatically generates options pages.

= 1.1 =

* ADDED: Shortcode [CBC_CODE/] - which will print your country code.
* ADDED: Special case for local testing, where if your IP Address is detected as 127.0.0.1, country and country code will be detected as 'localhost'.
* Tidied up the code A LOT.
* Improved the Admin Notices and DB update process, and is now using the correct WordPress action hooks.
* Began coding for adding some nice features later.

= 1.0 =

* First Release
