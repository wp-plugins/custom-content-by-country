=== Custom Content by Country (by iControlWP) ===
Contributors: paultgoodchild
Donate link: http://icwp.io/q
Tags: ip2nation, custom content, location, geolocation
Requires at least: 3.2.0
Tested up to: 4.3
Stable tag: 2.17.150725-0

== Description ==

Custom Content by Country WordPress plugin from [iControlWP](http://icwp.io/5z "iControlWP: Manage Multiple WordPress Sites Better")
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

Currently there are 4 options/parameters: *country*, *show*, *message*, *html*

country: a comma-separated list of country codes, e.g. country="us, es, uk"

show: is a simple yes ('y') or no ('n'). e.g. to hide content, show="n"

message: is an optional piece of text you can display when the content that you're showing/hiding from a group of people isn't shown.
Instead of displaying absolutely nothing, you can display a message. e.g message="Sorry, this content isn't available in your region."

html: This is the html tag within which the content will be wrapped, e.g. DIV, SPAN, ...  If this isn't specified, SPAN is used.  If you
don't want any HTML wrapping specify html="none"

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

If your site isn't using CloudFlare, you really should consider it.

Separately, if you are using it on your site, you have a slight optimization where I use a parameter that
CloudFlare sends that gives the users location saving me an SQL query.

= Where does the plugin pull its IP location data? =

The plugin makes use of the location data provided by IP 2 Nation that is freely available. You don't need
to pay for this.

= Will this plugin slow my site down by making external queries to 3rd parties? =

No. The plugin, upon activation, will ask you to install the necessary data into your own WordPress database.
If you don't install this data, you cannot use the plugin.

= Where can I find a list of the country codes? =

I believe the country codes are ISO 3166 country codes. You can find a list here: http://en.wikipedia.org/wiki/ISO_3166-1

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

= How does the W3 Total Cache option work exactly? =

W3 Total Cache allows you to (programmatically) set caching options on a per-page basis. This means we can say for any WordPress page/post that is loaded, to allow
caching, or not.

As of version 2.11, I have added the global plugin option to turn off page caching for ONLY those pages that use this shortcode.  If you use this shortcode throughout
your website using your theme, and you enable this option, you will effectively turn off page caching for your entire site.

Remember this only affects page caching. It doesn't affect any browser caching, database or object caching etc.  If you don't know what this means, read the FAQ on
the W3 Total Cache plugin for more info.

= Do you make any other plugins? =

We also created the [Manage Multiple WordPress Site Better Tool: iControlWP](http://icwp.io/60) for people with multiple WordPress sites to manage.

Yes, we created the only [Twitter Bootstrap WordPress](http://icwp.io/61 "Twitter Bootstrap WordPress Plugin")
plugin with over 122,000 downloads so far.

= What "country code" can I use to test locally if I'm accessing a server on our network? =

If your local network address is defined as "Private" according to the database, the country code to use in this case is: 01

This isn't fully tested and shouldn't be used as-is in production but it seems to hold up.  Feedback welcome.

= Is there an option to remove the data that was added to the DB by the plugin? =

Currently, no (but eventually, yes). You can do so manually by firing up your phpMyAdmin for your site and dropping the following 2 tables:

ip2nation
ip2nationCountries

== Changelog ==

= TODO =

* Add option to remove the ip2nations data from the database.

= 2.17.150725-0 =
*released 10th, August 2015*

* FIXED:  PHP Warning notice in settings page.

= 2.17.150725 =
*released 26th, July 2015*

* UPDATED:  Updated Geo location database to latest available version: 2015-07-25.

= 2.17.150613-0 =
*released 15th, June 2015*

* UPDATED:  Updated Geo location database to latest available version: 2015-06-13.

= 2.17.150218-1 =
*released 16th, April 2015*

* UPDATED:  Updated Geo location database to latest available version: 2015-02-18.
* FIX:      ISO Country Codes for Mexico (MX), and Maldives (MV)

= 2.16.140816-1 =

* CHANGED:  WordPress 4.0 compatibility.
* CHANGED:  Changed plugin version to be shorter (YYMMDD)

= 2.15.20140816-4 =

* FIXED:    Manually updated the database data to correctly store ISO Codes for Countries. [ref](http://en.wikipedia.org/wiki/ISO_3166-1)

= 2.15.20140816-2 =

* FIXED:    Manual tweak to the ip2nations database to correctly reflect the ISO country code for Sweden [ref](http://wordpress.org/support/topic/what-are-the-country-codes)
* CHANGED:  Plugin version now highlights the date of the ip2nations database (YYYYMMDD)
* ADDED:    Automatic plugin updates for updated ip2nations db, minor releases, bug fixes [as per my own article](http://icwp.io/62)
* CHANGED:  Plugin refactor to bring it closer in-line with developments made on [Simple Firewall](http://wordpress.org/plugins/wp-simple-firewall/) and [Twitter Bootstrap](http://wordpress.org/plugins/wordpress-bootstrap-css/) plugins

= 2.14 =

* UPDATED:  IP2Nations database to latest version from 16th August 2014
* UPDATED:  Major code refactor for better maintenance going forward.
* FIXED:    Developer mode (using cookies to optimize performance) setting was ignored in some cases.
* CHANGED:  Developer mode is enabled by default.

= 2.13 =

* UPDATED: IP2Nations database to version 22nd June 2014
* FIX: IP Address detection in cases where it's populating with Port number.
* FIX: shortcode usage of ' html="none" '
* ADDED: option to manually force the display of the database install option.

= 2.12 =

* UPDATED: IP2Nations database to version 22nd March 2014

= 2.11 =

* ADDED: Feature to allow you to by-pass W3TC Total Cache PAGE CACHING for pages that use this shortcode. See FAQs.

= 2.10 =

* ADDED: A global plugin option to turn HTML printing off. You can turn it off globally, and then override for individual shortcodes using the HTML (v2.9) parameter as and when you need it.

= 2.9 =

* UPDATED: IP2Nations database to version 15th January 2013
* ADDED: Ability to not print shortcode with surrounding HTML.  Simply use parameter html="none"

= 2.8 =

* FIXED: Call time by reference errors.
* ADDED: data-detected-country field to the HTML spans that are generated so you can see the exact country code being detected each time.

= 2.7 =

* Added a Developer Mode - turn this on to STOP the performance optimization whereby country code data is stored in a cookie to reduce repeat MySQL queries.
* Ensured that there would be no PHP warning errors associated with WORPIT_DS definition

= 2.6 =

* CHANGED: Now to prevent warnings with settings cookies, the cookie setting has been moved higher up in processing before http headers have been set.
(in response to: http://wordpress.org/support/topic/plugin-custom-content-by-country-from-worpit-warning-cannot-modify-header-information)

= 2.5 =

* ADDED: A dismiss button for those who have manually installed the IP 2 Nations database.

= 2.4 =

* ADDED: Now uses a 24hour cookies to store country code and country name to reduce repeated SQL queries. That is, every visitor that triggers this shortcode will incur only 1 MySQL query on the site.

= 2.3 =

* UPDATED: The IP2Nations IP-to-Country database to latest release (August 22, 2012)

= 2.2 =

* FIXED: Bug with undefined function error (thanks Merle!)

= 2.1 =

* FIXED: Bug with incomplete internationalisation functions. Will complete for a later release.

= 2.0 =

* UPDATED: the IP 2 Nation database to the version released 3rd June 2012. You will be prompted to run the database upgrade after the plugin is installed.
* ADDED: Plugin options/settings page - you must enable any of the 2 main features to use anything from the plugin. This is in order to maximum plugin
performance so only the absolutely necessary code is used.
* ADDED: Automatic Amazon Affiliate links using shortcode: [CBC_AMAZON] . You can also specify Amazon associate tags for each Amazon website
and the plugin will automatically use it with the appropriate site and generate an affiliate link for your product ASIN depending on where the visitor is from.
* ADDED: Plugin now conforms to iControlWP standard plugin structure. Faster, stable and automatically generates options pages.

= 1.1 =

* ADDED: Shortcode [CBC_CODE /] - which will print your country code.
* ADDED: Special case for local testing, where if your IP Address is detected as 127.0.0.1, country and country code will be detected as 'localhost'.
* Tidied up the code A LOT.
* Improved the Admin Notices and DB update process, and is now using the correct WordPress action hooks.
* Added special case for local testing, where if your IP Address is detected as 127.0.0.1, country and country code will be 'localhost'.
* Began coding for adding some nice features later.

= 1.0 =

* First Release

== Upgrade Notice ==

= 2.11 =

* ADDED: Feature to allow you to by-pass W3TC Total Cache PAGE CACHING for pages that use this shortcode. See FAQs.
