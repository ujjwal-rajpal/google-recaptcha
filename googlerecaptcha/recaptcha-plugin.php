<?php 
/**
  *@package stackoverflowplugin
 */
 /**
 * Plugin Name:       WP Login Form with reCAPTCHA
 * Plugin URI:        https://mindfiresolutions.com
 * Description:       creating googleRecaptcha plugin
 * Version:           1.0
 * Author:            Ujjwal Rajpal and Aftab Alam
 * Author URI:        https://mindfiresolutions.com 
 * License:           GPLv2 or later
 * License URI:       https://mindfiresolutions.com
 * Text Domain		  googleRecaptcha-plugin
 */


 /*  © Copyright 2018  recaptcha plugin  ( https://mindfiresolutions.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
  *Function to verify that user has enter the site key and secret 
  *if he entered only then then the captcha appears on login page
  */	


function recaptcha_has_configured() {


	$sitekey = get_option( 'recaptcha_sitekey' );
	$secretkey = get_option( 'recaptcha_secretkey' );
	
	if ( $sitekey && $secretkey ) 
	{
		return true;
	}
	else 
	{
		return false;
	}
}


/* check user enters the ite secret and key*/
if ( recaptcha_has_configured() )
{
	/**
	  * Function to load  captcha js on login page
	  *
	  */
	 
	function captcha_scripts() 
	{	
		wp_enqueue_script('custom','https://www.google.com/recaptcha/api.js',null,1.0,true);
	}
		add_action( 'login_enqueue_scripts', 'captcha_scripts' );


	/**
	 * Function that creates a login captcha on login page
	 *
	 */
	add_action('login_form','captcha_login_field');
	function captcha_login_field() {
		?>
	    <p>
	        <div style = "margin-left: -25px;padding: 10px;" class="g-recaptcha" data-sitekey="<?php echo get_option('recaptcha_sitekey'); ?>"></div>
	    </p>
		<?php
	}
	
	/**
	 * function that verifies user that it is a human or a bot 
	 *
	 */
	add_filter( 'wp_authenticate_user', 'verify_recaptcha_on_login', 10, 2 );
	function verify_recaptcha_on_login( $user, $password ) {
		
		if ( isset( $_POST['g-recaptcha-response'] ) ) {
			
			$secret_key = get_option( 'recaptcha_secretkey' );

			$response = wp_remote_get( 'https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response=' . $_POST['g-recaptcha-response'] );

			$response = json_decode($response['body'], true);

			if ( true == $response['success'] ) { 
				return $user;
			} else {
				// FIXME: This one fires if your password is incorrect... Check if password was incorrect before returning this error...
		 		return new WP_Error( 'Captcha Invalid', __('<strong>ERROR</strong>: You are a bot') );
			}


			} else {
				return new WP_Error( 'Captcha Invalid', __('<strong>ERROR</strong>: You are a bot. If not then enable JavaScript.') );
			}
	}
}


/**
  * Function to create a setting page in admin setting menu 
  *
  */

add_action( 'admin_menu', 'recaptcha_create_menu' );
function recaptcha_create_menu() {

	add_options_page( 'reCAPTCHA Settings', 'reCAPTCHA Plugin Settings', 'administrator', 'recaptcha', 'recaptcha_settings_page');
}

/**
  * Function to update site key and secret in database
  *
  */


function recaptcha_settings_page() {
		if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
			
			update_option( 'recaptcha_sitekey', $_POST["sitekey"] );
			update_option( 'recaptcha_secretkey', $_POST["secretkey"] ); 
		}


	?>
	<div class="wrap">
		<h1>ReCAPTCHA Plugin Setting</h1>
		<p><span style="color:red;  margin:-5px; font-size:12px ; ">Enter your site key and secret carefully</span></p>
		<p>Please follow this <a href="https://www.google.com/recaptcha/intro/v3.html">link</a> to get your sitekey and site secret</p>

	</div>
    <form method="post" action="">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="sitekey"> Enter Site Key</label>
					</th>
					<td>
						<input type="text" name="sitekey" id="sitekey"  value="<?php echo get_option( 'recaptcha_sitekey', '' ); ?>" >
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="secretkey"> Enter Secret Key</label>
					</th>
					<td>
						<input type="text" name="secretkey" id="secretkey" value="<?php echo get_option( 'recaptcha_secretkey', '' ); ?>"  >					</td>
				</tr>

		</table>
      	
      	<p>
      		<?php submit_button( $name = "save"); ?>
      	</p>
      	
    </form>

<?php
}

/**
  * Function to remove values from database when one deactivate the plugin
  *
  */

register_deactivation_hook( __FILE__, 'recaptcha_dactivation' );
function recaptcha_dactivation()
{
	delete_option('recaptcha_sitekey');
	delete_option('recaptcha_secretkey');
}


