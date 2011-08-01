<?php

#
#   This is an example cleanup file.
#
#   It should give you a few examples of how to define cleanups (and setups)
#


function sed_cleaner_config()
{
	return array(

		#
		#	Each of your cleanups needs to appear on its own line within the array in this function.
		#	Each line should start with a quote character and end with a quote and then a comma.
		#

		'Truncate txp_discuss_ipban',       # resets the ipban table

		'SetPref enable_xmlrpc_server "0"', # turn off rpc
		'RemoveDir ../rpc',                 # removes the rpc directory.

		#
		#	Configure some of my favourite pref settings...
		#
		'SetPref dateformat "%Y-%m-%d %H:%M"', 
		'SetPref archive_dateformat "%Y-%m-%d %H:%M"',
		'SetPref timezone_key "Europe/London"',
		'SetPref auto_dst "1"',
		'SetPref is_dst "1"',
		'SetPref comments_are_ol "0"',
		'SetPref permlink_mode "section_title"',

		#
		# Example of enabling plugins (if installed)...
		#
		# 'EnablePlugin smd_admin_themes',

		#
		#	To cleanup non-default sections & pages, uncomment any 
		#	of the following...
		#
		#'RemoveSection articles',
		#'RemoveSection about',
		#'RemovePage archive',
		#'BlankPage default',
		#'BlankPage error_default',
		#'BlankCSS default',
		#'BlankForm default',
		#'RemoveForm article_listing',
		#'RemoveForm lofi',
		#'RemoveForm search_results',
		#'RemoveForm single',
		#'RemoveForm popup_comments',
		#'RemoveForm noted',
		#'RemoveForm plainlinks',
	);
}

/**
Here is a list of the preference names from Textpattern 4.4.1...

admin_side_plugins
allow_article_php_scripting
allow_form_override
allow_page_php_scripting
allow_raw_php_scripting
archive_dateformat
articles_use_excerpts
article_list_pageby
attach_titles_to_permalinks
blog_mail_uid
blog_time_uid
blog_uid
comments_are_ol
comments_auto_append
comments_dateformat
comments_default_invite
comments_disabled_after
comments_disallow_images
comments_mode
comments_moderate
comments_on_default
comments_require_email
comments_require_name
comments_sendmail
comment_list_pageby
comment_means_site_updated
comment_nofollow
custom_10_set
custom_1_set
custom_2_set
custom_3_set
custom_4_set
custom_5_set
custom_6_set
custom_7_set
custom_8_set
custom_9_set
dateformat
dbupdatetime
edit_raw_css_by_default
expire_logs_after
file_base_path
file_list_pageby
file_max_upload_size
image_list_pageby
img_dir
include_email_atom
is_dst
language
lastmod
link_list_pageby
locale
logging
log_list_pageby
max_url_len
never_display_email
override_emailcharset
path_from_root
path_to_site
permalink_title_format
permlink_mode
ping_textpattern_com
ping_weblogsdotcom
prefs_id
production_status
rss_how_many
send_lastmod
show_article_category_count
show_comment_count_in_feed
sitename
siteurl
site_slogan
spam_blacklists
syndicate_body_or_excerpt
tempdir
textile_links
timeoffset
url_mode
use_categories
use_comments
use_dns
use_mail_on_feeds_id
use_plugins
use_sections
use_textile
version
*/


#eof
