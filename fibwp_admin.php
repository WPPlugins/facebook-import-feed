<?php

    if($_POST['fib_hidden'] == 'Y') {
        //Form data sent
        $fifid = $_POST['fib_id'];
        update_option('fib_id', $fifid);
         
       
        ?>
        <div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>
        <?php
    } else {
        $fifid = get_option('fib_id');
    }
?>
<div class="wrap">
    <?php    echo "<h2>" . __( 'Facebook Import Feed Options', 'fib_trdom' ) . "</h2>"; ?>
     
    <form name="fib_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <input type="hidden" name="fib_hidden" value="Y">
        <?php    echo "<h4>" . __( 'Facebook Import Feed Settings', 'fib_trdom' ) . "</h4>"; ?>
        <p><?php _e("Facebook ID: " ); ?>
        <input type="text" name="fib_id" value="<?php echo $fifid; ?>" size="20"><?php _e(" ex:123456789123" ); ?>
        </p>
        
        <p>How to find your Facebook ID : <br>
        Past your page url facebook here : <br>
        http://findmyfacebookid.com/
        </p>
        <hr />
        <p>Your Feed : (Update Once an hour)  </p><br>
         <hr />
         <p>Your Shortcode :   
        [fb_ipf]</p>
        <hr />
        <?php
    global $wpdb;
	$table_name = $wpdb->prefix . 'facebook_importfeel';
    $rows = $wpdb->get_results( "SELECT * FROM ".$table_name);
    echo '
    <table>
        <tr>
            <td>id</td>
            <td>Feed</td>
        </tr>
    ';
    foreach ( $rows as $row )  {
        echo '
        <tr>
            <td>'.$row->id.'</td>
            <td>'.$row->engine.'</td>
        </tr>
        ';
	}
    echo '</table>';
	
	
?>
        <hr />
        <p class="submit">
        <input type="submit" name="Submit" value="<?php _e('Update Options', 'fib_trdom' ) ?>" />
        </p>
        
        
    </form>
     <hr />
<?php    echo "<h2>" . __( 'Add or Delete Feeds', 'fib_trdom' ) . "</h2>"; ?>
<a href="" class="addrow">Add Feed (Refresh page after click.)</a><hr />   
<a href="" class="delete">Delete Feeds</a><hr />
</div>
