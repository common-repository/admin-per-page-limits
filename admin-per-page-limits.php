<?php
/*
Plugin Name: Admin Per Page Limits
Version: 1.1
Plugin URI: http://coffee2code.com/wp-plugins/admin-per-page-limits
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Control the number of posts per page, pages per page, and comments per page that appear in the admin listings of posts, pages, and comments.

** NOTE: This plugin is no longer necessary as of WordPress 2.8 as this functionality is now built-in under the "Screen Options" slide-down menu. **

By default, WordPress lists only 15 posts in the admin listing of posts and 20 for pages and comments.  There is no built-in way to change
how many posts/pages/comments to list.  This makes paging through the post/page/comment listings very cumbersome, and limits the number of
posts/pages/comments you can see at any one time.  This plugin provides a dropdown selection field allowing a choice of
5, 15, 20, 25, 50, 100, or 250 per page limits.  Each section (post, page, comments) has its own limit separate from the others, and the
limits are considered a user preference and not a global setting (so each admin/author can set the limits to their liking without affecting
other admins/authors).

Compatible with WordPress 2.5+, 2.6+, 2.7+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/admin-per-page-limits.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. On the 'Edit Posts', 'Edit Page', and 'Edit Comments' admin pages, use the new "# posts/pages/comments per page"
dropdown located above the listing table.

*/

/*
Copyright (c) 2009 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation 
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, 
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the 
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( !class_exists('AdminPerPageLimits') ) :

class AdminPerPageLimits {
	var $admin_options_name = 'c2c_admin_per_page_limits';
	var $base_field_name = 'admin_per_page_limit';
	var $possible_limits = array(5, 15, 20, 25, 50, 100, 250);
	var $config = array( /* The WP defaults */
			'comments_limit' => 20, 
			'pages_limit' => 20,
			'posts_limit' => 15
	);
	// Internal use
	var $field_name = '';
	var $prompt = '';
	var $options = array(); // Don't use this directly
	var $type = '';
	var $js_before = '';

	function AdminPerPageLimits() {
		global $pagenow;

		if ( !is_admin() || !in_array($pagenow, array('edit.php', 'edit-comments.php', 'edit-pages.php')) )
			return;
 
		if ( 'edit.php' == $pagenow ) {
			$this->type = 'posts';
			$this->js_before = '#post-query-submit';
		} elseif ( 'edit-comments.php' == $pagenow ) {
			$this->type = 'comments';
			$this->js_before = '#post-query-submit';
		} elseif ( 'edit-pages.php' == $pagenow ) {
			$this->type = 'pages';
			$this->js_before = '#doaction';
		} else
			return;

		$this->field_name = $this->base_field_name . '_' . $this->type;
		$this->prompt = __('%s ' . $this->type . ' per page');
		$this->setting_name = $this->type . '_limit';

		add_action('admin_init', array(&$this, 'maybe_save_options'));
		add_action('admin_footer', array(&$this, 'add_js'));
		add_action('post_limits', array(&$this, 'admin_post_limit'));
		add_action('manage_pages_query', array(&$this, 'manage_pages_query'));
		add_action('comments_per_page', array(&$this, 'comments_per_page'));
	}

	function add_js() {
		$options = $this->get_options();
		$input = "<span><select id='{$this->field_name}' name='{$this->field_name}'>";
		foreach ($this->possible_limits as $limit) {
			$checked = ($options[$this->setting_name] == $limit ? ' selected=\\"selected\\"' : '');
			$input .= "<option value='$limit'$checked>" . sprintf($this->prompt, $limit) . "</option>";
		}
		$input .= "</select></span>";
HTML;
		echo <<<JS
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$('{$this->js_before}').before("{$input}");
			});
		</script>
JS;
	}

	function get_options() {
		if ( !empty($this->options) ) return $this->options;
		$existing_options = get_user_option($this->admin_options_name);
		$this->options = wp_parse_args($existing_options, $this->config);
		return $this->options;
	}

	function maybe_save_options() {
		$user = wp_get_current_user();
		if ( isset($_GET[$this->field_name]) ) {
			$options = $this->get_options();
			$options[$this->setting_name] = attribute_escape($_GET[$this->field_name]);
			update_user_option($user->ID, $this->admin_options_name, $options);
			$this->options = $options;
		}
	}

	function admin_post_limit($sql_limit) {
		// WP takes a few things into account when determining the offset part of the LIMIT,
		//	so refrain from re-determining it
		if ( !$sql_limit || !is_admin() ) return $sql_limit;
		$options = $this->get_options();
		list($offset, $old_limit) = explode(',', $sql_limit, 2);
		$limit = $options[$this->setting_name];
		if (empty($limit)) return $sql_limit;
		
		// Deal with possible paging
		if (is_paged()) {
			global $wp_query;
			$offset = absint($wp_query->query_vars['offset']);
			$page = absint($wp_query->query_vars['paged']);
			if (empty($page))
				$page = 1;

			if (empty($offset)) {
				$offset = ($page - 1) * $limit;
			} else { // we're ignoring $page and using 'offset'
				$offset = absint($offset);
			}
			$offset = "LIMIT $offset";
		}

		global $wp_query;
		$wp_query->query_vars['posts_per_page'] = $limit;

		return ($limit ? "$offset, $limit" : '');
	}

	function comments_per_page($count) {
		if ( !is_admin() ) return $count;
		$options = $this->get_options();
		return $options[$this->setting_name];
	}

	function manage_pages_query($query) {
		if ( is_admin() ) {
			global $per_page;
			$options = $this->get_options();
//			$query['posts_per_page'] = $options[$this->setting_name];
			$per_page = $options[$this->setting_name]; // Holy hacky!
		}
		return $query;
	}
} // end AdminPerPageLimits

endif; // end if !class_exists()

if ( ($wp_db_version < 11548) && class_exists('AdminPerPageLimits') )
	new AdminPerPageLimits();

?>