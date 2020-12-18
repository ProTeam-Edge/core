=== Memberlite Shortcodes ===
Contributors: kimannwall, strangerstudios
Tags: theme, shortcodes, memberlite, membership, pmpro, paid memberships pro
Requires at least: 4.0
Tested up to: 5.5.1
Stable tag: 1.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A set of shortcodes designed enhance the appearance of your content with the Memberlite Theme.

== Description ==
= Shortcodes to enhance the appearance of your membership site =
For sites running the [Memberlite Theme](https://memberlitetheme.com/) or a Memberlite Child Theme, this plugin offers several shortcodes to simplify the use of various display elements and enhance the appearance of your site content.

[Memberlite](https://memberlitetheme.com) is the ideal theme for your membership site - packed with integration for top membership site plugins including [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/). It's fully customizable with your logo, colors, fonts, custom sidebars, and more global layout settings.

= [memberlite_accordion] Shortcode =
Add an accordion block with collapsible sections using this simple shortcode. [more info](https://memberlitetheme.com/memberlite-shortcodes/accordion/)

= [memberlite_banner] Shortcode =
Create fluid-width banners to divide and highlight sections of content. You can define the background as an included theme color (primary, secondary, action, or body) or any hex color. [more info](https://memberlitetheme.com/memberlite-shortcodes/banners/)

= [memberlite_btn] Shortcode =
Add formatted buttons with the link URL, text, style, class, target, size, and optional icon. [more info](https://memberlitetheme.com/memberlite-shortcodes/buttons/)

= [memberlite_msg] Shortcode =
Insert a stylized contextual message block with styling for default, info, alert, error, or a success message. [more info](https://memberlitetheme.com/memberlite-shortcodes/contextual-messages/)

= [memberlite_recent_posts] Shortcode =
Designed to be used on the homepage, this shortcode displays the newest posts or a defined category of posts accoriding to your shortcode settings. [more info](https://memberlitetheme.com/memberlite-shortcodes/recent-posts/)

= [memberlite_signup] Shortcode =
If you're using [Paid Memberships Pro](https://www.paidmembershipspro.com), this shortcode will display a block with signup fields for a specific membership level. [more info](https://memberlitetheme.com/memberlite-shortcodes/membership-signup-block/)

= [memberlite_subpagelist] Shortcode =
Easily create a digest view of a given pages' subpages, with an excerpt or the full page content, in the order you define. [more info](https://memberlitetheme.com/memberlite-shortcodes/subpagelist/)

= [memberlite_tabs] and [memberlite_tab] Shortcode =
Add a tabbed content block with this simple shortcode. [more info](https://memberlitetheme.com/memberlite-shortcodes/tabs/)

= [row] and [col] Shortcodes =
Format your content in responsive columns based on a 12 column grid. You can nest columns by using the [row_row] and [col_col] shortcodes. [more info](https://memberlitetheme.com/memberlite-shortcodes/column-shortcodes/)

= [fa] Shortcode =
Easily add any Font Awesome icon using this simple shortcode. [more info](https://memberlitetheme.com/memberlite-shortcodes/font-awesome-icons/)

Full documentation on all included shortcodes can be found at [the Memberlite Theme homepage](https://memberlitetheme.com/memberlite-shortcodes/)

== Installation ==

= Download, Install and Activate =
In your WordPress admin, go to Plugins > Add New to install Memberlite Shortcodes, or:

1. Download the latest version of the plugin.
2. Unzip the downloaded file to your computer.
3. Upload the /memberlite-shortcodes/ directory to the /wp-content/plugins/ directory of your site.
4. Activate the plugin through the 'Plugins' menu in WordPress.

= Start Using the Shortcodes! =
Browse the Memberlite Shortcodes documentation to see all shortcodes, their attributes, and to view sample shortcode demos.

[View Documentation](https://memberlitetheme.com/memberlite-shortcodes/)

== Screenshots ==
1. Demo of the Recent Posts shortcode [memberlite_recent_posts] with featured images, excerpts, and a three-column layout.
2. Demo of the [memberlite_subpagelist] shortcode in a column layout and demo of the [fa] icons.
3. Demo of the [memberlite_signup] shortcode for a Paid Memberships Pro membership level in a two-column layout using the [memberlite_banner] with the "body" background.

== Frequently Asked Questions ==

= Where can I find Memberlite Shortcodes documentation and user guides? =
For help setting up and configuring the Memberlite Shortcodes plugin, please refer to [documentation](https://memberlitetheme.com/memberlite-shortcodes/).

= Where can I get the Memberlite Theme? =
Visit https://memberlitetheme.com to get your copy of the Memberlite Theme.

= I'm not using the Memberlite Theme - can I still use Memberlite Shortcodes plugin? =
Some of the shortcodes in this plugin will work with any theme, but we cannot guarantee the appearance will match that of the demo site.

== Changelog ==

= 1.3.4 - 2020-09-14 =
* BUG FIX/ENHANCEMENT: Fixed issue with localization to allow for proper translation.
* BUG FIX/ENHANCEMENT: Improved the tabs shortcode to allow for hyperlink to a targeted tab and set as active.
* BUG FIX/ENHANCEMENT: Improved the accordion shortcode to allow for hyperlink to a targeted accordion item and set as active.
* BUG FIX: Removed code that was behaving inconsistently to set a cookie based on the last active tab visited.
* ENHANCEMENT: Now using version 5.14.0 of Font Awesome.
* ENHANCEMENT: Now tested up to WordPress 5.5.

= 1.3.3 - 2020-04-30 =
* BUG FIX: Fixed display bug for a recent post in shortcode output with no avatar or featured image.
* BUG FIX: Fixed issue with $memberlite_active_tabs not countable.
* BUG FIX: Now inheriting font-family for .btn, button, input[type=submit] from the body property.
* BUG FIX/ENHANCEMENT: Adjusted `memberlite_subpagelist` shortcode logic to accept `post_parent="-1"` to allow a meta_key query across pages, regardless of the parent.
* BUG FIX/ENHANCEMENT: Added alt tag to recent posts shortcode output for featured image or avatar.
* BUG FIX/ENHANCEMENT: Adjusted button font-family to inherity from body rather than set explicitly.
* ENHANCEMENT: Added support for `cat` or `tag_id` as attributes of the `memberlite_subpagelist` shortcode.
* ENHANCEMENT: Now using version 5.13.0 of Font Awesome.
* ENHANCEMENT: Now tested up to WordPress 5.4.

= 1.3.2 - 2019-05-09 =
* ENHANCEMENT: Now using version 5.8.2 of Font Awesome.

= 1.3.1 - 2018-08-01 =
* BUG FIX: Improved broken layout of Recent Posts shortcode to use grid layout properly.

= 1.3 - 2018-07-30 =
* ENHANCEMENT: Updated to use Font Awesome version 5.2.
* ENHANCEMENT: Adjusted styles and memberlite_subpagelist shortcode to match Memberlite Theme version 4.0 styling.

= 1.2 - 2018-02-21 =
* ENHANCEMENT: Added additional attributes to the memberlite_subpagelist shortcode (include, post_type, meta_key).
* ENHANCEMENT: Added Accordion shortcode.
* ENHANCEMENT: Now using version 4.7 of Font Awesome.

= 1.1 - 2017-11-28 =
* BUG: Fixed warning and deprecated function for get_the_author_meta().
* ENHANCEMENT: More attributes for recent posts to display posts by author or select post type (CPT).
* ENHANCEMENT: Added a 'icon_position' attribute to the memberlite_btn shortcode.
* ENHANCEMENT: Added a 'class' attribute to the memberlite_btn shortcode.
* ENHANCEMENT: Now only loading CSS/JS resources when needed.
* BUG: Now checking that PMPro is active before requiring memberlite_signup shortcode.

= 1.0.1 - 2016-08-15 =
* BUG: Removed empty 'h2' when no title defined for banner shortcode.
* BUG: Now using plugins_url to avoid http/https issues.

= 1.0 - 2016-07-05 =
Initial Release
