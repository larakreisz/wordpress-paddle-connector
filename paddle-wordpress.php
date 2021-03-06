<?php

/*
 * The plugin bootstrap file
 * 
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                https://github.com/larakreisz
 * @since               December 07, 2021
 * @package             Lara Rest Plugin
 * 
 * @wordpress-plugin
 * Plugin Name:         WordPress Paddle Plugin
 * Plugin URI:          https://test.cuteberry.de/
 * Description:         The plugin connects WordPress and Paddle and allows you to perform custom action after payment.
 * Version:             1.0
 * Author:              Lara Kreisz
 * Author URI:          https://github.com/larakreisz
 */
if (!defined('WPINC'))
    die;

/**
 * Define plugin name to use as global inside the plugin files
 * Rename this for plugin and update it as you required to change the plugin name for new versions.
 */


// 0. WordPress Log Function --------- used to log $data value on line 113

if (!function_exists('write_log')) {
	function write_log ( $log )  {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}




// 1. Enqueue Paddle Checkout
 
 add_action( 'wp_enqueue_scripts', 'enqueue_paddle_scripts' );
 function enqueue_paddle_scripts() {
    wp_register_script( 'paddle-js', 'https://cdn.paddle.com/paddle/paddle.js', null, null, true );
    wp_enqueue_script( 'paddle-js' );
 }
 


 // 2. Connect Paddle and WordPress
define('LARA_CONNECTOR_NAMESPACE', "lara-connector/v1");

add_action('rest_api_init', 'register_routes');
function register_routes() {
register_rest_route(LARA_CONNECTOR_NAMESPACE, '/paddlewebhooks/task', array(
   'methods' => 'POST',
   'callback' => 'paddlewebhooks_task_action',
   'permission_callback' => '__return_true'
    ));
}



function paddlewebhooks_task_action() {

// Your Paddle 'Public Key' Example
  $public_key_string =
"-----BEGIN PUBLIC KEY-----" . "\n" .
"MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEArgxnuC+6pyfQHBXDBbEm" . "\n" .
"ZObmsyVcgH08M7h9mg/m3bMPq0/9bUksOsfh0fQpeKH+bwMsScvQH5QWIpUjVKE+" . "\n" .
"RFeKLPJZsnbBxY6e+5WYrJjt1heM3qMWqXgAXaL00ZRIuBkkZVN0y672OhaoumiO" . "\n" .
"jaIkTjJQ5+lZ0XVvASZ4esvi7RGA4RIU3sqlqe4bRpVAmmdpME4TvmYvOpNwJnII" . "\n" .
"AoHB4V3DXDWe6ex31HNrEREMlqaMoFvDhe8QpzELbYIoNxX/R9aWZdJzWeKWT8+e" . "\n" .
"fD+si/Wy81CtRL13gvaG+Fcd22imzGJf429jV0RVr3iUKYeMplpHdBqo/wW2jkhm" . "\n" .
"xtKbNE2KOmrXUBR8kXa1TYN35MQhCiYKMhuOFrN0Qv3ckx0EkcCK5yHDU2BL33hr" . "\n" .
"E0fTZPgM6xGJZxaQJdDGiUFcmMrANugi4RXP5USyRSiT5D2fCQ2obLqnK75c+STI" . "\n" .
"iYcb1iJI5XiLvBbCRFx8Up7mQqg3++M8IXH++xzg5cNP8fpGKDDqeRdeK5LIiOMn" . "\n" .
"pi7DL5WDS++0TmuQEcKL8qqMSA0Aq7YC56A4iyp8vlmDm0gZ+Y/VhDdeoveqBx5i" . "\n" .
"PXp+PTANT1OxggDBNxxrHWTEJi+b2rb6zpJSwpmBnJucIqF2O/fjfvbulrfM8yE6" . "\n" .
"OBW7gABUz0h8ik1CLgzo4okCAwEAAQ==" . "\n" .
"-----END PUBLIC KEY-----";

  $public_key = openssl_get_publickey($public_key_string);
  
  // Get the p_signature parameter & base64 decode it.
$signature = base64_decode($_POST['p_signature']);	

  
  
  // Get the fields sent in the request, and remove the p_signature parameter
  $fields = $_POST;
  unset($fields['p_signature']);
  
  // ksort() and serialize the fields
  ksort($fields);
  foreach($fields as $k => $v) {
	  if(!in_array(gettype($v), array('object', 'array'))) {
		  $fields[$k] = "$v";
	  }
  }
  $data = serialize($fields);
  
  // log $data value !!!
  write_log($data);
  
  // Verify the signature
  $verification = openssl_verify($data, $signature, $public_key, OPENSSL_ALGO_SHA1);
  
     // log $verification value !!!
  write_log($verification);


  

  

	
	
	
/* ---------------------------------------------------------------------------------------------------------
//custom code


  $test = "Ausgangspunkt";
  if($verification == 1) {
	  $test = 'verification success';
  } else {	  
	  $test = 'verification NOT success';
  }	



$data = $_POST;
$passthrough = json_decode(stripslashes($data['passthrough']), true);
$locationID = $passthrough["reitanlage"];
$eventID = $passthrough["veranstaltung"];
			
update_post_meta($eventID, 'wpcf-paddle-payment-location-id', $locationID . '[success]');
update_post_meta($eventID, 'wpcf-paddle-payment-event-id', $eventID . '[success]');
	
if (isset($data["product_id"]) && !empty($data["product_id"])) {	
update_post_meta($eventID, 'wpcf-paddle-payment-product-id', $data["product_id"] . '[success]');	
}

if (isset($data["email"]) && !empty($data["email"])) {
update_post_meta($eventID, 'wpcf-paddle-payment-email', $data["email"] . '[success]' . ' / ' . $verification);
}
	
------------------------------------------------------------------------------------------------- */
   
echo json_encode([
        "code" => 200,
        "status" => "Success",
        "message" => "Meta data updated successfully.",
        "data" => $fields
    ]);
    exit;	  
	

}
