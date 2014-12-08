<?php
/*
  $Id$
  
  Widget Class

*/
class SupportGreatWriters extends WP_Widget {

    /** constructor */
	function SupportGreatWriters() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'sgw', 'description' => 'Display Amazon books and other products in sidebar.' );
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'sgw' );
		/* Create the widget. */
		$this->WP_Widget( 'sgw', 'Amazon Book Store', $widget_ops, $control_ops );
	}

  /**
  * Get the amazon link for a passed-in ASIN and Associates ID
  */
  function get_amazon_link($asin,$assoc,$country) {
    $url_map = array(
      'us' => 'amazon.com', 
      'uk' => 'amazon.co.uk', 
      'de' => 'amazon.de', 
      'fr' => 'amazon.fr', 
      'ca' => 'amazon.ca');
    
    if (!$asin) {
      // display default image
      $link = sprintf('<img src="%s" title="Production ASIN not defined">',SGW_DEFAULT_IMAGE);
    } else {
      $format = '<a title="Click for more Information" target=_blank href="http://www.%s/gp/product/%s?ie=UTF8&tag=%s&linkCode=as2&camp=1789&creative=9325&creativeASIN=%s"><img border="0" src="http://ecx.images-amazon.com/images/P/%s.01._SCMZZZZZZZ_.jpg"></a><img src="http://www.assoc-%s/e/ir?t=%s&l=as2&o=1&a=%s" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />';
      $link = sprintf($format,$url_map[$country],$asin,$assoc,$asin,$asin,$url_map[$country],$assoc,$asin);
    }
    return $link;
  }

  /**
  * Split a comma-separated list of asins apart and return an array of POST or DEFAULT asins for display.
  */
  private function split_asin_list($list) {
    $asins = array();
    if ($list) {
  	  $array = split(',',$list);
      // if the number of vals is greater than the max number of slots to fill, get a random value
      if (count($array) > 2) {
        // we fetch a random asin from the array
        $rand = array_rand($array,2);
        $asins[0] = $array[$rand[0]];
        $asins[1] = $array[$rand[1]];
      } else {
        $asins = $array;
      }
    }
	  return $asins;
  }
  

  /** @see WP_Widget::widget */
	function widget($args, $instance) {
	  global $post;
		// outputs the content of the widget
      extract($args);
      $title = apply_filters('widget_title', $instance['title']);
      $affiliate = apply_filters('widget_affiliate', $instance['affiliate']);
      $country = apply_filters('widget_country', $instance['country']);
      $display_count = apply_filters('widget_display_count', $instance['display_count']);
      //  ensure some defaults get set immediately
      if (!$affiliate) { $affiliate = 'loud-writers-20'; $country = 'us'; }
      if (!$display_count) { $display_count = 2; }

// start the output
    echo $before_widget; 
    if ( $title ) {
      echo $before_title . $title . $after_title; 
    }
    if ( $affiliate ) { 
      $asin_list = '';
      if (!is_home()) { 
        // look to see if we have a post id meta attribute
    	  $asin_list = get_post_meta($post->ID,SGW_POST_META_KEY,true);
      }
      // if we don't have asin values, need to fetch the defaults
      if (!$asin_list) {
        $opts = get_option(SGW_PLUGIN_OPTTIONS);
        $asin_list = $opts['default'];
      }
      $asins = $this->split_asin_list($asin_list);
      if (count($asins) < $display_count) {
        $display_count = 1;
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
    }  // end of affiliate test
    echo $after_widget; 
	}

    /** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
	    return $new_instance;
	}

	// outputs the options form on admin
    /** @see WP_Widget::form */
	function form($instance) {
    // load the vars
    $title = esc_attr($instance['title']);
    $affiliate = esc_attr($instance['affiliate']);
    $country = esc_attr($instance['country']);
    if (!$country) { $country = 'us'; }
      
    $display_count = esc_attr($instance['display_count']);
    if (!$display_count) { $display_count = 1; }
?>
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('affiliate'); ?>"><?php _e('Affiliate ID:'); ?>
          <input class="widefat" id="<?php echo $this->get_field_id('affiliate'); ?>" name="<?php echo $this->get_field_name('affiliate'); ?>" type="text" value="<?php echo $affiliate; ?>" /></label>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('country'); ?>"><?php _e('Affiliate Country:'); ?>
          <select name="<?php echo $this->get_field_name('country'); ?>">
<?php
  $countries = array('us' => 'United States', 'uk' => 'United Kingdon', 'de' => 'Germany', 'fr' => 'France', 'ca' => 'Canada');
  foreach ($countries as $key=>$val) {
    $sel = '';
    if ($country==$key) { $sel = 'selected="selected"'; }
    printf("<option value='%s' %s>%s</option>",$key,$sel,$val);
  }
?>          
          </select>
          </label>
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
