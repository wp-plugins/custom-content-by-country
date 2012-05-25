=== Custom Content by Country, from Worpit ===
Contributors: paultgoodchild, dlgoodchild
Donate link: http://worpit.com/
Tags: ip-2-nation, custom content, location
Requires at least: 3.2.0
Tested up to: 3.4
Stable tag: trunk

== Description ==

Custom Content by Country WordPress plugin from [Worpit](http://worpit.com/ "Worpit: Fast, Centralized WordPress Admin") 
offers you the option to show/hide content to users based on their location (where provided).

With a simple shortcode you can specify, using a list of country codes whether to 
display or hide a block of text/content.

== Frequently Asked Questions ==

= What is the Shortcode to use? =

[CBC] [/CBC]

= What options are availabe in the shortcode? =

Currently there are 2 options/parameters: country and show

country: is a comma-separated list of country codes, e.g. country="us, es, uk"

show: is a simple yes ('y') or no ('no'). e.g. to hide content, show="n"

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

= Where does the plugin pull it's IP location data? =

The plugin makes use of the location data provided by IP 2 Nation that is freely available. You don't need
to pay for this.

= Will this plugin slow my site down by making external queries to 3rd parties? =

No. The plugin, upon activation, will give ask you to install the necessary data into your WordPress database.
If you don't install this data, you cannot use the plugin.

= There's no plugin options page - why? =

Currently you don't need it. This plugin is as slim and does all it needs to with no frills. If you have any requests
for related functionality, please let me know.

== Screenshots ==

== Changelog ==

= 1.0 =

* First Release

== Upgrade Notice ==

= 1.0 =

* First Release
