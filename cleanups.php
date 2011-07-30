<?php

function sed_cleaner_config()
{
	return array(
		'truncate txp_discuss_ipban',				# resets the ipban table

		'setpref enable_xmlrpc_server "0"', # turn off rpc
		'removedir ../rpc',  							  # removes the rpc directory.

		'setpref dateformat "%Y-%m-%d %H:%M"', 
		'setpref archive_dateformat "%Y-%m-%d %H:%M"',
		'setpref timezone_key "Europe/London"',
		'setpref auto_dst "1"',
		'setpref is_dst "1"',
		'setpref comments_are_ol "0"',
		'setpref permlink_mode "section_title"',
	);
}

#eof
