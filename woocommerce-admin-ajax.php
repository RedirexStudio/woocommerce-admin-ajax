<?php
/**
 * woocommerce-admin-ajax
 *
 * @package           woocommerce-admin-ajax
 * @author            Sergey Samokhvalov
 * @wordpress-plugin
 *
 * Plugin Name:       WooCommerce Admin Ajax
 * Plugin URI:        https://redirex.studio
 * Description:       Additional functionality for ajax update of product in admin page without reload page.
 * Version:           1.0
 * Requires PHP:      5.6.20
 * Author:            Sergey Samokhvalov
 * Author URI:        https://github.com/RedirexStudio/woocommerce-admin-ajax
 * Text Domain:       Woocommerce Admin Ajax
 */

/* Registrate admin js and styles */
  require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
  add_action( 'admin_enqueue_scripts', 'reg_amin_js' );
  function reg_amin_js( $page ) {
    // change to the $page where you want to enqueue the script
    if( $page == 'post-new.php' || $page == 'post.php' || $page == 'edit.php' ) {
      // Enqueue WordPress media scripts
      wp_enqueue_media();
      
      // Enqueue custom script that will interact with wp.media
      wp_enqueue_script( 'woocommerce_admin-scripts', plugins_url('/admin/js/admin.js',__FILE__ ), array('jquery') );
      // Enqueue custom styles for admin panel
      wp_enqueue_style('woocommerce_admin-styles', plugins_url('/admin/css/style.css', __FILE__ ));
      
    }
  }
/* //END//Registrate admin js and styles */

/* Ajax Requests */
  require 'requests.php';
/* //END//Ajax Requests */

/**
 * Polylang capability
 * Register some string from the customizer to be translated with Polylang
 */
  function plugin_pll_register_string() {
    if ( function_exists( 'pll_register_string' ) ) {
      pll_register_string('woocommerce-admin-ajax-strings', 'Update');
      pll_register_string('woocommerce-admin-ajax-strings', 'Published');
      pll_register_string('woocommerce-admin-ajax-strings', 'Product is updated!');
    }
  }
  add_action( 'admin_init', 'plugin_pll_register_string' );
/* //END//Polylang capability */