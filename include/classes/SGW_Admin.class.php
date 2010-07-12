<?php
/*
  $Id$
  
  Admin Class
  
  Contains all of the functions for managing SGW administration functions.

*/
class SGW_Admin {

  var $options = array();
  var $old_widget_name = 'widget_supportgreatwriters';
  var $widget_name = 'widget_sgw';
  var $max_asins_per = 4;
  var $post_meta_key = SGW_POST_META_KEY;
  var $error = false;

  public function __construct() {
    $this->options = get_option(SGW_PLUGIN_OPTTIONS);
  }   

  public function __destruct() {
    if ($this->options) {
      update_option(SGW_PLUGIN_OPTTIONS,$this->options);
    }
  }

  public function check_plugin_version() {
    $opts = get_option(SGW_PLUGIN_OPTTIONS);
    if (!$opts || !$opts[plugin] || $opts[plugin][version_last] == false) {
      $this->init_plugin();
      // there is a possible upgrade path from old widget to this one - in which case we want to migrate data
      $this->migrate_old_widget();
      return;
    }
    // check for upgrade option here
    if ($opts[plugin][version_current] != SGW_PLUGIN_VERSION) {
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

  /**
  * Init the Plugin
  */
  private function upgrade_plugin($opts) {
    // $opts is what's stored in the database
    // we don't have an upgrade path for rev2 of plugin yet - so this method will be written when that time comes
    
  }

  /**
  * Init the Plugin
  */
  private function init_plugin() {
    $this->init_install_options();
    $this->options[plugin][version_last] = SGW_PLUGIN_VERSION;
    $this->options[plugin][version_current] = SGW_PLUGIN_VERSION;
    $this->options[plugin][install_date] = Date('Y-m-d');
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
      $this->error = 'You must input at least one ASIN'; return false;
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
    return false;
  }

  public function truncate_string($str) {
    if (strlen($str)>40) {
      $str = substr($str,0,40) . '...';
    } 
    return $str;
  }

  /**
  * Update all of the page options sent by the form post
  */
  public function update_options($form) {
    // printf("<pre>In update_options()\nREQ: %s\naction = %s</pre>",print_r($_REQUEST,1),$_REQUEST['action']); 
     $message = null;
    if(isset($_POST['save_settings'])) {
      check_admin_referer(SGW_ADMIN_PAGE_NONCE);
      if (isset($_POST['sgw_opt'])) {
        // need to validate username and password against HeyPublisher and if valid save isvalidated boolean
        $opts = $_POST['sgw_opt'];
        // update the default settings
        if ($test = $this->normalize_asin_list($opts['default'])) {
          $this->options['default'] = $test;
          update_option(SGW_PLUGIN_OPTTIONS,$this->options);
        } else {
          $this->print_process_errors();
          return false;
        }
        // update the newly added ASINs
        if ($opts['new']) {
          foreach ($opts['new'] as $id=>$hash) {
            if ($test = $this->normalize_asin_list($hash['asin'])) {
              add_post_meta($id,SGW_POST_META_KEY,$test,true) or update_post_meta($id,SGW_POST_META_KEY,$test);
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
            } else {
              $this->print_process_errors();
              return false;
            }
          }
        }          
        
      }
      return 'Options have been updated';
    }
  }
}
?>
