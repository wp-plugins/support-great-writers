<?php
/*
  Widget Class
  http://codex.wordpress.org/Widgets_API
*/
class SupportGreatWriters extends WP_Widget {
  var $seen = [];
  var $options = array();
  var $asins = [];  


	function __construct() {
		$control_ops = array( 'id_base' => 'sgw' );
		$widget_ops = array('description' => __('Easily sell Amazon books or other products in sidebar.','sgw'));
		$this->options = get_option(SGW_PLUGIN_OPTTIONS);
	  parent::__construct('sgw', __('Amazon Book Store','sgw'), $widget_ops,$control_ops );
	}

  // Get the amazon link for a passed-in ASIN and Associates ID
  function get_amazon_link($asin,$assoc,$country) {
    $url_map = array(
      'us' => 'amazon.com', 
      'uk' => 'amazon.co.uk', 
      'de' => 'amazon.de', 
      'fr' => 'amazon.fr', 
      'ca' => 'amazon.ca');
    
    if (!$asin) {
      // display default image
      $link = sprintf('<img src="%s" title="Product ASIN not defined">',SGW_DEFAULT_IMAGE);
    } else {
      $format = '<a title="Click for more Information" target=_blank href="http://www.%s/gp/product/%s?ie=UTF8&tag=%s&linkCode=as2&camp=1789&creative=9325&creativeASIN=%s"><img class="sgw_product_img" src="http://ecx.images-amazon.com/images/P/%s.01._SCMZZZZZZZ_.jpg"></a><img src="http://www.assoc-%s/e/ir?t=%s&l=as2&o=1&a=%s" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';
      $link = sprintf($format,$url_map[$country],$asin,$assoc,$asin,$asin,$url_map[$country],$assoc,$asin);
    }
    return $link;
  }

  // Split a comma-separated list of asins apart and return an array of POST or DEFAULT asins for display.
  private function shuffle_asin_list($list) {
    $asins = array();
    if ($list) {
  	  $array = split(',',$list);
  	  shuffle($array);
      return $array;
    }
	  return $asins;
  }
  // load the ASINs into memory in way that makes it easy to pop them off later
  public function load_asins() {
	  global $post; // this is only available within the widget function, not within the constructor
    $list = '';
    if (!is_home()) { 
      // look to see if we have a post id meta attribute
  	  $list = get_post_meta($post->ID,SGW_POST_META_KEY,true);
  	  $this->asins = array_merge($this->asins,$this->shuffle_asin_list($list));
    }
    // concatenate the defaults onto the end
    $this->asins = array_merge($this->asins,$this->shuffle_asin_list($this->options['default']));
    // need to uniquify the array to prevent duplicates
    $this->asins = array_unique($this->asins);
  }
  // Get the next ASINs for display
  public function get_next_asin_set($count) {
    $asins = [];
    $diff = array_diff($this->asins,$this->seen);
    if (count($diff) >= $count) {
      for ($i = 0; $i < $count; $i++) {
        $next = array_shift($diff);
        $asins[] = $next;
        $this->seen[] = $next;
      }
    }
    return $asins;
  }

  // @see WP_Widget::widget 
	function widget($args, $instance) {
	  $this->load_asins();
		// outputs the content of the widget
    extract($args);
    // widget level opts
    $title = apply_filters('widget_title', $instance['title']);
    $display_count = apply_filters('widget_display_count', $instance['display_count']);
    if (!$display_count) { $display_count = 1; }
    // system level opts
    $affiliate = $this->options['affiliate_id'];
    $country = $this->options['country_id'];
    if (!$affiliate) { $affiliate = 'sgw-1-2-2-20'; $country = 'us'; } // set a default so plugin doesn't stop working
    $asins = $this->get_next_asin_set($display_count);
    if ($asins) { 
      // start the output
      echo $before_widget; 
      if ( $title ) {
        echo $before_title . $title . $after_title; 
      }
?>
<div class="textwidget">
<TABLE id="support_great_writers" style="margin:0px auto;">
    <tr>
<?php

    if ($display_count == 2) {
      printf('<td style="width:50%%;">%s</td><td style="width:50%%;">%s</td>',
        $this->get_amazon_link(@$asins[0],$affiliate,$country),$this->get_amazon_link(@$asins[1],$affiliate,$country));
    } else {
      printf('<td>%s</td>',$this->get_amazon_link(@$asins[0],$affiliate,$country));
    }
?>
</tr></table>
</div>
<?php
    echo $after_widget; 
    } // end of test if count is greater than desired display
	}

  // @see WP_Widget::update
	function update($new_instance, $old_instance) {
	    return $new_instance;
	}

	// outputs the options form on admin
  // @see WP_Widget::form 
	function form($instance) {
    // load the vars
    $title = esc_attr($instance['title']);
    $display_count = esc_attr($instance['display_count']);
    if (!$display_count) { $display_count = 1; }
?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('display_count'); ?>"><?php _e('Display # of Products in Widget:'); ?>
          <select name="<?php echo $this->get_field_name('display_count'); ?>">
<?php
          for ($i=1;$i<=2;$i++) {
            $sel = '';
            if ($display_count==$i) { $sel = 'selected="selected"'; }
            printf("<option value='%s' %s>%s</option>",$i,$sel, $i);
          }
?>          
          </select>
          </label>
        </p>
          
<?php           
          
	}
} // end class
