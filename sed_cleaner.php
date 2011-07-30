<?php

$plugin['name'] = 'sed_cleaner';
$plugin['version'] = '0.2';
$plugin['author'] = 'Netcarver';
$plugin['author_uri'] = 'http://txp-plugins.netcarving.com';
$plugin['description'] = 'Does a little house cleaning on new installs.';
$plugin['type'] = '3';
$plugin['order'] = 1;

@include_once('../zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

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
	safe_update( 'txp_prefs', "`val`='0'",              "`name`='use_dns'", $debug );
	safe_update( 'txp_prefs', "`val`='1'",              "`name`='never_display_email'", $debug );

	#
	#	Optional changes...
	#
	#safe_update( 'txp_prefs', "`val`='%Y-%m-%d %H:%M'", "`name`='dateformat'", $debug );
	#safe_update( 'txp_prefs', "`val`='%Y-%m-%d %H:%M'", "`name`='archive_dateformat'", $debug );
	#safe_update( 'txp_prefs', "`val`='Europe/London'",  "`name`='timezone_key'", $debug );
	#safe_update( 'txp_prefs', "`val`='1'",              "`name`='auto_dst'", $debug );
	#safe_update( 'txp_prefs', "`val`='1'",              "`name`='is_dst'", $debug );
	#safe_update( 'txp_prefs', "`val`='0'",              "`name`='comments_are_ol'", $debug );

	#
	# Remove the setup directory (if permissions allow)...
	#
	sed_cleaner_rmdir( 'setup', $debug );


	#
	# Finally, we self-destruct...
	#
	safe_delete( 'txp_plugin', "`name`='sed_cleaner'", $debug );
}


#
#	sed_cleaner_rmdir removes a dir non-recursively.
#
function sed_cleaner_rmdir( $dir, $debug = 0 )
{
	if( !is_string( $dir ) || empty( $dir ) || !is_dir( $dir ) )
	{
		echo "<pre>Could not remove the directory [$dir].</pre><br />";
		return false;
	}

	$objects = scandir($dir);
	foreach ($objects as $object)
	{
		if ($object != "." && $object != "..")
		{
			if (filetype($dir."/".$object) !== "dir")
			{
				if( $debug ) echo "<pre>Removing $dir/$object\n</pre>";
				unlink($dir."/".$object);
			}
    }
  }
  reset($objects);
	if( $debug ) echo "<pre>Removing $dir\n</pre>";
	rmdir($dir);

	return true;
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

h1(#top). SED Cleaner.

Introduces section-specific overrides for admin interface fields.

Kills site content. Only enable this on **NEW** sites!

h2(#changelog). Change Log

h3. v0.2 (30th July, 2011)

* Tries to remove the setup directory where file permissions allow.

h3. v0.1 (29th July, 2011)

* Initial release.

 <span style="float:right"><a href="#top" title="Jump to the top">top</a></span>

</div>
# --- END PLUGIN HELP ---
*/
?>
