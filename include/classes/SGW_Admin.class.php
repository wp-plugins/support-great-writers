<?php
/*
  Admin Class
  
  Contains all of the functions for managing SGW administration functions.

*/
class SGW_Admin {

  var $help = false;
  var $options = array();
  var $old_widget_name = 'widget_supportgreatwriters';
  var $widget_name = 'widget_sgw';
  var $max_asins_per = 4;
  var $post_meta_key = SGW_POST_META_KEY;
  var $error = false;
  var $donate_link = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Y8SL68GN5J2PL';

  public function __construct() {
    $this->options = get_option(SGW_PLUGIN_OPTTIONS);
  }   

  public function __destruct() {
    // nothing to see yet
  }
  public function activate_plugin() {
    $this->log("in the activate_plugin()");
    $this->check_plugin_version();
  }
  public function deactivate_plugin() {
    $this->options = false;
    delete_option(SGW_PLUGIN_OPTTIONS);  // remove the default options
	  return;
  }
  
  private function plugin_admin_url() {
    $url = 'options-general.php?page='.SGW_ADMIN_PAGE;
    return $url;
  }
  // Called by plugin filter to create the link to settings
  public function plugin_link($links) {
    $url = $this->plugin_admin_url();
    $settings = '<a href="'. $url . '">'.__("Settings", "sgw").'</a>';
    array_unshift($links, $settings);  // push to left side
    return $links;
  }
  
  // Filter for creating the link to settings
  public function plugin_filter() {
    return sprintf('plugin_action_links_%s',SGW_PLUGIN_FILE); 
  }

	public function html_box_header($id, $title) {
?>
			<div id="<?php echo $id; ?>" class="postbox">
				<h3 class="hndle"><span><?php echo $title ?></span></h3>
				<div class="inside">
<?php
	}

	public function html_box_footer() {
?>
				</div>
			</div>
<?php
  }

  public function sidebar_link($key,$link,$text) {
    printf('<a class="sgw_button sgw_%s" href="%s" target="_blank">%s</a>',$key,$link,__($text,'sgw'));
  }
  
  public function check_plugin_version() {
    $this->log("in check_plugin_version()");
    
    $opts = get_option(SGW_PLUGIN_OPTTIONS);
    // printf("<pre>In check_plugin_version()\n opts = %s</pre>",print_r($opts,1));
    if (!$opts || !$opts[plugin] || $opts[plugin][version_last] == false) {
      $this->log("no old version - initializing");
      $this->init_plugin();
      // there is a possible upgrade path from old widget to this one - in which case we want to migrate data
      $this->migrate_old_widget();
      return;
    }
    // check for upgrade option here
    if ($opts[plugin][version_current] != SGW_PLUGIN_VERSION) {
      $this->log("need to upgrade version");
      $this->upgrade_plugin($opts);
      return;
    }
  }

  // This is throw-away code.  Once we get everyone upgraded, this can be removed.
  private function migrate_old_widget() {
    $old = get_option($this->old_widget_name);
    if ($old) {
      $asins = array();
      foreach ($old as $i=>$hash) {
        if ($hash[asin1]) { $asins[] = $hash[asin1]; }
        if ($hash[asin2]) { $asins[] = $hash[asin2]; }
      }
      $this->options['default'] = join(',',$asins);
      update_option(SGW_PLUGIN_OPTTIONS,$this->options);
      // uncomment this before final testing
      // delete_option($this->old_widget_name);
    }
    return;
  }
  private function get_version_as_int($str) {
    $var = intval(preg_replace("/[^0-9 ]/", '', $str));
    return $var;
  }

  /**
  * Upgrade path
  */
  private function upgrade_plugin($opts) {
    $ver = $this->get_version_as_int($this->options[plugin][version_current]);
    $this->log("Version = $ver");
    // printf("<pre>In upgrade_plugin()\n ver = %s\nopts = %s</pre>",print_r($ver,1),print_r($this->options,1));
    if ($ver < 210) {
      $url = $this->plugin_admin_url();
      // need to show the mesage about id changing 
      // $html = '<div class="updated"><p>';
      // $html .= __( 'You will need to update your Amazon Associate ID <a href="'.$url.'">on the Settings page</a>.', 'sgw' );
      // echo $html;
    }
    $this->options[plugin][version_last] = $this->options[plugin][version_current];
    $this->options[plugin][version_current] = SGW_PLUGIN_VERSION;
    $this->options[plugin][upgrade_date] = Date('Y-m-d');
    update_option(SGW_PLUGIN_OPTTIONS,$this->options);
  }

