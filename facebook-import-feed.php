<?php
/*
* Plugin Name: Facebook Import Feed 
* Plugin URI: http://jaocreation.fr/
* Description: Facebook import feed.
* Version: 1.2 
* Author: Blaise Bruno
* Author URI: http://jaocreation.fr/
* License: GPL
*/ 
 
class facebook_import_feed_plugin extends WP_Widget {

	// constructor
	function facebook_import_feed_plugin() {
	        parent::WP_Widget(false, $name = __('Facebook Feed Import', 'wp_widget_plugin') );
    
	}

// widget form creation
function form($instance) {

// Check values
if( $instance) {
     $title = esc_attr($instance['title']);
     $textarea = $instance['textarea'];
} else {
     $title = '';
     $textarea = '';
}
?>

<p>
<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>


<p>
<label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Description:', 'wp_widget_plugin'); ?></label>
<textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>" rows="7" cols="20" ><?php echo $textarea; ?></textarea>
</p>
<?php
}

	
function update($new_instance, $old_instance) {
      $instance = $old_instance;
// Fields
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['textarea'] = strip_tags($new_instance['textarea']);
     return $instance;
	 
	 
	 
}
	

// display widget
function widget($args, $instance) {
   extract( $args );
   
// these are the widget options
   $title = apply_filters('widget_title', $instance['title']);
    $textarea = $instance['textarea'];
	$row ='';
   echo $before_widget;

// Display the widget
   echo '<div class="widget-text wp_widget_plugin_box">';
   echo '<div class="widget-title">';
   
// Check if title is set
   if ( $title ) {
   echo  $before_title . $title . $after_title ;
   }
   echo '</div>';
  
// Check if textarea is set
   echo '<div class="widget-textarea">';
   if( $textarea ) {
   echo '<p class="wp_widget_plugin_textarea" style="font-size:15px;">'.$textarea.'</p>';
   }
   echo '</div>';
   echo '</div>';
   
//Display Feed
   
   global $wpdb;
   $table_name = $wpdb->prefix . 'facebook_importfeel';
  
   $lastid = $wpdb->get_col("SELECT ID FROM $table_name ORDER BY ID DESC LIMIT 0 , 1" );
   $row = $wpdb->get_row( $wpdb->prepare('SELECT * FROM '.$table_name.' WHERE id = %d', $lastid) );
   
   if( $row->engine ) {
   echo '<p class="wp_widget_plugin_textarea" style="font-size:15px;">'.$row->engine.'</p>';
   }
   echo $after_widget;
   
}

}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("facebook_import_feed_plugin");')); 



// create table
function fif_create_table() {
global $wpdb;
$table_name = $wpdb->prefix . "facebook_importfeel";



    $sql = 'CREATE TABLE ' . $table_name . ' (
    id int(11) NOT NULL AUTO_INCREMENT,
    engine longtext NOT NULL,
    UNIQUE KEY (id))';

    //reference to upgrade.php file
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

}
register_activation_hook( __FILE__, 'fif_create_table' );
//End Create TAble







// Creating the plugin's administration page
if (is_admin()){
function fib_admin() {
    include('fibwp_admin.php');
}


function fib_actions() {
 
 add_options_page("Facebook Import Feed", "Facebook Import Feed", 1, "Config_fib", "fib_admin");
 
}
 
add_action('admin_menu', 'fib_actions');
}

//Cron

register_activation_hook( __FILE__, 'wi_create_daily_backup_schedule' );
function wi_create_daily_backup_schedule(){
 
  $timestamp = wp_next_scheduled( 'wi_create_daily_backup' );

 
  if( $timestamp == false ){
    
    wp_schedule_event( time(), 'hourly', 'wi_create_daily_backup' );
  }
}


//Hook our function , wi_create_backup(), into the action wi_create_daily_backup
add_action( 'wi_create_daily_backup', 'wi_create_backup' );
function wi_create_backup(){
 global $wpdb;
 global $returnMarkup;
 global $idfeed, $rowsold, $rowold;
 $idfeed = get_option('fib_id');
	
  	
    ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
    
    $rssUrl = "http://www.facebook.com/feeds/page.php?id=$idfeed&format=rss20";
    $xml = simplexml_load_file($rssUrl); // Load the XML file
    
    $entry = $xml->channel->item;
   
    for ($i = 0; $i < 1; $i++) {
       $returnMarkup .= "<h4 style='font-size:12px; color:#aaaaaa'>".$entry[$i]->title."</h4>";
	   $returnMarkup .= "<p style='font-size:10px; color:#aaaaaa'>".$entry[$i]->description."</p>"; 
       
    }
  
   $table_name = $wpdb->prefix . "facebook_importfeel";       
   $lastid = $wpdb->get_col("SELECT ID FROM $table_name ORDER BY ID DESC LIMIT 0 , 1" );
   $rowsold = $wpdb->get_row( $wpdb->prepare('SELECT * FROM '.$table_name.' WHERE id = %d', $lastid) );
   $rowold = $rowsold->engine;
   
  similar_text($rowold, $returnMarkup, $percent); 
   if ($percent < 90)  {
    $wpdb->insert($table_name, array('engine' => $returnMarkup) ); 
   
   } else {};
   
}



