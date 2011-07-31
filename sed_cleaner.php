<?php

$plugin['name'] = 'sed_cleaner';
$plugin['version'] = '0.3';
$plugin['author'] = 'Netcarver';
$plugin['author_uri'] = 'http://txp-plugins.netcarving.com';
$plugin['description'] = 'Does a little house cleaning on new installs.';
$plugin['type'] = '3';
$plugin['order'] = 1;

@include_once('../zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

if( @txpinterface === 'admin' )
{
	global $prefs, $txpcfg;
	$files = $prefs['file_base_path']; 
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
	# Remove the setup directory (if permissions allow)...
	#
	sed_cleaner_rmdir( 'setup', $debug );


	#
	# Process the cleanups.php file...
	#
	$file = $files . DS . 'cleanups.php' ;
	if( file_exists( $file ) )
	{
		$cleanups = array();

		#
		# Include the scripted cleanups...
		#
		@include $file;
		if( is_callable( 'sed_cleaner_config' ) )
			$cleanups = sed_cleaner_config();

		if( !empty( $cleanups ) )
		{
			#
			#	Take the scripted actions...
			#
			foreach( $cleanups as $action )
			{
				$p = explode( ' ', $action );
				if( $debug )
					dmp( $p );

				$action = strtolower( array_shift( $p ) );
				$fn = "sed_cleaner_{$action}_action";

				if( is_callable( $fn ) )
				{
					$fn( $p, $debug );
				}
			}
		}
		elseif( $debug )
			echo "<pre>No installation specific cleanups found.\n</pre>";
	}
	elseif( $debug )
		echo "<pre>No installation specific cleanup file found.\n</pre>";


	#
	#	Try to auto-install any plugin files found in the files directory...
	#
	include_once $txpcfg['txpath']. DS . 'include' . DS . 'txp_plugin.php';


	#
	#	Now cleanup the cleanup files...
	#
	sed_cleaner_empty_dir( $prefs['file_base_path'], $debug, true );	# exclude hiddens!
	safe_query( 'TRUNCATE TABLE `txp_file`', $debug );

	#
	# Finally, we self-destruct...
	#
	safe_delete( 'txp_plugin', "`name`='sed_cleaner'", $debug );
}


#
#	sed_cleaner_setpref_action handles adding/setting of prefs...
#
function sed_cleaner_setpref_action( $args, $debug )
{
	$key = doSlash( array_shift( $args ) );
	$args = join( ' ', $args );
	$args = doSlash( trim( $args, '" ' ) );
	safe_upsert( 'txp_prefs', "`val`='$args'",  "`name`='$key'", $debug );
}


#
#	Handles truncating of tables...
#
function sed_cleaner_truncate_action( $args, $debug )
{
	$table = doSlash( array_shift( $args ) );
	safe_query( "TRUNCATE TABLE `$table`", $debug );
}


#
# Handles non-recursice removal of directories...
#
function sed_cleaner_removedir_action( $args, $debug )
{
	$dir = array_shift( $args );
	sed_cleaner_rmdir( $dir, $debug );
}


#
#	sed_cleaner_rmdir removes a dir non-recursively...
#
function sed_cleaner_rmdir( $dir, $debug = 0 )
{
	if( !is_string( $dir ) || empty( $dir ) || !is_dir( $dir ) )
	{
		echo "<pre>Could not remove the directory [$dir].</pre><br />";
		return false;
	}

	sed_cleaner_empty_dir( $dir, $debug );

	if( $debug ) echo "<pre>Removing $dir\n</pre>";
	rmdir($dir);

	return true;
}

function sed_cleaner_empty_dir($dir, $debug, $exclude_hidden = false)
{
	$objects = scandir($dir);
	foreach ($objects as $object)
	{
		if ($object != "." && $object != "..")
		{
			if (filetype($dir . DS . $object) !== "dir")
			{
				if( $object[0] == '.' && $exclude_hidden )
					continue;

				if( $debug ) echo "<pre>Removing $dir/$object\n</pre>";
				unlink($dir. DS .$object);
			}
    }
  }
  reset($objects);
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

h3. v0.3 (30th July, 2011)

* Adds basic scripting capabilities for...
** setting additional prefs
** truncating additional tables
** removing additional directories

h3. v0.2 (30th July, 2011)

* Tries to remove the setup directory where file permissions allow.

h3. v0.1 (29th July, 2011)

* Initial release.

That's it.

 <span style="float:right"><a href="#top" title="Jump to the top">top</a></span>

</div>
# --- END PLUGIN HELP ---
*/
?>