  /**
  * Init the Plugin
  */
  private function init_plugin() {
    $this->init_install_options();
    $this->options[plugin][version_last] = SGW_PLUGIN_VERSION;
    $this->options[plugin][version_current] = SGW_PLUGIN_VERSION;
    $this->options[plugin][install_date] = Date('Y-m-d');
    $this->options[plugin][upgrade_date] = Date('Y-m-d');
    add_option(SGW_PLUGIN_OPTTIONS,$this->options);
    return;
  }

  private function init_install_options() {
    $this->options = array(
      'plugin' => array(
        'version_last'    => null,
        'version_current' => null,
        'install_date'    => null,
        'upgrade_date'    => null),
      'default' => null,
      'dynamic' => array()
    );
    return;
  }

  private function print_process_errors() {
?>
  <div id='sgw_error'>
    <h2>Error Encountered</h2>
    <p><?php echo $this->error; ?></p>
    <p><b><?php echo SGW_PLUGIN_ERROR_CONTACT; ?></b></p>
  </div>
<?php
  }
  


  private function normalize_asin_list($list) {
    if (!$list) {
      $list = SGW_BESTSELLERS;
      // $this->error = 'You must input at least one ASIN'; return false;
    }
    $new = array();
    $array = split(',',$list);
    foreach ($array as $asin) {
      $x = trim($asin);
      if (strlen($x) != 10) {
        $this->error = "The ASIN '$x' is invalid - only 10-character ASINs are allowed"; return false;
      } else {
        $new[] = $x;
      }
    }
    $newlist = join(',',$new);
    return $newlist;
  }

