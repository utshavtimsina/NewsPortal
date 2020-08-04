<?php
/*
 * Plugin Name: Corona Virus (COVID-19) Banner & Live Data
 * Version: 1.7.0.4
 * Description: Display a notice to visitors about how your business/organization will respond to COVID-19.	Now with live data.
 * Author: Orchestrated
 * Author URI: http://www.orchestrated.ca
 * Requires at least: 5.1
 * Tested up to: 5.4
 *
 * Text Domain: corona-virus-covid-19-banner
 * Domain Path: /lang
 *
 * @package WordPress
 * @author Orchestrated
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/orchestrated-corona-virus-banner.php' );
require_once( 'includes/orchestrated-corona-virus-banner-settings.php' );

/**
 * Returns the main instance of Orchestrated_Corona_Virus_Banner to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Orchestrated_Corona_Virus_Banner
 */
function Orchestrated_Corona_Virus_Banner () {
	$instance = Orchestrated_Corona_Virus_Banner::instance( __FILE__, '1.7.0.4' );
	if ( is_null( $instance->settings ) ) {
		$instance->settings = Orchestrated_Corona_Virus_Banner_Settings::instance( $instance );
	}

	return $instance;
}

Orchestrated_Corona_Virus_Banner();
