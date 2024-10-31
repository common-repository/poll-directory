<?php
/* 
 * Plugin Name:   Poll Directory
 * Version:       2.1.0
 * Plugin URI:    http://www.holypoll.com/
 * Description:   Add this plugin to gain access to hundreds of pre-made user polls.  Just choose a category and your site visitors will be able to participate in an ever changing collection of pre-made polls.
 * Author:        Dimbal Software
 * Author URI:    http://www.dimbal.com/
 */
if ( ! defined( 'ABSPATH' ) ) exit();	// sanity check

//error_reporting(E_ALL);
//ini_set('display_errors','On');

//Let's grab the data from the text file and display it
$dmbl_pd_start_time = microtime();

//These are the categories that correspond to matching Zones in the Poll System
$dmbl_pd_categories = array();
$dmbl_pd_categories[]=array('id'=>'11','title'=>'Movies');
$dmbl_pd_categories[]=array('id'=>'2','title'=>'Music');
$dmbl_pd_categories[]=array('id'=>'9','title'=>'Television');
$dmbl_pd_categories[]=array('id'=>'3','title'=>'Technology');
$dmbl_pd_categories[]=array('id'=>'5','title'=>'Food');
$dmbl_pd_categories[]=array('id'=>'1','title'=>'Sports');
$dmbl_pd_categories[]=array('id'=>'4','title'=>'Politics');
$dmbl_pd_categories[]=array('id'=>'10','title'=>'Random');
$dmbl_pd_categories[]=array('id'=>'8','title'=>'Pets');
$dmbl_pd_categories[]=array('id'=>'6','title'=>'Places/Travel');

$dmbl_pd_end_time = microtime();

$dmbl_pd_time_diff = $dmbl_pd_end_time - $dmbl_pd_start_time;

register_activation_hook(__FILE__, 'dmbl_pd_activate');

//Function that is run when the plugin is activated
function dmbl_pd_activate(){
	
}

/*
 * This will be the function that translates the shortcode into something useful.  
 * In this case it will be a placeholder DIV and proper element information so that the JS can render it via AJAX
 * RETURN the HTML to be put in place of the shortcode
 */
function dmbl_pd_shortcode_handler($atts){
	$dpmzone = $dpmzonedisplayall = $zoneId = '';
	extract( shortcode_atts( array(
		'dpmzone' => '0',
		'dpmpoll' => '0',
		'dpmzonedisplayall' => 'false'
		), $atts ) );
	
	$rand = rand(0,10000);
    //At this point I now have the variables from the shortcode... now what do I want to do with them?? Build the HTML?
    $html = '<div id="dmbl_pd_embed_container_'.$rand.'" class="dpmWidgetWrapper" dpmZone="'.$dpmzone.'" dpmZoneDisplayAll="'.$dpmzonedisplayall.'"><a href="http://www.dimbal.com" title="The best Poll Management Software">Poll Management Software by Dimbal Software</a></div>';
    
    $html = '<div class="hpWidgetWrapper" hpZone="'.$zoneId.'">Loading poll from <a href="http://www.holypoll.com">HolyPoll</a>.</div>';
    
    return $html;
}


//Grab a random item and display it
function dmbl_pd_display_zone($dmbl_pd_zone_id){
	//Curl out to Dimbal and make the request
	$dmbl_pd_url = 'http://www.holypoll.com/poll/ajax.php?zone='.$dmbl_pd_zone_id;
	$dmbl_pd_response = wp_remote_request($dmbl_pd_url, array('headers' => array('user-agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)')));
    if(is_wp_error($dmbl_pd_response)){
	    return '';
    }elseif($dmbl_pd_response['response']['code'] != 200){
	    return '';
    }else{
	    return $dmbl_pd_response['body'];
    }
}

//Widget class for determining when to display the Glossary Term
class DMB_PD_Widget extends WP_Widget {

