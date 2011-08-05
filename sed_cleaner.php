<?php

$plugin['name'] = 'sed_cleaner';
$plugin['version'] = '0.5';
$plugin['author'] = 'Netcarver';
$plugin['author_uri'] = 'https://github.com/netcarver/sed_cleaner';
$plugin['description'] = 'Does a little house cleaning on new installs.';
$plugin['type'] = '3';
$plugin['order'] = 1;

@include_once('../zem_tpl.php');

# --- BEGIN PLUGIN CODE ---

if( @txpinterface === 'admin' )
{
	global $prefs, $txpcfg;
	$files_path = $prefs['file_base_path'];
	$tpref = $txpcfg['table_prefix'];
	$debug = 0;

	#
	#	Remove default content...
	#
	safe_query( "TRUNCATE TABLE `{$tpref}txp_discuss`", $debug );
	safe_query( "TRUNCATE TABLE `{$tpref}txp_link`", $debug );
	safe_query( "TRUNCATE TABLE `{$tpref}textpattern`", $debug );
	safe_query( "TRUNCATE TABLE `{$tpref}txp_image`", $debug );
	safe_delete( 'txp_category', "`name` <> 'root'");

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
	#	identify installable files found in the files/ directory...
	#
	include_once $txpcfg['txpath']. DS . 'include' . DS . 'txp_plugin.php';
	$files = array();
	$path = $files_path;
	if( $debug ) echo br , "Auto Install Plugins... Accessing dir($path) ...";
	$dir = @dir( $path );
	if( $dir === false )
	{
		if( $debug ) echo " failed!";
	}
	else
	{
		while( $file = $dir->read() )
		{
			$parts = pathinfo($file);
			$fileaddr = $path.DS.$file;
			if( !is_dir($fileaddr) )
			{
				if( $debug ) echo br , "... found ($file)";
				switch( @$parts['extension'] )
				{
					case 'plugin' :
						$files['plugins'][] = $file;
						if( $debug ) echo " : accepting as a candidate plugin file.";
						break;
					case 'css' :
						$files['css'][] = $file;
						if( $debug ) echo " : accepting as a candidate CSS file.";
						break;
					case 'page' :
						$files['page'][] = $file;
						if( $debug ) echo " : accepting as a candidate Txp page file.";
						break;
					case 'form' :
						$files['form'][] = $file;
						if( $debug ) echo " : accepting as a candidate Txp form file.";
						break;
					default:
						break;
				}
			}
		}
	}
	$dir->close();


	#
	#	Try to auto-install any plugin files found in the files directory...
	#
	if( empty( $files['plugins'] ) )
	{
		if( $debug ) echo " no plugin candidate files found.";
	}
	else
	{
		foreach( $files['plugins'] as $file )
		{
			if( $debug ) echo br , "Processing $file : ";
			#
			#	Load the file into the $_POST['plugin64'] entry and try installing it...
			#
			$plugin = join( '', file($path.DS.$file) );
			$_POST['plugin64'] = $plugin;
			if( $debug ) echo "installing,";
			include_once $txpcfg['txpath'].'/lib/txplib_head.php';
			plugin_install();
		}
	}


	#
	#	Try to install any CSS files found...
	#
	if( empty( $files['css'] ) )
	{
		if( $debug ) echo " no CSS candidates found.";
	}
	else
	{
		foreach( $files['css'] as $file )
		{
			if( $debug ) echo br , "Processing $file : ";
			$content = doSlash( file_get_contents( $path.DS.$file ) );
			$parts = pathinfo($file);
			$name = doSlash( $parts['filename'] );
			safe_upsert( 'txp_css', "`css`='$content'", "`name`='$name'" , $debug );
		}
	}


	#
	#	Try to install any page files found...
	#
	if( empty( $files['page'] ) )
	{
		if( $debug ) echo " no page candidates found.";
	}
	else
	{
		foreach( $files['page'] as $file )
		{
			if( $debug ) echo br , "Processing $file : ";
			$content = doSlash( file_get_contents( $path.DS.$file ) );
			$parts = pathinfo($file);
			$name = doSlash( $parts['filename'] );
			safe_upsert( 'txp_page', "`user_html`='$content'", "`name`='$name'" , $debug );
		}
	}


	#
	#	Try to install any form files found...
	#
	#	Filename format = name.type.form
	#	where type is one of { article, link, file, comment, misc }
	#
	if( empty( $files['form'] ) )
	{
		if( $debug ) echo " no form candidates found.";
	}
	else
	{
		foreach( $files['form'] as $file )
		{
			if( $debug ) echo br , "Processing $file : ";
			$content = doSlash( file_get_contents( $path.DS.$file ) );
			$parts = pathinfo($file);
			$tmp = explode( '.', $parts['filename'] );
			$type = doSlash( array_pop($tmp) );
			$name = doSlash( implode( '.', $tmp ) );

			echo br, "Found form $name of type $type.";
			safe_upsert( 'txp_form', "`Form`='$content', `type`='$type'", "`name`='$name'" , $debug );
		}
	}


	#
	# Process the cleanups.php file...
	#
	$file = $files_path . DS . 'cleanups.php' ;
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


	if( !$debug )
	{
		#
		#	cleanup the cleanup files...
		#
		sed_cleaner_empty_dir( $prefs['file_base_path'], $debug, true );	# exclude hiddens!
		safe_query( "TRUNCATE TABLE `{$tpref}txp_file`", $debug );

		#
		# Finally, we self-destruct unless debugging and redirect to the plugins tab...
		#
		safe_delete( 'txp_plugin', "`name`='sed_cleaner'", $debug );
		while( @ob_end_clean() );
		header('Location: '.hu.'textpattern/index.php?event=prefs');
		header('Connection: close');
		header('Content-Length: 0');
		exit(0);
	}
}

