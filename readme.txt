=== Admin Per Page Limits ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: admin, posts per page, pages per page, comments per page, posts, pages, comments, coffee2code
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 1.1
Version: 1.1

Control the number of posts per page, pages per page, and comments per page that appear in the admin listings of posts, pages, and comments.

== Description ==

**NOTE: This plugin is no longer necessary as of WordPress 2.8 as this functionality is now built-in under the "Screen Options" slide-down menu.**

Control the number of posts per page, pages per page, and comments per page that appear in the admin listings of posts, pages, and comments.

By default, WordPress lists only 15 posts in the admin listing of posts and 20 for pages and comments.  There is no built-in way to change how many posts/pages/comments to list.  This makes paging through the post/page/comment listings very cumbersome, and limits the number of posts/pages/comments you can see at any one time.  This plugin provides a dropdown selection field allowing a choice of 5, 15, 20, 25, 50, 100, or 250 per page limits.  Each section (post, page, comments) has its own limit separate from the others, and the limits are considered a user preference and not a global setting (so each admin/author can set the limits to their liking without affecting other admins/authors).

== Installation ==

1. Unzip `admin-per-page-limits.zip` inside the `/wp-content/plugins/` directory, or upload `admin-per-page-limits.php` into `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. On the `Edit Posts`, `Edit Page`, and `Edit Comments` admin pages, use the new "# posts/pages/comments per page" dropdown located above the listings table.

== Frequently Asked Questions ==

= After activating the plugin, why can't I see the dropdown fields above the listing of posts on the "Edit Posts" admin page? =

This plugin only works for users that have JavaScript enabled.

= If I change the number of posts per page, will that also affect the number of pages per page? =

No.  The posts per page, pages per page, and comments per page are separate limits that are set individually.

= If I change the number of posts per page, will that value take effect for all users? =

No.  The values for the various per page limits are saved on a per-user basis, so it won't affect another user.

== Screenshots ==

1. A screenshot of the Edit Posts admin page. Above the table listing the posts, just before the “Filter” button, is the newly added dropdown selection field allowing you to define how many posts per page you want listed. The paging controls on the far right correctly reflect the number of posts currently being displayed, and partition the paging accordingly.
2. A screenshot of the “Edit Pages” admin page. Above the table listing the pages, just before the “Apply” button, is the newly added dropdown selection field allowing you to define how many pages per page you want listed.
3. A screenshot of the “Edit Comments” admin page. Above the table listing the comments, just before the “Filter” button, is the newly added dropdown selection field allowing you to define how many comments per page you want listed.