<?php

$plugin['name'] = 'sed_cleaner';
$plugin['version'] = '0.1';
$plugin['author'] = 'Netcarver';
$plugin['author_uri'] = 'http://txp-plugins.netcarving.com';
$plugin['description'] = 'Does a little house cleaning on new installs.';
$plugin['type'] = '1';
$plugin['order'] = 1;

@include_once('../zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

defined('sed_cleaner_prefix') || define( 'sed_cleaner_prefix' , 'sed_cleaner' );

if( @txpinterface === 'admin' )
{
	$debug = 0;

	#
	#	Remove default content...
	#
	safe_query( 'TRUNCATE TABLE `txp_discuss`', $debug );
	safe_query( 'TRUNCATE TABLE `txp_link`', $debug );
	safe_query( 'TRUNCATE TABLE `txp_category`', $debug );
	safe_query( 'TRUNCATE TABLE `textpattern`', $debug );
	safe_query( 'TRUNCATE TABLE `txp_image`', $debug );

	#
	#	Setup some defaults...
	#
	safe_update( 'txp_prefs', "`val`=''",               "`name`='site_slogan'", $debug );
	safe_update( 'txp_prefs', "`val`=''",               "`name`='custom_1_set'", $debug );
	safe_update( 'txp_prefs', "`val`=''",               "`name`='custom_2_set'", $debug );
	safe_update( 'txp_prefs', "`val`=''",               "`name`='spam_blacklists'", $debug );

	safe_update( 'txp_prefs', "`val`='%Y-%m-%d %H:%M'", "`name`='dateformat'", $debug );
	safe_update( 'txp_prefs', "`val`='%Y-%m-%d %H:%M'", "`name`='archive_dateformat'", $debug );
	safe_update( 'txp_prefs', "`val`='Europe/London'",  "`name`='timezone_key'", $debug );
	safe_update( 'txp_prefs', "`val`='1'",              "`name`='auto_dst'", $debug );
	safe_update( 'txp_prefs', "`val`='1'",              "`name`='is_dst'", $debug );
	#safe_update( 'txp_prefs', "`val`='0'",              "`name`='comments_are_ol'", $debug );
	safe_update( 'txp_prefs', "`val`='1'",              "`name`='never_display_email'", $debug );

	safe_update( 'txp_prefs', "`val`='0'",              "`name`='use_dns'", $debug );

	#
	# Finally, we self-destruct...
	#
	safe_delete( 'txp_plugin', "`name`='sed_cleaner'", $debug );
}

# --- END PLUGIN CODE ---

/*
# --- BEGIN PLUGIN CSS ---
	<style type="text/css">
	div#sed_cleaner_help td { vertical-align:top; }
	div#sed_cleaner_help code { font-weight:bold; font: 105%/130% "Courier New", courier, monospace; background-color: #FFFFCC;}
	div#sed_cleaner_help code.sed_code_tag { font-weight:normal; border:1px dotted #999; background-color: #f0e68c; display:block; margin:10px 10px 20px; padding:10px; }
	div#sed_cleaner_help a:link, div#sed_cleaner_help a:visited { color: blue; text-decoration: none; border-bottom: 1px solid blue; padding-bottom:1px;}
	div#sed_cleaner_help a:hover, div#sed_cleaner_help a:active { color: blue; text-decoration: none; border-bottom: 2px solid blue; padding-bottom:1px;}
	div#sed_cleaner_help h1 { color: #369; font: 20px Georgia, sans-serif; margin: 0; text-align: center; }
	div#sed_cleaner_help h2 { border-bottom: 1px solid black; padding:10px 0 0; color: #369; font: 17px Georgia, sans-serif; }
	div#sed_cleaner_help h3 { color: #693; font: bold 12px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0;text-transform: uppercase;}
	div#sed_cleaner_help ul ul { font-size:85%; }
	div#sed_cleaner_help h3 { color: #693; font: bold 12px Arial, sans-serif; letter-spacing: 1px; margin: 10px 0 0;text-transform: uppercase;}
	</style>
# --- END PLUGIN CSS ---
# --- BEGIN PLUGIN HELP ---
<div id="sed_cleaner_help">

h1(#top). SED Section Fields Help.

Introduces section-specific overrides for admin interface fields.

h2. Upgrading from version 2

If you are updating for the first time from v2 to v3 (or higher) of this plugin then you
will need to upgrade the section_field preferences by following <a href="/textpattern/index.php?sed_resources=update_data_format" rel="nofollow">this link to upgrade the data.</a>

If the link doesn't work for you and you are running on a localhost server configuration, you can *try* typing the following into your browser to try to force an update...
http://localhost/your-site-name-here/textpattern/index.php?sed_resources=update_data_format

Change 'your-site-name-here' to the name of your local site.


h2(#changelog). Change Log

h3. v0.4

* Adds new layout for "Presentation > Section" tab. You can turn the new layout on and off from the "Admin > Prefs > Advanced" page. Look for the *sed_sf* preferences towards the bottom of the screen.
* Adds a "live" filter to the section index on the new section tab. *NB* This will only appear once the limit specified in "Admin > Prefs > Advanced > sed_sf" is exceeded.
* Bugfix: Error console/IE errors due to a text/xml header being sent for text/plain data.
* Bugfix: PHP notices (treated as errors in some setups) stop the section tab working.

h3. v0.3

* Adds a "Show all" and "Hide all" link under custom field lists to allow all of them to be turned on or off with one click (don't forget to save your change!)
* Now allows sections to be marked as 'static' for exclusion from the write tab's section select list *for non-publishers*.
* Depends upon sed_plugin_lib for MLP support and compact storage format (thanks Dale.)
* Bugfix: Removed limit of 20 custom fields with glz_custom_fields (thanks Dale.)
* Bugfix: Creating new sections now shows the custom controls.
* Bugfix: Renaming a section now preserves existing sed_sf data.
* Using jQuery -- should now work on IE.

h3. v0.2

* Knows how to hide glz_custom_fields too.

h3. v0.1

* Use the presentations > section tab to choose which custom fields to hide for any
article in that section.
* When you write or edit an article, your per-section custom fields preferences will
appear.
* If you change the section of an article and then edit it, the new section's
fields will appear (or disappear) as appropriate to the section.

 <span style="float:right"><a href="#top" title="Jump to the top">top</a></span>

</div>
# --- END PLUGIN HELP ---
*/
?>