#
#   Action handlers for the cleanups.php script follow...
#

function sed_cleaner_removesection_action( $args, $debug )
{
	$section_name = doSlash( array_shift( $args ) );
	if( $debug ) echo " attempting to remove section $section_name.";
	safe_delete('txp_section', "`name` = '$section_name'" , $debug );
}

function sed_cleaner_addsection_action( $args, $debug )
{
	$section_title = doSlash( array_shift( $args ) );
	$section_name = strtolower(sanitizeForUrl($section_title));

	if( !empty( $args ) ) 
		$page = doSlash( array_shift( $args ) );
	else
		$page = $default['page'];

	if( !empty( $args ) ) 
		$css = doSlash( array_shift( $args ) );
	else
		$css = $default['css'];

	if( !empty( $args ) ) 
		$rss = doSlash( array_shift( $args ) );
	else
		$rss = 0;

	if( !empty( $args ) ) 
		$frontpage = doSlash( array_shift( $args ) );
	else
		$frontpage = 0;

	if( !empty( $args ) ) 
		$searchable = doSlash( array_shift( $args ) );
	else
		$searchable = 0;

	$default = doSlash(safe_row('page, css', 'txp_section', "name = 'default'"));
	if( $debug ) echo " attempting to add a section entitled '$section_title'.";
	safe_insert( 'txp_section',
		"`name` = '$section_name',
		`title` = '$section_title',
		`page`  = '$page',
		`css`   = '$css',
		`is_default` = 0,
		`in_rss` = $rss,
		`on_frontpage` = $frontpage,
		`searchable` = $searchable",
		$debug );
}

function sed_cleaner_removepage_action( $args, $debug )
{
	$page = doSlash( array_shift( $args ) );
	if( $debug ) echo " removing page $page.";
	safe_delete( 'txp_page', "`name` = '$page'", $debug );
}

function sed_cleaner_blankpage_action( $args, $debug )
{
	$page = doSlash( array_shift( $args ) );
	if( $debug ) echo " blanking page $page.";
	$content = '';
	if( $page === 'default' )
		$content = <<<HTML
<html>
  <head>
    <title><txp:site_name /></title>
  </head>

  <body>
    <p><txp:site_name /> is blank.</p>
  </body>
</html>
HTML;
	safe_update( 'txp_page', "`user_html` = '$content'", "`name` = '$page'", $debug );
}

function sed_cleaner_blankcss_action( $args, $debug )
{
	$css = doSlash( array_shift( $args ) );
	if( $debug ) echo " blanking CSS $css.";
	safe_update( 'txp_css', "`css`=''", "`name`='$css'", $debug );
}

function sed_cleaner_enableplugin_action( $args, $debug )
{
	$plugin = doSlash( array_shift( $args ) );
	if( $debug ) echo " attempting to activate $plugin.";
	safe_update( 'txp_plugin', "`status`='1'", "`name`='$plugin'", $debug );
}

function sed_cleaner_removeform_action( $args, $debug )
{
	$form = doSlash( array_shift( $args ) );
	if( $debug ) echo " removing form $form.";
  safe_delete( 'txp_form', "`name`='$form'", $debug );
}

function sed_cleaner_blankform_action( $args, $debug )
{
	$form = doSlash( array_shift( $args ) );
	if( $debug ) echo " blanking form $form.";
	safe_update( 'txp_form', "`Form` = ''", "`name`='$form'", $debug );
}

function sed_cleaner_disableplugin_action( $args, $debug )
{
	$plugin = doSlash( array_shift( $args ) );
	if( $debug ) echo " attempting to deactivate $plugin.";
	safe_update( 'txp_plugin', "`status`='0'", "`name`='$plugin'", $debug );
}

function sed_cleaner_setpref_action( $args, $debug )
{
	$key = doSlash( array_shift( $args ) );
	$args = join( ' ', $args );
	$args = doSlash( trim( $args, '" ' ) );
	safe_upsert( 'txp_prefs', "`val`='$args'",  "`name`='$key'", $debug );
}

function sed_cleaner_truncate_action( $args, $debug )
{
	$table = doSlash( array_shift( $args ) );
	safe_query( "TRUNCATE TABLE `$table`", $debug );
}

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
		echo "<pre>Could not remove the directory [$dir]. It doesn't seem to exist.</pre><br />";
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

function sed_cleaner_removefile_action( $args, $debug )
{
	$filename = array_shift( $args );
	$whitelist = array( 'license.txt', 'lgpl-2.1.txt', '../HISTORY.txt', '../README.txt' );
	if( !in_array( $filename, $whitelist ) )
	{
		echo "[$filename] is not in the files whitelist.";
		return;
	}

	if( $debug ) echo " attempting to remove file [$filename].";
	if( file_exists( $filename ) )
		unlink( $filename );
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

h2(#changelog). "Change Log":http://forum.textpattern.com/viewtopic.php?pid=250247#p250247


That's it.

 <span style="float:right"><a href="#top" title="Jump to the top">top</a></span>

</div>
# --- END PLUGIN HELP ---
*/
?>
