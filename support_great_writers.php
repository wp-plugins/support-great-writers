<?php
/*
Plugin Name: Support Great Writers
Plugin URI: http://dimax.com
Description: Side-Bar Widget to support Great Writers
Author: Richard Luck
Version: 1.0
Author URI: http://dimax.com
*/

class SupportGreatWriters extends WP_Widget {

    /** constructor */
	function SupportGreatWriters() {
		// widget contructor
        parent::WP_Widget(false, $name = 'SupportGreatWriters');	
	}

    /** @see WP_Widget::widget */
	function widget($args, $instance) {
		// outputs the content of the widget
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $affiliate = apply_filters('widget_affiliate', $instance['affiliate']);
        $asin1 = apply_filters('widget_asin1', $instance['asin1']);
        $asin2 = apply_filters('widget_asin2', $instance['asin2']);

// start the output
?>

<?php echo $before_widget; ?>
<?php if ( $title ) {
    echo $before_title . $title . $after_title; 
} ?>
<?php if ( $affiliate ) { ?>
<TABLE id="support_great_writers" style="margin:0px auto;">
    <tr>
    <td style="width:50%;"><?php if ($asin1) { ?>
        <a target=_blank title="Find out more about this great writer"  
        href="http://www.amazon.com/gp/product/<?php echo $asin1; ?>?ie=UTF8&tag=<?php echo $affiliate ?>&linkCode=as2&camp=1789&creative=9325&creativeASIN=<?php echo $asin1; ?>">
<img border="0" src="http://images.amazon.com/images/P/<?php echo $asin1; ?>.01._SCMZZZZZZZ_.jpg"></a>
<img src="http://www.assoc-amazon.com/e/ir?t=<?php echo $affiliate ?>&l=as2&o=1&a=<?php echo $asin1; ?>" 
width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />
<?php } ?></td>
<td style="width:50%;"><?php if ($asin2) { ?>
<a target=_blank title="Find out more about this great writer"  
href="http://www.amazon.com/gp/product/<?php echo $asin2; ?>?ie=UTF8&tag=<?php echo $affiliate ?>&linkCode=as2&camp=1789&creative=9325&creativeASIN=<?php echo $asin2; ?>">
<img border="0" src="http://images.amazon.com/images/P/<?php echo $asin2; ?>.01._SCMZZZZZZZ_.jpg"></a>
<img src="http://www.assoc-amazon.com/e/ir?t=<?php echo $affiliate ?>&l=as2&o=1&a=<?php echo $asin2; ?>" 
width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />
<?php } ?></td>
</tr></table>
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
        $title = esc_attr($instance['title']);
        $affiliate = esc_attr($instance['affiliate']);
        $asin1 = esc_attr($instance['asin1']);
        $asin2 = esc_attr($instance['asin2']);
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
          <label for="<?php echo $this->get_field_id('asin1'); ?>"><?php _e('ASIN 1:'); ?>
          <input class="widefat" id="<?php echo $this->get_field_id('asin1'); ?>" name="<?php echo $this->get_field_name('asin1'); ?>" type="text" value="<?php echo $asin1; ?>" /></label>
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('asin2'); ?>"><?php _e('ASIN 2:'); ?>
          <input class="widefat" id="<?php echo $this->get_field_id('asin2'); ?>" name="<?php echo $this->get_field_name('asin2'); ?>" type="text" value="<?php echo $asin2; ?>" /></label>
        </p>
          
<?php           
          
	}
} // end class

//  register_widget('SupportGreatWriters');
// register FooWidget widget
add_action('widgets_init', create_function('', 'return register_widget("SupportGreatWriters");'));

?>