	public function __construct() {
		// widget actual processes
		parent::__construct(
	 		'dmbl_pd', // Base ID
			'Dimbal Poll Directory', // Name
			array( 'description' => __( 'Add this widget to display pre-made random polls Courtesy of HolyPoll.com and the Dimbal Poll Directory.  Choose your desired topic and then you are good to go.', 'text_domain' ), ) // Args
		);
	}

 	public function form( $instance ) {
		// outputs the options form on admin
		global $dmbl_pd_categories;
		$catId = false;
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
			$catId = $instance['dmbl_pd_cat_id'];
		}
		else {
			$title = __( 'User Polls' );
		}
?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		<br />
		<br />
		<label for="<?php echo $this->get_field_id( 'dmbl_pd_cat_id' ); ?>"><?php _e( 'Category:' ); ?></label> 
		<select id="<?php echo $this->get_field_id( 'dmbl_pd_cat_id' ); ?>" name="<?php echo $this->get_field_name( 'dmbl_pd_cat_id' ); ?>">
<?php 
			foreach($dmbl_pd_categories as $dmbl_pd_category){
				$selected='';
				if($catId == $dmbl_pd_category['id']){
					$selected=' selected="selected"';
				} 
				echo '<option value="'.$dmbl_pd_category['id'].'"'.$selected.'>'.$dmbl_pd_category['title'].'</option>';
			}				
?>
		</select>
		</p>
<?php 
	}

	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['dmbl_pd_cat_id'] = strip_tags( $new_instance['dmbl_pd_cat_id'] );
		return $instance;
	}

	public function widget( $args, $instance ) {
		// outputs the content of the widget
		
		//Title information
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}

		//Translate Ids:
		$zoneId = $instance['dmbl_pd_cat_id'];
		$idTable = array(
			'20'=>'11',
			'21'=>'2',
			'22'=>'9',
			'23'=>'3',
			'24'=>'5',
			'28'=>'1',
			'29'=>'4',
		);
		if(array_key_exists($zoneId, $idTable)){
			$zoneId = $idTable[$zoneId];	
		}
		
		echo '<div class="hpWidgetWrapper" hpZone="'.$zoneId.'">Loading poll from <a href="http://www.holypoll.com">HolyPoll</a> and the <a href="http://www.dimbal.com">Dimbal Poll Manager</a>.</div>';
		echo '<script id="hpScript" src="http://www.holypoll.com/poll/hp.js" type="text/javascript"></script>';
		
	}

}

//Now register the widgets into the system
function dimbal_pd_register_widgets() {
	register_widget( 'DMB_PD_Widget' );
}
add_action( 'widgets_init', 'dimbal_pd_register_widgets' );


