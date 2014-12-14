<?php
/**
* $Id$
*
* Admin page for configuring ASIN pools
*/


if (is_user_logged_in() && is_admin() ){

  $sgw_admin = new SGW_Admin;
  $sgw_admin->check_plugin_version();

  $message = $sgw_admin->update_options($_POST);
  $opts = get_option(SGW_PLUGIN_OPTTIONS);
  $posts = $sgw_admin->get_post_meta();
  $existing = array();

  if ($message) {
    printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
  } elseif ($sgw_admin->error) { // reload the form post in this form
    // set the defaults
    $opts['default'] =  $_POST['sgw_opt']['default'];
    // restructure the posts hash
    foreach ($posts as $x=>$hash) {
      $id = $hash['ID'];
      if (isset($_POST['sgw_opt']['posts'][$id])) {
        $hash['meta_value'] = $_POST['sgw_opt']['posts'][$id];
        $posts[$x] = $hash;
      }
    }
  }
	if (!$opts['default']) {
		$opts['default'] = SGW_BESTSELLERS;
	}

?>
    <div class="wrap">
      <h2>Configure Amazon Book Store Widget</h2>
      <div id='flowchart'>
        <a target=_new href='<?php echo SGW_BASE_URL; ?>images/flow.png' title='Click to see larger image'>
          <img src='<?php echo SGW_BASE_URL; ?>images/flow_thumb.png'>
        </a>
          <br/>Process Flow Chart
      </div>
      <p>Here is where you configure the ASIN pool(s) for the <b>Amazon Book Store</b> sidebar Widget.</p>
<?php
  // handle that case where a user has gone widget-happy
      if (count($widget) > 2) {
?>        
      <p><strong>You are currently running <?php echo count($widget)-1; ?> instances of this widget.  Ensure that all are configured to use the same <code>country</code> code &#8212; or that you only use ASINs that will appear in both countries' catalogs.</strong></p>
<?php
      }
?>
<form method="post" action="admin.php?page=<?php echo SGW_ADMIN_PAGE; ?>">
<?php
    if(function_exists('wp_nonce_field')){ wp_nonce_field(SGW_ADMIN_PAGE_NONCE); }
?>    
  <h3>POST-specific ASINs</h3>
    <p>You can manage the ASINs for all POSTs using the form below.  Or, you can manage the ASINs for individual POSTs on the Post Edit page.  When managing ASINs on a Post page, use the custom field <code><?php echo $sgw_admin->post_meta_key; ?></code> and input a comma-separated list of ASINs.</p>
    <p>To remove a POST-specific setting, simply clear the input field and click the 'Update Settings' button.</p>
<?php
  if ($posts) {
    foreach ($posts as $id=>$hash) {
      $existing[] = $hash['ID'];
      printf('<br/><label class="sgw_label" for="sgw_posts_%s">%s</label><input type="text" name="sgw_opt[posts][%s]" id="sgw_posts_%s" class="sgw_input"  value="%s"/>',
        $hash['ID'],$sgw_admin->truncate_string($hash['post_title']),$hash['ID'],$hash['ID'],$hash['meta_value']);
    }
  }
  // conditional test - if we had errors - reprint out the 'new' vals
  if ($sgw_admin->error && @$_POST['sgw_opt']['new']) { 
    foreach ($_POST['sgw_opt']['new'] as $id=>$hash) {
      if (!in_array($id,$existing)) { // this prevents successful saves from being re-listed
        $existing[] = $id;
        printf('<br><label class="sgw_label" for="sgw_new[%s]">%s</label><input type="text" name="sgw_opt[new][%s][asin]" id="sgw_new_%s" class="sgw_input" value="%s"/><input type="hidden" name="sgw_opt[new][%s][title]" value="%s"/>',$id,$sgw_admin->truncate_string($hash['post_title']),$id,$id,$hash['asin'],$id,$hash['title']);
      }
    }
  }
  
?>  
  <br/>
  <div id="newly_added_post_asins"></div>
  
  <label class='sgw_label add_new' for='sgw_add_new'>Add New Post ASINs:</label>
  <select name="sgw_opt[list_all]" id="sgw_add_new" class='sgw_input' onchange='sgw.append_asin_block(this.value);'/>
  <option value='0' selected='selected'>-- Select --</option>
<?php
  $post_list = get_posts(array('numberposts' => -1,'orderby' => 'title', 'order' => 'ASC' ));
 foreach($post_list as $post) {
   if (!in_array($post->ID,$existing)) {
     printf('<option value="%s">%s</option>',$post->ID,$post->post_title);
    }
  }
 ?>
  </select>
  <br>

  <h3>Default ASINs</h3>
    <p>Input a comma-separated list of the ASINs you would like displayed <em>if no other matches are made</em>.  These products will be displayed for those posts where you have not defined specific ASINs for display.</p>
    <p>You <strong>must</b> input at least one AISN in the box below.</p>
      <label class='sgw_label' for='sgw_default_asins'>Default ASINs:</label>
      <input type="text" name="sgw_opt[default]" id="sgw_default" class='sgw_input' value="<?php echo  $opts['default']; ?>" />
      <br>
        <br>
      <input type="hidden" name="save_settings" value="1" />
      <input type="submit" name="save_button" id="save_button" value="Update Settings &raquo;" />
  	</form>
<?php
  // $widget = get_option($sgw_admin->widget_name);
  // printf("<pre>Option hash: \n%s\n</p>",print_r(get_option(SGW_PLUGIN_OPTTIONS),1));
  // printf("<pre>Widget hash: \n%s\n</p>",print_r($widget,1));
?>    
  </div>
<?php

}

?>