  /**
  * Get all of the posts with our custom meta field
  */
  public function get_post_meta() {
    global $wpdb;
    $sql = sprintf("
        SELECT wposts.post_title, wposts.ID, wpostmeta.meta_value 
        FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
        WHERE wposts.ID = wpostmeta.post_id 
        AND wpostmeta.meta_key = '%s' 
        AND wposts.post_type = 'post' 
        ORDER BY wposts.post_title ASC", SGW_POST_META_KEY);
    if ($posts = $wpdb->get_results($sql, ARRAY_A)) {
      return $posts;
    }
    return array();
  }

  public function truncate_string($str) {
    if (strlen($str)>40) {
      $str = substr($str,0,40) . '...';
    } 
    return $str;
  }

  public function supported_countries() {
    $countries = array(
      'us' => 'United States', 
      'uk' => 'United Kingdon', 
      'de' => 'Germany', 
      'fr' => 'France', 
      'ca' => 'Canada'
    );
    // $countries = array(
    //   'us' => 'amazon.com', 
    //   'uk' => 'amazon.co.uk', 
    //   'de' => 'amazon.de', 
    //   'fr' => 'amazon.fr', 
    //   'ca' => 'amazon.ca');
    return $countries;
  }

  /**
  * Update all of the page options sent by the form post
  */
  public function update_options($form) {
     $message = 'Your updates have been saved.';
    if(isset($_POST['save_settings'])) {
      check_admin_referer(SGW_ADMIN_PAGE_NONCE);
      if (isset($_POST['sgw_opt'])) {
        $opts = $_POST['sgw_opt'];
        // printf("<pre>In update_options()\OPTS: %s\naction = %s</pre>",print_r($opts,1),$_REQUEST['action']); 
        $this->options['affiliate_id'] = $opts['affiliate_id'];
        $this->options['country_id'] = $opts['country_id'];
        $this->options['default'] = SGW_BESTSELLERS;
        // update the default asins, if present
        if ($test = $this->normalize_asin_list($opts['default'])) {
          $this->options['default'] = $test;
        }
        update_option(SGW_PLUGIN_OPTTIONS,$this->options);
        // update the newly added ASINs
        if ($opts['new']) {
          foreach ($opts['new'] as $id=>$hash) {
            if ($test = $this->normalize_asin_list($hash['asin'])) {
              add_post_meta($id,SGW_POST_META_KEY,$test,true) or update_post_meta($id,SGW_POST_META_KEY,$test);
							$message = "Your updates have been saved.";
            } else {
              $this->print_process_errors();
              return false;
            }
          }
        }
        // update the existing post ASINs
        if ($opts['posts']) {
          foreach ($opts['posts'] as $id=>$asin_list) {
            // we delete by setting to null
            if (!$asin_list) {
              delete_post_meta($id,SGW_POST_META_KEY);
            } elseif ($test = $this->normalize_asin_list($asin_list,true)) {
              update_post_meta($id,SGW_POST_META_KEY,$test);
							$message = "Your updates have been saved.";
            } else {
              $this->print_process_errors();
              return false;
            }
          }
        }          
        
      }
      return $message;
    }
  }
	/* Contextual Help for the Plugin Configuration Screen */
  public function configuration_screen_help($contextual_help, $screen_id, $screen) {
    if ($screen_id == $this->help) {
      $contextual_help = <<<EOF
<h2>Overview</h2>      
<p>You can sell any kind of Amazon product using this plugin.  To begin, you must first find the ASIN of the product(s) you want to sell.  See <a href="http://askville.amazon.com/find-Amazon-ASIN-product-details-page/AnswerViewer.do?requestId=11106037" target=_blank>How to Find Amazon ASINs</a> for more information.  You can input more than one ASIN - just seperate multiple values with a comma.
</p>

<h2>Settings</h2>
<p>Input your Amazon Affiliate ID and select the approprite affiliate country.  Additionally, input a comma-separated list of ASINs for the products you want to display <i>by default</i> if a more specific list for an individual POST is not configured.  To get you started, we've pre-populated this field with two of the best-selling books currently on Amazon.</p>

<h2>POST-specific ASINs</h2>
<p>Select a POST from the drop-down list and an input field will be added to the page where you can input the ASINs for the products you want displayed specifically on that page.  Alternatively, you can edit the POST directly, adding the custom field <code>$this->post_meta_key</code>.</p>

EOF;
    }
  	return $contextual_help;
  }
  public function configuration_screen() {
    if (is_user_logged_in() && is_admin() ){

      $message = $this->update_options($_POST);
      $opts = get_option(SGW_PLUGIN_OPTTIONS);
      $posts = $this->get_post_meta();
      $existing = array();

      if ($message) {
        printf('<div id="message" class="updated fade"><p>%s</p></div>',$message);
      } elseif ($this->error) { // reload the form post in this form
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
      if ($opts['default'] && !$opts['affiliate_id']) {
        // $this->missing_affiliate_id();
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
    				<p><strong>Thanks for using this plugin! If it works for you, <a href='<?php echo $this->donate_link; ?>' target='_blank'>please donate!</a> Donations help keep this plugin free for everyone to use.</strong></p>
          </div>
          <?php
          }
          ?>
          <div id="poststuff" class="metabox-holder has-right-sidebar">
            <!-- Right Side -->
    				<div class="inner-sidebar">
    					<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">
                <?php 
                  $this->html_box_header('sgw_about',__('About this Plugin','sgw'),true);
                  // side bar elems
                  $this->sidebar_link('PayPal',$this->donate_link,'Donate with PayPal'); 
                  $this->sidebar_link('Home','https://wordpress.org/plugins/support-great-writers/','Plugin Homepage'); 
                  $this->sidebar_link('Suggestion','https://wordpress.org/support/plugin/support-great-writers','Suggestions'); 
                  $this->sidebar_link('Contact','mailto:wordpress@loudlever.com','Contact Us'); 
              	  $this->html_box_footer(true); 
              	?>  
                <?php $this->html_box_header('sgw_how',__('How it Works','sgw'),true); ?>
                    <a target=_new href='<?php echo SGW_BASE_URL; ?>images/flow.png' title='Click to see larger image'>
                      <img src='<?php echo SGW_BASE_URL; ?>images/flow_thumb.png'>
                    </a>
              	<?php $this->html_box_footer(true); ?>  
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
                    <!-- Default Settings -->
                    <?php $this->html_box_header('sgw_default_asins',__('Settings','sgw'),true); ?>
      						  <p>Add the widget to your side-bar and configure which products you want to sell using the form below.</p>

                      <p>
                        <label class='sgw_label' for='sgw_affiliate_id'>Affiliate ID:</label>
                        <input type="text" name="sgw_opt[affiliate_id]" id="affiliate_id" class='sgw_input' value="<?php echo  $opts['affiliate_id']; ?>" />
                      </p>
                      <p>
                        <label class='sgw_label' for='country_id'>Affiliate Country:</label>
                        <select name="sgw_opt[country_id]" id="country_id" class='sgw_input'>
                          <?php
                            $countries = $this->supported_countries();
                            foreach ($countries as $key=>$val) {
                              $sel = '';
                              if ($opts['country_id']==$key) { $sel = 'selected="selected"'; }
                              printf("<option value='%s' %s>%s</option>",$key,$sel,$val);
                            }
                          ?>          
                        </select>
                      </p>

                      <p>
                        <label class='sgw_label' for='sgw_default_asins'>Default ASINs:</label>
                        <input type="text" name="sgw_opt[default]" id="sgw_default" class='sgw_input' value="<?php echo  $opts['default']; ?>" />
                        <input type="hidden" name="save_settings" value="1" />
                      </p>
                    <?php $this->html_box_footer(true); ?>  
                    <?php $this->html_box_header('sgw_post_asins',__('POST-specific ASINs','sgw'),true); ?>
                      <p>If you want specific products to display on individual pages, add those product ASINs here.  Select the POST from the drop-down list below then input the desired ASINs as a comma-separated list.  You can add as many or as few as you like.  You can also set the ASINs in the Post Edit page by using the custom field <code><?php echo $this->post_meta_key; ?></code>.</p>
                      <?php
                        if ($posts) {
                          foreach ($posts as $id=>$hash) {
                            $existing[] = $hash['ID'];
                            printf('<br/><label class="sgw_label" for="sgw_posts_%s">%s</label><input type="text" name="sgw_opt[posts][%s]" id="sgw_posts_%s" class="sgw_input"  value="%s"/>',
                              $hash['ID'],$this->truncate_string($hash['post_title']),$hash['ID'],$hash['ID'],$hash['meta_value']);
                          }
                        }
                        // conditional test - if we had errors - reprint out the 'new' vals
                        if ($this->error && @$_POST['sgw_opt']['new']) { 
                          foreach ($_POST['sgw_opt']['new'] as $id=>$hash) {
                            if (!in_array($id,$existing)) { // this prevents successful saves from being re-listed
                              $existing[] = $id;
                              printf('<br><label class="sgw_label" for="sgw_new[%s]">%s</label><input type="text" name="sgw_opt[new][%s][asin]" id="sgw_new_%s" class="sgw_input" value="%s"/><input type="hidden" name="sgw_opt[new][%s][title]" value="%s"/>',$id,$this->truncate_string($hash['post_title']),$id,$id,$hash['asin'],$id,$hash['title']);
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
                  	<?php $this->html_box_footer(true); ?>  
                    <input type="submit" class="button-primary" name="save_button" value="<?php _e('Update Settings', 'sgw'); ?>" />
      	          </form>
                </div>
              </div>
            </div>
        </div>
      </div>
    <?php

    }
    
  }
  private function log($msg) {
    if (SGW_DEBUG) {
      error_log(sprintf("%s\n",$msg),3,dirname(__FILE__) . '/../../error.log');
    }
  }
  private function missing_affiliate_id() {
?>    
    <div id="affiliate_id_message" class="update-nag">
      <p>Though this plugin will work without one, until you input your Affiliate ID you will not get credit for sales made from the widget.</p>
    </div>
<?php  
  }
}
?>