//ADMIN MENU PAGE
add_action( 'admin_menu', 'dimbal_pd_plugin_menu' );
function dimbal_pd_plugin_menu() {
	add_options_page( 'Dimbal Poll Directory Settings', 'Dimbal Poll Directory', 'manage_options', 'dimbal-pd-plugin-options', 'dimbal_pd_plugin_options' );
}
function dimbal_pd_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	
	?>
	<div style="float:right; margin: 10px 10px 0 0"><a href="http://www.dimbal.com"><img src="http://www.dimbal.com/images/logo_300.png" alt="Dimbal Software" /></a></div>
	<h1>The Dimbal Poll Directory</h1>
	<p style="font-style:italic; font-size:larger;">Easily add pre-made Polls to your website in 3 simple steps.</p>
	<hr />
	<div style="display:table; width:100%;">
		<div style="display:table-cell; width:auto; vertical-align:top;">
			<!-- LEFT SIDE CONTENT -->
			<div style="display:table-cell; vertical-align:middle;"><a href="http://www.dimbal.com/website-software/dimbal-poll-manager/"><img src="http://www.dimbal.com/images/dpm-software-box-100.png" style="margin:10px;" /></a></div>
			<div style="display:table-cell; vertical-align:middle;">
			<h4>Powered by the FREE Dimbal Poll Manager</h4>
			<p>Did you know that this plugin is powered by the FREE Dimbal Poll Manager?  Learn how this simple, downloadable software can be used to create custom polls for your website or blog.   <a href="http://www.dimbal.com/website-software/dimbal-poll-manager/">More Information.</a>  Poll content hosted by <a href="http://www.holypoll.com">HolyPoll.com</a></p>
			</div>
			<hr style="clear:both;" />
			<h4>Usage Instructions</h4>
			<p>This plugin comes ready to use right out of the box.  Below are some tips to help you include a pre-made poll in your posts.</p>
			<p>1. On the Widget Settings page within Wordpress, find the Dimbal Poll Directory widget and drag it to the sidebar location of your choosing.</p>
			<p><img src="http://www.dimbal.com/images/dimbal-poll-directory-widget.png" style="vertical-align:middle; margin:10px; border:1px solid black;" /></p>
			<p>2. Using the options that appear choose (1) a Heading for the Polls as well as (2) the Poll Group you want to select polls from.  Click the save button once finished.</p>
			<p><img src="http://www.dimbal.com/images/dimbal-poll-directory-options.png" style="vertical-align:middle; margin:10px; border:1px solid black;" /></p>
			<p>3. That's it.  The polls should now appear on your Wordpress site.</p>
			<p><img src="http://www.dimbal.com/images/dimbal-poll-directory-poll.png" style="vertical-align:middle; margin:10px; border:1px solid black;" /></p>
			<br /><br />
		</div>
		<div style="display:table-cell; width:300px; padding-left:20px; vertical-align:top;">
			<!-- RIGHT SIDE CONTENT -->
			<h4>Did you like this Plugin?  Please help it grow.</h4>
			<div style="text-align:center;"><a href="http://wordpress.org/support/view/plugin-reviews/poll-directory">Rate this Plugin on Wordpress</a></div>
			<br />
			<div style="text-align:center;">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="5GMXFKZ79EJFA">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
			<hr />
			<h4>Follow us for Free Giveaways and more...</h4>
			<div id="fb-root"></div>
			<script type="text/javascript">
			  // Additional JS functions here
			  window.fbAsyncInit = function() {
			    FB.init({
			      appId      : '539348092746687', // App ID
			      //channelUrl : '//<?=(URL_ROOT)?>channel.html', // Channel File
			      status     : true, // check login status
			      cookie     : true, // enable cookies to allow the server to access the session
			      xfbml      : true,  // parse XFBML
			      frictionlessRequests: true,  //Enable Frictionless requests
			    });
			  };

			  // Load the SDK Asynchronously
			  (function(d){
			     var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			     if (d.getElementById(id)) {return;}
			     js = d.createElement('script'); js.id = id; js.async = true;
			     js.src = "//connect.facebook.net/en_US/all.js";
			     ref.parentNode.insertBefore(js, ref);
			   }(document));
			</script>
			<div style="text-align:center;"><div class="fb-like" data-href="https://www.facebook.com/dimbalsoftware" data-send="false" data-layout="standard" data-show-faces="false" data-width="200"></div></div>
			<hr />
			<h4>Questions?  Support?  Record a Bug?</h4>
			<p>Need help with this plugin? Visit...</p>
			<p><a href="http://www.dimbal.com/support">http://www.dimbal.com/support</a></p>
			<hr />
			<h4>Other great Dimbal Products</h4>
			<div class="dbmWidgetWrapper" dbmZone="9"></div>
			<div class="dbmWidgetWrapper" dbmZone="10"></div>
			<div class="dbmWidgetWrapper" dbmZone="11"></div>
			<a href="http://www.dimbal.com">Powered by the Dimbal Banner Manager</a>
			<script id="dbmScript" src="http://www.dimbal.com/dimbal/apps/dbm/banner/dbm.js" type="text/javascript"></script>
		</div>
	</div>
	<?
}
