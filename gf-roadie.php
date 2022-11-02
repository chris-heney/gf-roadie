<?php
/*
Plugin Name: Gravity Forms Roadie
Plugin URI: https://expertoverflow.com
Description: A Gravity Forms addon for Roadie Delivery
Version: 1.0.1
Requires at least: 4.0
Requires PHP: 7.4
Author: Chris Heney
Author URI: https://chrisheney.com
Text Domain: gf_roadie

------------------------------------------------------------------------
Copyright 2009-2022 Expert Overflow, LLC
*/

define( 'GF_ROADIE_VERSION', '2.0' );

add_action( 'gform_loaded', array( 'GF_Roadie_Bootstrap', 'load' ), 5 );

class GF_Roadie_Bootstrap {

	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-roadie.php' );
		require_once( 'class-api-gf-roadie.php' );

		GFAddOn::register( 'GFRoadie' );
	}

}

function gf_roadie() {
	return GFRoadie::get_instance();
}
