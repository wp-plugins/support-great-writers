<?php
/**
* Icons : http://www.iconarchive.com/show/100-flat-icons-by-graphicloads.html
*
* Admin page for configuring ASIN pools
*/


if (is_user_logged_in() && is_admin() ){

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
<style type='text/css'>
  a.sgw_PayPal {
    background-image:url(<?php echo SGW_BASE_URL; ?>images/paypal.png);
  }
  a.sgw_Home {
    background-image:url(<?php echo SGW_BASE_URL; ?>images/home.png);
  }
  a.sgw_Suggestion {
    background-image:url(<?php echo SGW_BASE_URL; ?>images/suggestion.png);
  }
  a.sgw_Contact {
    background-image:url(<?php echo SGW_BASE_URL; ?>images/contact.png);
  }
</style>

    <div class="wrap">
      <h2>Amazon Book Store Widget</h2>
      <?php
      if (!$message) {
      ?>
      <div class="updated">
				<p><strong>Thanks for using this plugin! If it works for you, <a href='<?php echo $sgw_admin->donate_link; ?>' target='_blank'>please donate!</a> Donations help keep this plugin free for everyone to use.</strong></p>
      </div>
      <?php
      }
      ?>
      <div id="poststuff" class="metabox-holder has-right-sidebar">
        <!-- Right Side -->
				<div class="inner-sidebar">
					<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
            <?php 
              $sgw_admin->html_box_header('sgw_about',__('About this Plugin','sgw'),true);
              // side bar elems
              $sgw_admin->sidebar_link('PayPal',$sgw_admin->donate_link,'Donate with PayPal'); 
              $sgw_admin->sidebar_link('Home','https://wordpress.org/plugins/support-great-writers/','Plugin Homepage'); 
              $sgw_admin->sidebar_link('Suggestion','https://wordpress.org/support/plugin/support-great-writers','Suggestions'); 
              $sgw_admin->sidebar_link('Contact','mailto:wordpress@loudlever.com','Contact Us'); 
          	  $sgw_admin->html_box_footer(true); 
          	?>  
            <?php $sgw_admin->html_box_header('sgw_how',__('How it Works','sgw'),true); ?>
                <a target=_new href='<?php echo SGW_BASE_URL; ?>images/flow.png' title='Click to see larger image'>
                  <img src='<?php echo SGW_BASE_URL; ?>images/flow_thumb.png'>
                </a>
          	<?php $sgw_admin->html_box_footer(true); ?>  
          </div>
        </div>
        <!-- Left Side -->
        <div class="has-sidebar sm-padded">
					<div id="post-body-content" class="has-sidebar-content">
						<div class="meta-box-sortabless">
              <form method="post" action="admin.php?page=<?php echo SGW_ADMIN_PAGE; ?>">
                <?php
                  if(function_exists('wp_nonce_field')){ wp_nonce_field(SGW_ADMIN_PAGE_NONCE); }
                ?>   
                <?php $sgw_admin->html_box_header('sgw_post_asins',__('POST-specific ASINs','sgw'),true); ?>
                <?php
                  // handle that case where a user has gone widget-happy
                  if (count($widget) > 1) {
                ?>        
                <p><strong>You are currently running <?php echo count($widget)-1; ?> instances of this widget.  Ensure that all are configured to use the same <code>country</code> code &#8212; or that you only use ASINs that will appear in both countries' catalogs.</strong></p>
                <?php
                  }
                ?>
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
                  <!-- placeholder for where new entries are put -->
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
              	<?php $sgw_admin->html_box_footer(true); ?>  
                <?php $sgw_admin->html_box_header('sgw_default_asins',__('Default ASINs','sgw'),true); ?>
                  <p>Input a comma-separated list of the ASINs you would like displayed <em>if no other matches are made</em>.  These products will be displayed for those posts where you have not defined specific ASINs for display.</p>
                  <p>You <strong>must</b> input at least one AISN in the box below.</p>
                  <p>
                    <label class='sgw_label' for='sgw_default_asins'>Default ASINs:</label>
                    <input type="text" name="sgw_opt[default]" id="sgw_default" class='sgw_input' value="<?php echo  $opts['default']; ?>" />
                    <input type="hidden" name="save_settings" value="1" />
                  </p>
                <?php $sgw_admin->html_box_footer(true); ?>  
                <input type="submit" class="button-primary" name="save_button" value="<?php _e('Update Settings', 'sgw'); ?>" />
  	          </form>
            </div>
          </div>
        </div>
    </div>
  </div>
<?php

}

?>
