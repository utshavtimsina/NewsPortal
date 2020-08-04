<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Orchestrated_Corona_Virus_Banner {

	private static $_instance = null;
	public $settings = null;
	public $_version;
	public $_token;
	public $is_home = false;
	public $file;
	public $dir;
	public $assets_dir;
	public $assets_url;
	public $script_suffix;

	/**
	 * Load all of the dependencies
	 *
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'orchestrated_corona_virus_banner';
		$this->_text_domain = 'corona-virus-covid-19-banner';

		//* Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		//* Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		//* Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		$this->settings = new Orchestrated_Corona_Virus_Banner_Settings( $this );

		//* Handle Localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		add_action( 'wp_footer', array( $this, 'page_code' ) );

		add_action( 'wp_ajax_get_data_for_country', array( $this, 'get_data_for_country' ) );
		add_action( 'wp_ajax_nopriv_get_data_for_country', array( $this, 'get_data_for_country' ) );

		add_shortcode('covid-data', array( $this, 'display_shortcode' ) );
	}


	/**
	 * Display code on page for notice
	 * 
	 * @access  public
	 * @since   1.1.0
	 * @return void
	 */
	public function page_code() {
		$option_values = $this->settings->get_option_values();
		$enabled = $option_values[ 'enabled' ];
		$allow_close_expiry = $option_values[ 'allow_close_expiry' ];
		$display_page = $option_values[ 'display_page' ];
		$display_on_page = false;

		switch( $display_page ) {
			case "home":
				$display_on_page = is_front_page() ? true : false;
			break;
			default:
				$display_on_page = true;
			break;
		}
		$html = "";
		if ( $enabled && $display_on_page ) {
			$notice_html = $this->settings->get_notice_display();
			$html .= $notice_html;
		}
		$html .= <<<HTML
		<script>
			jQuery( function () { window.ocvb.init( $allow_close_expiry ); });
		</script>
HTML;
		echo $html;
	}

	/**
	 * Get Pages from WordPress.
	 *
	 * Loop through the Pages available on the website
	 * and return them.
	 * 
	 * @access  public
	 * @since   1.1.0
	 * @return void
	 */
	public function get_pages() {
		$pages = get_pages();
		$pages_select = [];
		foreach( $pages as $page ) {
			if( !empty( $page->post_title ) ) {
				$pages_select["$page->ID"] = $page->post_title;
			}
		}
		return $pages_select;
	}

	public function get_data_countries_select_options( $selected_value = 'world|World' ) {
		$labels = $this->settings->get_labels();
		$url = "https://api.covid19api.com/countries";
		
		$resp_json = file_get_contents( $url );
		$resp = json_decode( $resp_json, true );
		$return_html = "";

		if( ! $resp ) {
			$return_html .= '<option value="">Data service is unavailable</option>';
		} else {

			$not_active_selected = $selected_value == "" ?  "selected" : "";
			$world_selected = $selected_value == "world|World" ?  "selected" : "";
			$world_label = $labels[ 'world' ];

			$return_html .= '<option value="" ' . $not_active_selected . '>Not active</option>';
			$return_html .= '<option value="" disabled>––––––––––––––</option>';
			$return_html .= '<option value="world|World" ' . $world_selected . '>' . $world_label . '</option>';
			$return_html .= '<option value="" disabled>––––––––––––––</option>';
			
			if( $resp ) {

				usort( $resp, function( $a, $b ) { 
					return $a['Country'] > $b['Country'];
				} ); 

				foreach ( $resp as $country ) {
					$label = $country[ 'Country' ];
					$value = "{$country[ 'Slug' ]}|${label}";
					$selected = false;
					if ( $value == $selected_value ) {
						$selected = true;
					}
					if( $value != "" ) {
						$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $value ) . '">' . $label . '</option>';
					}
				}
			}
		}

		return $return_html;
	}

	public function get_data_for_country() {
		$country_slug = $_POST[ 'data_country_slug' ];
		$country_label = $_POST[ 'data_country_label' ];
		$labels = $this->settings->get_labels();
		$confirmed_cases = 0;
		$recovered_cases = 0;
		$deaths_cases = 0;
		$response = [ 'status' => 'OK', 'country_slug' => $country_slug, 'country_label' => $country_label, 'confirmed' => 0, 'recovered' => 0, 'deaths' => 0, 'last_updated' => $labels[ 'just now' ] ];
		$response_country = "";
		$error_with_api = false;
		$summary_url = "https://api.covid19api.com/summary";

		if ( $country_slug == "" ) {

			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country', null );
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_deaths', null );
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_recovered', null );
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_confirmed', null );

			$response[ 'confirmed' ] = "-";
			$response[ 'recovered' ] = "-";
			$response[ 'deaths' ] = "-";
			$response[ 'last_updated' ] = "–";

		} else {

			if( $country_slug == "world" ) {

				$resp_json = file_get_contents( $summary_url );
				$resp = json_decode( $resp_json, true );

				if( ! $resp ) $error_with_api = true;

				$confirmed_cases = $resp[ 'Global' ][ 'TotalConfirmed' ];
				$recovered_cases = $resp[ 'Global' ][ 'TotalRecovered' ];
				$deaths_cases = $resp[ 'Global' ][ 'TotalDeaths' ];
				$response_country = "world|World";

			} else {

				$resp_json = file_get_contents( $summary_url );
				$resp = json_decode( $resp_json, true );

				if( ! $resp ) $error_with_api = true;

				$countries = $resp[ 'Countries' ];
				$key = array_search( $country_slug, array_column( $countries, 'Slug' ) );
				$country = $countries[ $key ];
				$confirmed_cases = $country[ 'TotalConfirmed' ];
				$recovered_cases = $country[ 'TotalRecovered' ];
				$deaths_cases = $country[ 'TotalDeaths' ];
				$response_country = "$country_slug|$country_label";
			}
			
			if( ! $error_with_api ) {
				$response[ 'confirmed' ] = $confirmed_cases;
				$response[ 'recovered' ] = $recovered_cases;
				$response[ 'deaths' ] = $deaths_cases;
				$response[ 'last_updated' ] = $labels[ 'just_now' ];

				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country', "$response_country" );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_deaths', $deaths_cases );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_recovered', $recovered_cases );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_confirmed', $confirmed_cases );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', date( 'Y-m-d h:i:sa' ) );
			} else {
				$response[ 'status' ] = 'ERROR';
				$response[ 'last_updated' ] = $labels[ 'api_error' ];
			}
		}

		echo json_encode( $response );
		die();
	}

	public function update_data( $country_slug = "" ) {
		$summary_url = "https://api.covid19api.com/summary";

		if( $country_slug == "" ) return;

		if( $country_slug == "world" ) {

			$resp_json = file_get_contents( $summary_url );
			$resp = json_decode( $resp_json, true );
			$confirmed_cases = $resp[ 'Global' ][ 'TotalConfirmed' ];
			$recovered_cases = $resp[ 'Global' ][ 'TotalRecovered' ];
			$deaths_cases = $resp[ 'Global' ][ 'TotalDeaths' ];

		} else {

			$resp_json = file_get_contents( $summary_url );
			$resp = json_decode( $resp_json, true );
			$countries = $resp[ 'Countries' ];
			$key = array_search( $country_slug, array_column( $countries, 'Slug' ) );
			$country = $countries[ $key ];
			$confirmed_cases = $country[ 'TotalConfirmed' ];
			$recovered_cases = $country[ 'TotalRecovered' ];
			$deaths_cases = $country[ 'TotalDeaths' ];
		}
		
		update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_deaths', $deaths_cases );
		update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_recovered', $recovered_cases );
		update_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_confirmed', $confirmed_cases );
		update_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', date( 'Y-m-d h:i:sa' ) );
	}

	public function display_shortcode() {
		$option_values = $this->settings->get_option_values();
		$enabled = $option_values[ 'data_country' ] == "" ? false : true;
		if( $enabled )
			return $this->settings->get_shortcode_display();
		else
			return "";
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-frontend' );
		wp_register_style( $this->_token . '-font', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-font' );
	} 


	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-frontend' );
		wp_register_script( $this->_token . '-jscookie', esc_url( $this->assets_url ) . 'js/js.cookie.min.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-jscookie' );
	}


	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
		wp_register_style( $this->_token . '-font', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-font' );
	}


	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
		wp_register_script( $this->_token . '-foundation', esc_url( $this->assets_url ) . 'js/foundation' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-foundation' );
		wp_register_script( $this->_token . '-jscolor', esc_url( $this->assets_url ) . 'js/jscolor' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-jscolor' );
	}


	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation() {
		$this->is_home = is_front_page();
		load_plugin_textdomain( $this->_token, false, $this->dir . '/lang/' );
	}


	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
	    $locale = apply_filters( 'plugin_locale', get_locale(), $this->_text_domain );
	    load_textdomain( $this->_text_domain, $this->dir . '/lang/' . $this->_token . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $this->_text_domain, false, $this->dir . '/lang/' );
	}


	/**
	 * Main Orchestrated_Corona_Virus_Banner Instance
	 *
	 * Ensures only one instance of Orchestrated_Corona_Virus_Banner is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Orchestrated_Corona_Virus_Banner()
	 * @return Main Orchestrated_Corona_Virus_Banner instance
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		
		return self::$_instance;
	}


	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */

	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}


	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */

	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	}


	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */

	public function install () {
		$this->_log_version_number();
	}


	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */

	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	}
}