// delete row
function action_delete() {
?>
<script>
jQuery( document ).ready( function( $ ) {
	$('.delete').click(function() { 
		
		var data = {
		action: 'delete',
		
		
		};
	
        $.post(ajaxurl, data, function(response) {
            alert('Got this from the server: ' + response);
        });	 
		
	});
});
</script>
<?php
}

add_action( 'admin_footer', 'action_delete' );
 

function action_delete_callback() {
global $wpdb;
$table_name = $wpdb->prefix.'facebook_importfeel';
$wpdb->query("TRUNCATE TABLE ".$table_name);
die(); 
}
add_action( 'wp_ajax_delete', 'action_delete_callback' );



//Add row
function action_deletes() {
?>
<script>
jQuery( document ).ready( function( $ ) {
	$('.addrow').click(function() { 
		
		var data = {
		action: 'addrow',
		
		
		};
	
        $.post(ajaxurl, data, function(response) {
            alert('Got this from the server: ' + response);
        });	 
		
	});
});
</script>
<?php
}

add_action( 'admin_footer', 'action_deletes' );
 

function action_delete_callbacks() {
	global $wpdb;
	global $returnMarkup;
    global $idfeeds, $rowsold, $rowold;
    $idfeeds = get_option('fib_id');
	
  	
    ini_set('user_agent', 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9');
    
    $rssUrl = "http://www.facebook.com/feeds/page.php?id=$idfeeds&format=rss20";
    $xml = simplexml_load_file($rssUrl); // Load the XML file
  
    $entry = $xml->channel->item;
   
    for ($i = 0; $i < 1; $i++) {
        $returnMarkup .= "<h4 style='font-size:12px; color:#aaaaaa'>".$entry[$i]->title."</h4>";
		$returnMarkup .= "<p style='font-size:10px; color:#aaaaaa'>".$entry[$i]->description."</p>"; // Title of the update
		
       
    }
// get old feed  
   $table_name = $wpdb->prefix . "facebook_importfeel";
   $lastid = $wpdb->get_col("SELECT ID FROM $table_name ORDER BY ID DESC LIMIT 0 , 1" );
   $rowsold = $wpdb->get_row( $wpdb->prepare('SELECT * FROM '.$table_name.' WHERE id = %d', $lastid) );
   $rowold = $rowsold->engine;
   
// ComparefFeed 
   similar_text($rowold, $returnMarkup, $percent); 
   if ($percent < 90)  {
    $wpdb->insert($table_name, array('engine' => $returnMarkup) ); 
   } 
   else {};
  
   die(); 
}
add_action( 'wp_ajax_addrow', 'action_delete_callbacks' );

//delete cron  deactivation
register_deactivation_hook( __FILE__, 'wi_remove_daily_backup_schedule' );
function wi_remove_daily_backup_schedule(){
wp_clear_scheduled_hook( 'wi_create_daily_backup' );
}

//delete table  deactivation
register_deactivation_hook(__FILE__ , 'fif_deactivate' ); 
function  fif_deactivate() {
global $wpdb;	
$table_name = $wpdb->prefix . "facebook_importfeel";
$wpdb->query("DROP TABLE IF EXISTS $table_name");	
}

// [fb_ipf]
function fb_ipf_func( $atts ) {
  
   
   global $wpdb;
   $table_name = $wpdb->prefix . 'facebook_importfeel';
  
   $lastid = $wpdb->get_col("SELECT ID FROM $table_name ORDER BY ID DESC LIMIT 0 , 1" );
   $row = $wpdb->get_row( $wpdb->prepare('SELECT * FROM '.$table_name.' WHERE id = %d', $lastid) );
   
   if( $row->engine ) {
   echo '<p class="wp_widget_plugin_textarea" style="font-size:15px;">'.$row->engine.'</p>';
   }
}
add_shortcode( 'fb_ipf', 'fb_ipf_func' );



?>