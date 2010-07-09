<?php
/*
Plugin Name: Support Great Writers
Plugin URI: http://www.loudlever.com/callout/greatwriters
Description: Side-Bar Widget to display Amazon product images in your Wordpress blog or magazine.  Can be configured to display the same products (static-mode) or products based upon the post itself (dynamic-mode).
Author: Loudlever, Inc.
Author URI: http://www.loudlever.com
Version: 1.1.0

  $Id$

  Copyright 2009-2010 Loudlever, Inc. (wordpress@loudlever.com)

  Permission is hereby granted, free of charge, to any person
  obtaining a copy of this software and associated documentation
  files (the "Software"), to deal in the Software without
  restriction, including without limitation the rights to use,
  copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the
  Software is furnished to do so, subject to the following
  conditions:

  The above copyright notice and this permission notice shall be
  included in all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  OTHER DEALINGS IN THE SOFTWARE.

*/

/*
---------------------------------------------------------------------------------
  OPTION SETTINGS
---------------------------------------------------------------------------------
*/  

define("SGW_PLUGIN_VERSION", "1.1.0");
define('SGW_PLUGIN_OPTTIONS', '_sgw_plugin_options');
define('SGW_BASE_URL', get_option('siteurl').'/wp-content/plugins/support-great-writers/');
define('SGW_DEFAULT_IMAGE', get_option('siteurl').'/wp-content/plugins/support-great-writers/images/not_found.gif');
define('SGW_POST_META_KEY','SGW_ASIN');
define('SGW_ADMIN_PAGE','amazon_bookstore_options');
define('SGW_ADMIN_PAGE_NONCE','sgw-save-options');
define('SGW_PLUGIN_ERROR_CONTACT','Please contact <a href="mailto:wordpress@loudlever.com?subject=plugin%20error">wordpress@loudlever.com</a> if you have any questions');
// define('SGW_FEEDBACK_EMAIL_VALUE','wordpress@loudlever.com?subject=SGW%20Wordpress%20Plugin');
// define('SGW_SVC_URL_STYLE_GUIDE','http://www.loudlever.com/docs/plugins/wordpress/style_guide');     # designates the URL of the style guide

require_once('include/classes/SGW_Widget.class.php');
require_once('include/classes/SGW_Admin.class.php');

function RegisterAdminPage() {
  // ensure our js and style sheet only get loaded on our admin page
  $page = add_options_page('Amazon Book Store', 'Amazon Book Store', 'manage_options', SGW_ADMIN_PAGE, 'AdminPage');
  add_action("admin_print_scripts-$page", 'AdminInit');
  add_action("admin_print_styles-$page", 'AdminHeader' );
}

function AdminHeader() {
?>
  <link rel='stylesheet' href='<?php echo SGW_BASE_URL; ?>css/sgw_style.css' type='text/css' />
<?php  
}
function AdminPage() {
  // wp_enqueue_script( 'postbox' );
  // wp_enqueue_script( 'jquery-ui-sortable' );
  require_once('admin/admin.php');
}
function AdminInit() {
  wp_enqueue_script('sgw', WP_PLUGIN_URL . '/support-great-writers/js/sgw.js'); 
}

if (class_exists("SupportGreatWriters")) {
  add_action('widgets_init', create_function('', 'return register_widget("SupportGreatWriters");'));
  add_action('admin_menu', 'RegisterAdminPage'); //admin page
}

?>
