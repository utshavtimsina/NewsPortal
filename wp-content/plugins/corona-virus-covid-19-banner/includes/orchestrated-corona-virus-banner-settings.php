<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Orchestrated_Corona_Virus_Banner_Settings {

	private static $_instance = null;
	public $parent = null;
	public $base = '';
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'orchestrated_corona_virus_banner_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
		$this->check_install();
		$this->run_jobs();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Corona Virus Banner', $this->parent->_text_domain ) , __( 'Corona Virus Banner', $this->parent->_text_domain ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {
	  	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery', $this->parent->_text_domain ), '1.0.0' );
	  	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}


	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', $this->parent->_text_domain ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	public function get_shortcode_display( $preview = false ) {
		$labels = $this->get_labels();
		$option_values = $this->get_option_values();

		$asset_url = $this->parent->assets_url;

		if( $option_values[ 'data_country' ] != "" ) {

			$data_country = explode( "|", $option_values[ 'data_country' ] );
			$data_confirmed_value = number_format( $option_values[ 'data_country_confirmed' ] );
			$data_recovered_value = number_format( $option_values[ 'data_country_recovered' ] );
			$data_deaths_value = number_format( $option_values[ 'data_country_deaths' ] );
			$data_last_run_date = $this->time_elapsed_string( ( new DateTime( $option_values[ 'last_job_run_date' ] ) )->format( 'Y-m-d h:i:sa' ) );

		} else {
			
			$data_country = ["", ""];
			$data_confirmed_value = "-";
			$data_recovered_value = "-";
			$data_deaths_value = "-";
			$data_last_run_date = "-";

		}

		return <<<HTML

			<style>
				#ocvb-shortcode-container {
					background: url( '${asset_url}/images/data-shortcode-big-box-background.jpg' ) no-repeat;
				}
			</style>
			<div id="ocvb-shortcode-container">
				<div class="grid-y align-middle ocvb-shortcode">
					<div class="cell small-2">
						<div class="ocvb-shortcode-headline">${labels['data_shortcode_title']}</div>
						<div class="ocvb-shortcode-country">{$data_country[ 1 ]}</div>
					</div>
					<div class="ocvb-shortcode-stats cell small-9">
						<div class="ocvb-shortcode-stats-confirmed grid-x grid-padding-y">
							<div class="ocvb-shortcode-stats-label cell small-7">${labels['data_confirmed']}</div>
							<div class="ocvb-shortcode-stats-value cell small-5">$data_confirmed_value</div>
						</div>
						<div class="ocvb-shortcode-stats-recovered grid-x grid-padding-y">
							<div class="ocvb-shortcode-stats-label cell small-7">${labels['data_recovered']}</div>
							<div class="ocvb-shortcode-stats-value cell small-5">$data_recovered_value</div>
						</div>
						<div class="ocvb-shortcode-stats-deaths grid-x grid-padding-y">
							<div class="ocvb-shortcode-stats-label cell small-7">${labels['data_deaths']}</div>
							<div class="ocvb-shortcode-stats-value cell small-5">$data_deaths_value</div>
						</div>
					</div>
					<div class="cell small-1 ocvb-shortcode-last-run-date">
						Last updated: <span class="data-label">$data_last_run_date</label>
					</div>
				</div>
			</div>
HTML;
	}

	/**
	 * Display notice
	 * @return string Returns HTML to display notice
	 */
	public function get_notice_display ( $preview = false ) {
		$option_values = $this->get_option_values();
		$banner_state_class = $option_values['enabled'] ? "ocvb-enabled" : "ocvb-disabled";
		$banner_display_type_class = "ocvb-display-type-banner";
		$link_state_class = "ocvb-disabled";
		$link_to_direct_to = "";
		$ready_state = $preview ? "ready-and-display" : "not-ready";

		switch( $option_values['display_type'] ) {
			case "banner":
				$banner_display_type_class = "ocvb-display-type-banner";
			break;
			case "overlay":
				$banner_display_type_class = "ocvb-display-type-overlay";
			break;
			case "leaderboard":
				$banner_display_type_class = "ocvb-display-type-leaderboard";
			break;
		}

		if ( ( $option_values['display_type'] == "leaderboard" || $option_values['display_type'] == "banner" ) && $option_values['allow_close'] == "true" ) {
			$close_button_state_class = "ocvb-enabled";
		} else if ( $option_values['display_type'] == "overlay" ) {
			$close_button_state_class = "ocvb-enabled";
		} else {
			$close_button_state_class = "ocvb-disabled";
		}

		if ( $option_values[ 'internal_link' ] == "ext" ) {
			//	Link to external URL
			if ( filter_var( $option_values[ 'external_link' ], FILTER_VALIDATE_URL ) === FALSE ) {
				//	Reset because URL is invalid: malformed
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', "none" );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link', "" );
				$link_state_class = "ocvb-disabled";
			} else {
				$link_to_direct_to = $option_values[ 'external_link' ];
				$link_state_class = "ocvb-enabled";
			}
		} else if ( $option_values[ 'internal_link' ] == "none" ) {
			//	Remove any page/link info
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', "none" );
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link', "" );
			$link_state_class = "ocvb-disabled";
		} else {
			//	Link to internal Page
			if ( 'publish' == get_post_status ( $option_values[ 'internal_link' ] ) ) {
				$page_url = get_page_link ( $option_values[ 'internal_link' ] );
				$link_to_direct_to = $page_url;
				$link_state_class = "ocvb-enabled";
			} else {
				//	Reset because Page is not found
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', "none" );
				update_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link', "" );
				$link_state_class = "ocvb-disabled";
			}
		}

		if ( $preview ) {
			$container_css = "";
			$container_css_mobile = "";
			$banner_display_type_class = "ocvb-display-type-banner";
			$close_button_state_class = "ocvb-enabled";
		}

		return <<<HTML
			<style>
				#ocvb-container #ocvb-body {
					color: ${option_values[ 'foreground_color' ]};
					background-color: ${option_values[ 'background_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
					${option_values[ 'container_css' ]}
				}
				@media screen and (max-width: 480px) {
					#ocvb-container #ocvb-body {
						${option_values[ 'container_css_mobile' ]}
					}
				}
				#ocvb-container #ocvb-body h1,
				#ocvb-container #ocvb-body h2,
				#ocvb-container #ocvb-body h3,
				#ocvb-container #ocvb-body h4,
				#ocvb-container #ocvb-body h5,
				#ocvb-container #ocvb-body h6 {
					color: ${option_values[ 'foreground_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
					margin-top: 4px;
					margin-bottom: 10px;
				}
				#ocvb-container #ocvb-body p {
					color: ${option_values[ 'foreground_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
				}
				#ocvb-container #ocvb-body a {
					color: ${option_values[ 'foreground_color' ]};
					text-align: ${option_values[ 'message_alignment' ]};
				}
				#ocvb-container[data-message-alignment="right"][data-allow-close="true"] #ocvb-container-notice-text {
					padding-right: 30px;
				}
				#ocvb-container[data-allow-close="true"] #ocvb-container-notice-text {
					padding-right: 20px;
				}
			</style>
			<script>
				if ( typeof Cookies != 'undefined' ) 
					if ( "${option_values['allow_close']}" == false && ( "${option_values['display_type']}" == "leaderboard" || "${option_values['display_type']}" == "banner" ) )
						Cookies.set( 'ocvb-keep-banner-closed', 'false' );
			</script>
			<div id="ocvb-container" class="${ready_state} ${banner_state_class} ${banner_display_type_class}" data-message-alignment="${option_values['message_alignment']}" data-display-type="${option_values['display_type']}" data-allow-close="${option_values['allow_close']}" data-title-header-size="${option_values['message_title_header_size']}">
				<div id="ocvb-body">
					<div id="ocvb-container-close-button" class="${close_button_state_class}"><a href="#">x</a></div>
					<div id="ocvb-container-notice-text">
						<${option_values[ 'message_title_header_size' ]}>${option_values[ 'message_title' ]}</${option_values[ 'message_title_header_size' ]}>
						<p>${option_values[ 'message_text' ]}</p>
						<div id="ocvb-container-notice-link" class="${link_state_class}">
							<a href="${link_to_direct_to}" target="${option_values[ 'link_target' ]}">${option_values[ 'link_text' ]}</a>
						</div>
					</div>
				</div>
			</div>
HTML;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {
		$option_values = $this->get_option_values();
		$pages = $this->parent->get_pages();

		$settings['settings'] = array(
			'title'						=> __( '', $this->parent->_text_domain ),
			'description'				=> __( '', $this->parent->_text_domain ),
			'fields'					=> array()
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}


	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_enabled', array( 'type' => 'boolean', 'default' => false ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_display_type', array( 'type' => 'string', 'default' => 'none' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_allow_close', array( 'type' => 'boolean', 'default' => false ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_allow_close_expiry', array( 'type' => 'integer', 'default' => 2 ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_display_page', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_title', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_title_header_size', array( 'type' => 'string', 'default' => 'h4' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_text', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_message_alignment', array( 'type' => 'string', 'default' => 'center' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_internal_link', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_external_link', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_link_text', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_link_target', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_foreground_color', array( 'type' => 'string', 'default' => '#ffffff' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_background_color', array( 'type' => 'string', 'default' => '#cc0000' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_container_css', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_container_css_mobile', array( 'type' => 'string', 'default' => '' ) );

		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', array( 'type' => 'string', 'default' => date( 'Y-m-d h:i:sa' ) ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_data_country', array( 'type' => 'string', 'default' => '' ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_data_country_confirmed', array( 'type' => 'integer', 'default' => 0 ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_data_country_recovered', array( 'type' => 'integer', 'default' => 0 ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_data_country_deaths', array( 'type' => 'integer', 'default' => 0 ) );
		register_setting( $this->parent->_token . '_settings', Orchestrated_Corona_Virus_Banner()->_token . '_data_frequency', array( 'type' => 'integer', 'default' => 0 ) );
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {
		ob_start();

		$admin_url = admin_url('admin-ajax.php');

		$notice_display = $this->get_notice_display( true );
		$shortcode_display = $this->get_shortcode_display( true );

		$option_values = $this->get_option_values();

		$field_enabled_value = $option_values['enabled'] == true ? "checked" : "";
		$field_display_type_value = $option_values['display_type'];
		$field_allow_close_value = $option_values['allow_close'] == "true" ? "true" : "false";
		$field_allow_close_expiry_value = $option_values['allow_close_expiry'];
		$field_display_page_value = strip_tags( $option_values['display_page'] );
		$field_message_title_value = strip_tags( $option_values['message_title'] );
		$field_message_title_header_size = strip_tags( $option_values['message_title_header_size'] );
		$field_message_text_value = strip_tags( $option_values['message_text'] );
		$field_message_alignment_value = strip_tags( $option_values[ 'message_alignment']  );
		$field_external_link_value = strip_tags( $option_values['external_link'] );
		$field_internal_link_value = strip_tags( $option_values['internal_link'] );
		$field_link_text_value = strip_tags( $option_values['link_text'] );
		$field_link_target_value = strip_tags( $option_values['link_target'] );
		$field_foreground_color_value = strip_tags( $option_values['foreground_color'] );
		$field_background_color_value = strip_tags( $option_values['background_color'] );
		$field_container_css_value = strip_tags( $option_values['container_css'] );
		$field_container_css_mobile_value = strip_tags( $option_values['container_css_mobile'] );
		$field_data_country_value = strip_tags( $option_values['data_country'] );
		$field_data_country_confirmed_value = strip_tags( $option_values['data_country_confirmed'] );
		$field_data_country_recovered_value = strip_tags( $option_values['data_country_recovered'] );
		$field_data_country_deaths_value = strip_tags( $option_values['data_country_deaths'] );
		$field_data_frequency_value = strip_tags( $option_values['data_frequency'] );

		$pages_options_html = $this->get_select_options( 'pages', $field_internal_link_value );
		$alignment_options_html = $this->get_select_options( 'alignment', $field_message_alignment_value );
		$display_type_options_html = $this->get_select_options( 'display_type', $field_display_type_value );
		$link_target_options_html = $this->get_select_options( 'targets', $field_link_target_value );
		$header_size_options_html = $this->get_select_options( 'header_sizes', $field_message_title_header_size );
		$data_countries_html = $this->parent->get_data_countries_select_options( $field_data_country_value );
		$data_frequency_times_html = $this->get_select_options( 'frequency_times', $field_data_frequency_value );
		$display_page_options_html = $this->get_select_options( 'display_pages', $field_display_page_value );

		$field_allow_close_checked_true = ( $field_allow_close_value == "true" || $field_allow_close_value == "checked" ) ? "checked" : "";
		$field_allow_close_checked_false = $field_allow_close_value == "false" ? "checked" : "";

		$data_last_run_date = $this->time_elapsed_string( ( new DateTime( $option_values[ 'last_job_run_date' ] ) )->format( 'Y-m-d h:i:sa' ) );

		$asset_url = $this->parent->assets_url;

		$display_states = [
			'none' => __( 'Notice is currently set to not display to users. Choose "Banner" or "Overlay" to display the notice.', $this->parent->_text_domain ),
			'banner' => __( 'Notice is displaying on the website as a Banner. If you are experiencing display issues, try "Overlay."', $this->parent->_text_domain ),
			'overlay' => __( 'Notice is displaying on the website as an Overlay.', $this->parent->_text_domain ),
			'leaderboard' => __( 'Notice is displaying on the website as a Leaderboard.', $this->parent->_text_domain ),
		];

		$labels = $this->get_labels();

		$html = <<<HTML
		<div id="ocvb-admin-container" data-admin-url="$admin_url">
			<input id="enabled" type="hidden" name="orchestrated_corona_virus_banner_enabled" value="$field_enabled_value">
			<input id="display_type" type="hidden" name="orchestrated_corona_virus_banner_display_type" value="$field_display_type_value">
			<input id="data_country_confirmed" type="hidden" name="orchestrated_corona_virus_banner_data_country_confirmed" value="$field_data_country_confirmed_value">
			<input id="data_country_recovered" type="hidden" name="orchestrated_corona_virus_banner_data_country_recovered" value="$field_data_country_recovered_value">
			<input id="data_country_deaths" type="hidden" name="orchestrated_corona_virus_banner_data_country_deaths" value="$field_data_country_deaths_value">
			<div class="grid-x grid-container grid-padding-y admin-settings">
				<div class="cell small-12">
					<div class="callout">
						<h2>${labels['ocvb_title']}</h2>
						<p>${labels['ocvb_description']}</p>
					</div>
					
					<div data-abide-error class="alert callout" style="display: none;">
						<p>${labels['please_complete_notice']}</p>
					</div>

					<ul class="tabs" data-tabs id="setting-tabs" data-deep-link="true">
						<li class="tabs-title is-active"><a href="#display" aria-selected="true">${labels['display']}</a></li>
						<li class="tabs-title"><a href="#options">${labels['options']}</a></li>
						<li class="tabs-title"><a href="#preview">${labels['preview']}</a></li>
						<li class="tabs-title"><a href="#data">${labels['data']} <small>New!</small></a></li>
						<li class="tabs-title"><a href="#about">${labels['about']}</a></li>
					</ul>

					<div class="tabs-content grid-x" data-tabs-content="setting-tabs">
      					<div class="tabs-panel is-active cell small-12" id="display">
							<label>${labels['how_display_notice']}</label>
							<p></p>
							<div class="grid-x grid-padding-y align-middle">
								<div class="small-6 cell">
									<div class="grid-x">
										<div class="settings-image-option settings-image-option-none cell small-3" data-selection="none">
											<div class="container">
												<img src="$asset_url/images/display-type-none.png" alt="${labels['display']}: ${labels['none']}" />
												<label>${labels['none']}</label>
											</div>
										</div>
										<div class="settings-image-option settings-image-option-banner cell small-3" data-selection="banner">
											<div class="container">
												<img src="$asset_url/images/display-type-banner.png" alt="${labels['display']}: ${labels['banner']}" />
												<label>${labels['banner']}</label>
											</div>
										</div>
										<div class="settings-image-option settings-image-option-leaderboard cell small-3" data-selection="leaderboard">
											<div class="container">
												<img src="$asset_url/images/display-type-leaderboard.png" alt="${labels['display']}: ${labels['leaderboard']}" />
												<label>${labels['leaderboard']}</label>
											</div>
										</div>
										<div class="settings-image-option settings-image-option-overlay cell small-3" data-selection="overlay">
											<div class="container">
												<img src="$asset_url/images/display-type-overlay.png" alt="${labels['display']}: ${labels['overlay']}" />
												<label>${labels['overlay']}</label>
											</div>
										</div>
									</div>
								</div>
								<div class="small-4 small-offset-1 end cell">
									<div class="grid-x align-middle callout text-center">
										<div class="small-12 cell display-type-status">
											$display_states[$field_display_type_value]
										</div>
									</div>
								</div>
							</div>
							<div class="grid-x grid-padding-y display-required">
								<div class="small-12 cell">
									<label>
										<div class="form-label">${labels['title']}</div>
										<input id="message_title" type="text" name="orchestrated_corona_virus_banner_message_title" placeholder="${labels['enter_notice_title']}" value="$field_message_title_value">
										<span class="form-error" data-form-error-for="message_title">${labels['required']}</span>
									</label>
								</div>
								<div class="small-12 cell">
									<label>
										<div class="form-label">${labels['message']}</div>
										<textarea id="message_text" rows="5" cols="50" name="orchestrated_corona_virus_banner_message_text" placeholder="${labels['enter_notice_text']}">$field_message_text_value</textarea>
										<span class="form-error" data-form-error-for="message_text">${labels['required']}</span>
									</label>
								</div>
							</div>
							<div class="grid-x grid-padding-y display-required">
								<div class="small-4 cell">
									<label>
										<div class="form-label">${labels['where_to_direct_users']}</div>
										<select name="orchestrated_corona_virus_banner_internal_link" id="internal_link">
											$pages_options_html
										</select>
									</label>
								</div>
								<div class="small-4 cell">
									<label>
										<div class="form-label">${labels['link_target']}</div>
										<select name="orchestrated_corona_virus_banner_link_target" id="link_target">
											$link_target_options_html
										</select>
									</label>
								</div>
								<div class="small-3 cell">
									<label>
										<div class="form-label">${labels['display_page']}</div>
										<select name="orchestrated_corona_virus_banner_display_page" id="display_page">
											$display_page_options_html
										</select>
									</label>
								</div>
							</div>
							<div class="grid-x grid-padding-y display-required option-link-url">
								<div class="small-6 cell">
									<label>
										<div class="form-label">${labels['link_url']}</div>
										<input id="external_link" type="url" name="orchestrated_corona_virus_banner_external_link" placeholder="http://www.host.com" value="$field_external_link_value" pattern="url">
									</label>
								</div>
							</div>
							<div class="grid-x grid-padding-y display-required option-link-text">
								<div class="small-6 cell">
									<label>
										<div class="form-label">${labels['link_label']}</div>
										<input id="link_text" type="text" name="orchestrated_corona_virus_banner_link_text" placeholder="${labels['more_information']}" value="$field_link_text_value">
									</label>
								</div>
							</div>
						</div>

						<div class="tabs-panel" id="options">
							<div class="grid-x display-required callout">
								<div class="small-12 cell">
									<h3>${labels['design']}</h3>
								</div>
								<div class="small-3 cell">
									<label>
										<div class="form-label">${labels['header_size']}</div>
										<select name="orchestrated_corona_virus_banner_message_title_header_size" id="message_title_header_size">
											$header_size_options_html
										</select>
									</label>
								</div>
								<div class="small-3 cell">
									<label>
										<div class="form-label">${labels['text_alignment']}</div>
										<select name="orchestrated_corona_virus_banner_message_alignment" id="message_alignment">
											$alignment_options_html
										</select>
									</label>
								</div>
								<div class="small-3 cell">
									<label>
										<div class="form-label">${labels['foreground_color']}</div>
										<input type="text" id="foreground_color" name="orchestrated_corona_virus_banner_foreground_color" class="color jscolor {hash:true}" value="$field_foreground_color_value" autocomplete="off" pattern="color">
										<span class="form-error" data-form-error-for="foreground_color">${labels['required']}</span>
									</label>
								</div>
								<div class="small-3 cell">
									<label>
										<div class="form-label">${labels['background_color']}</div>
										<input type="text" id="background_color" name="orchestrated_corona_virus_banner_background_color" class="color jscolor {hash:true}" value="$field_background_color_value" autocomplete="off" pattern="color">
										<span class="form-error" data-form-error-for="background_color">${labels['required']}</span>
									</label>
								</div>
							</div>
							<div class="grid-x display-required callout display-type-banner-required">
								<div class="small-12 cell">
									<h3>${labels['user_preferences']}</h3>
								</div>
								<div class="small-6 cell">
									<label>${labels['should_allowed_close_notice']}</label>
									<p></p>
									<div>
										<span class="radio-item"><input type="radio" name="orchestrated_corona_virus_banner_allow_close" value="true" id="allow_close_true" $field_allow_close_checked_true> ${labels['yes']}</span>
    									<span class="radio-item"><input type="radio" name="orchestrated_corona_virus_banner_allow_close" value="false" id="allow_close_false" $field_allow_close_checked_false> ${labels['no']}</span>
									</div>
								</div>
								<div class="small-6 cell">
									<label>
										<div class="form-label">${labels['how_many_days_notice']}</div>
										<input id="allow_close_expiry" type="number" name="orchestrated_corona_virus_banner_allow_close_expiry" placeholder="2" value="$field_allow_close_expiry_value" pattern="number">
										<small>${labels['use_0_to_never_reappear']}</small>
									</label>
								</div>
							</div>
							<div class="grid-x display-required callout">
								<div class="small-12 cell">
									<h3>${labels['styling']}</h3>
								</div>
								<div class="small-6 cell">
									<label>
										<div class="form-label">${labels['custom_css']}</div>
										<textarea id="container_css" rows="5" cols="50" name="orchestrated_corona_virus_banner_container_css" placeholder="e.g. margin-top: 20px;">$field_container_css_value</textarea>
									</label>
								</div>
								<div class="small-6 cell">
									<label>
										<div class="form-label">${labels['custom_css_mobile']}</div>
										<textarea id="container_css_mobile" rows="5" cols="50" name="orchestrated_corona_virus_banner_container_css_mobile" placeholder="e.g. margin-top: 20px;">$field_container_css_mobile_value</textarea>
									</label>
								</div>
								<div class="small-12 cell"><small>${labels['styles_not_applied_to_preview']}</small></div>
							</div>
						</div>

						<div class="tabs-panel" id="data">
							<div class="grid-x">
								<div class="small-7 cell">
									${labels['data_introduction']}
									<p>&nbsp;</p>
									<div class="grid-x align-middle callout grid-margin-y">
										<div class="small-6 cell">
											<label>
												<div class="form-label">${labels['data_country']}</div>
												<select name="orchestrated_corona_virus_banner_data_country" id="data_country">
													$data_countries_html
												</select>
											</label>
										</div>
										<div class="small-6 cell last-run-date">
											<label>
												<div class="form-label">${labels['data_last_updated']}</div>
												<code class="data-label">$data_last_run_date</code>
											</label>
										</div>
										<div class="small-12 cell">
											<label>
												<div class="form-label">${labels['data_frequency']}</div>
												<select id="data_frequency" name="orchestrated_corona_virus_banner_data_frequency">
													$data_frequency_times_html
												</select>
											</label>
										</div>
									</div>
								</div>
								<div class="small-5 cell">
									<div class="ocvb-shortcode-preview">
										$shortcode_display
										<div class="how-to-use">Use: <code>[covid-data]</code></div>
									</div>
								</div>
							</div>
						</div>

						<div class="tabs-panel" id="preview">
							<div class="grid-x">
								<div class="ocvb-preview-container small-12 cell" data-preview-enabled="true">
									$notice_display
								</div>

								<div class="small-6 small-offset-3 cell" data-preview-enabled="false">
									<div class="callout text-center">
										${labels['preview_not_available']}
									</div>
								</div>
							</div>
						</div>

						<div class="tabs-panel" id="about">
							<div class="grid-x">
								<div class="cell small-11 card about-card">
									<img src="$asset_url/images/orchestrated-logo.png" alt="Orchestrated" width="176" height="50" />
									<p>${labels['about_line_1']}</p>
									<p>${labels['about_line_2']}</p>
									<p>${labels['about_line_3']}</p>
									<p>${labels['about_line_4']}</p>
								</div>
							</div>
							<div class="grid-x">
								<div class="cell small-11 card about-card">
									<h3>${labels['support_title']}</h3>
									<p>${labels['support_line_1']}</p>
								</div>
							</div>
						</div>

					</div>
				</div>

				<div class="cell small-12">
					<div class="submit">
						<input name="Submit" type="submit" class="button-primary" value="${labels['save_settings']}" />
					</div>
				</div>

				<div class="display-type-caption" data-display-type-caption="none">${display_states['none']}</div>
				<div class="display-type-caption" data-display-type-caption="banner">${display_states['banner']}</div>
				<div class="display-type-caption" data-display-type-caption="overlay">${display_states['overlay']}</div>
				<div class="display-type-caption" data-display-type-caption="leaderboard">${display_states['leaderboard']}</div>
			</div>
		</div>
HTML;
		echo '<form method="post" action="options.php" enctype="multipart/form-data" data-abide novalidate>';
		settings_fields( $this->parent->_token . '_settings' );
		echo $html;
		echo '</form>';
	}

	public function get_labels() {
		return [
			'ocvb_title' => __( 'Corona Virus Banner', $this->parent->_text_domain ),
			'ocvb_description' => __( 'This is a very simple plugin with a sole purpose of allowing website owners a quick way to add a COVID-19 notice to their website. See how <a href="https://google.org/crisisresponse/covid19-map" target="_blank">COVID-19 is impacting the world</a>.', $this->parent->_text_domain ),
			'please_complete_notice' => __( 'Please complete all required fields.', $this->parent->_text_domain ),
			'title' => __( 'Title', $this->parent->_text_domain ),
			'message' => __( 'Message', $this->parent->_text_domain ),
			'display' => __( 'Display', $this->parent->_text_domain ),
			'options' => __( 'Options', $this->parent->_text_domain ),
			'about' => __( 'About', $this->parent->_text_domain ),
			'preview' => __( 'Preview', $this->parent->_text_domain ),
			'none' => __( 'None', $this->parent->_text_domain ),
			'banner' => __( 'Banner', $this->parent->_text_domain ),
			'overlay' => __( 'Overlay', $this->parent->_text_domain ),
			'leaderboard' => __( 'Leaderboard', $this->parent->_text_domain ),
			'design' => __( 'Design', $this->parent->_text_domain ),
			'styling' => __( 'Styling', $this->parent->_text_domain ),
			'required' => __( 'Required', $this->parent->_text_domain ),
			'yes' => __( 'Yes', $this->parent->_text_domain ),
			'no' => __( 'No', $this->parent->_text_domain ),
			'enter_notice_title' => __( 'Enter your notice title', $this->parent->_text_domain ),
			'enter_notice_text' => __( 'Enter your notice text', $this->parent->_text_domain ),
			'how_display_notice' => __( 'How would you like to display the notice?', $this->parent->_text_domain ),
			'header_size' => __( 'Message title size', $this->parent->_text_domain ),
			'link_url' => __( 'Link URL', $this->parent->_text_domain ),
			'link_label' => __( 'Link label', $this->parent->_text_domain ),
			'more_information' => __( 'More information', $this->parent->_text_domain ),
			'link_target' => __( 'Should the link open in a new tab?', $this->parent->_text_domain ),
			'where_to_direct_users' => __( 'Where do you want to direct users for more information?', $this->parent->_text_domain ),
			'user_preferences' => __( 'User preferences', $this->parent->_text_domain ),
			'save_settings' => __( 'Save Settings', $this->parent->_text_domain ),
			'should_allowed_close_notice' => __( 'Should users be allowed to close the notice?', $this->parent->_text_domain ),
			'how_many_days_notice' => __( 'How many days before the notice re-appears?', $this->parent->_text_domain ),
			'use_0_to_never_reappear' => __( 'Use "0" to never re-appear', $this->parent->_text_domain ),
			'text_alignment' => __( 'Text alignment', $this->parent->_text_domain ),
			'foreground_color' => __( 'Foreground color', $this->parent->_text_domain ),
			'background_color' => __( 'Background color', $this->parent->_text_domain ),
			'custom_css' => __( 'Custom CSS', $this->parent->_text_domain ),
			'custom_css_mobile' => __( 'Custom CSS (mobile)', $this->parent->_text_domain ),
			'styles_not_applied_to_preview' => __( 'Note: Custom CSS is not applied to Preview.', $this->parent->_text_domain ),
			'preview_not_available' => __( 'Once you have enabled "Banner" or "Overlay", a preview will appear here. See the "Display" tab to continue.', $this->parent->_text_domain ),
			'data' => __( 'Data', $this->parent->_text_domain ),
			'data_country' => __( 'Country to display', $this->parent->_text_domain ),
			'data_last_updated' => __( 'Last updated', $this->parent->_text_domain ),
			'data_confirmed' => __( 'Confirmed', $this->parent->_text_domain ),
			'data_recovered' => __( 'Recovered', $this->parent->_text_domain ),
			'data_deaths' => __( 'Deaths', $this->parent->_text_domain ),
			'data_frequency' => __( 'How often should the data be updated?', $this->parent->_text_domain ),
			'data_shortcode_title' => __( 'Live COVID-19 statistics for', $this->parent->_text_domain ),
			'data_introduction' => __( 'In addition to displaying a Banner or Overlay, this plugin also allows you to display COVID-19 statistics for your country. Use the shortcode <code>[covid-data]</code> to display this data in any Page or Post.', $this->parent->_text_domain ),
			'about_line_1' => __( 'In March, after COVID-19 had begun to overwhelm small and large companies alike, several of our clients were asking for a way to quickly announce their closures and updates to their clients and customers. We’re a small digital agency based in Hamilton, Ontario that regularly creates WordPress websites for our clients – so we quickly wrote a plugin that served the need, which has now become the plugin you see before you.', $this->parent->_text_domain ),
			'about_line_2' => __( 'It’s deliberately simple, and meant to be a plugin that you will remove in the future – without changing your theme, stylesheets or overall configuration of your WordPress website.', $this->parent->_text_domain ),
			'about_line_3' => __( 'This plugin is free and will remain free forever, but if you have a moment, donate to causes that are <a href="https://www.canadahelps.org/en/donate-to-coronavirus-outbreak-response/" target="_blank">helping victims and families of victims</a>. Everything helps.', $this->parent->_text_domain ),
			'about_line_4' => __( '– Team <a href="http://orchestrated.ca" target="_blank">Orchestrated</a>', $this->parent->_text_domain ),
			'world' => __( 'World', $this->parent->_text_domain ),
			'just_now' => __( 'just now', $this->parent->_text_domain ),
			'api_error' => __( 'There is an issue with data source at the moment.', $this->parent->_text_domain ),
			'support_title' => __( 'Support', $this->parent->_text_domain ),
			'support_line_1' => __( 'Need a hand with the plugin? Send us <a href="mailto:support@orchestrated.ca?subject=SUPPORT with Corona Virus Plugin" target="_blank">an email</a>. We\'ll get back to you within 3-4 business days.', $this->parent->_text_domain ),
			'display_page' => __( 'Where to display notice?', $this->parent->_text_domain ),
		];
	}

	public function get_option_values () {
		return [
			'enabled' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_enabled' ) ?: false ),
			'display_type' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_display_type' ), FILTER_SANITIZE_STRING ) ?: 'none' ),
			'allow_close' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_allow_close' ) ?: 'false' ),
			'allow_close_expiry' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_allow_close_expiry' ) ?: 2 ),
			'display_page' => ( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_display_page' ) ?: '' ),
			'message_title' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_title' ), FILTER_SANITIZE_STRING ) ?: '' ),
			'message_title_header_size' => ( strip_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_title_header_size' ) ) ?: 'h4' ),
			'message_text' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_text' ), FILTER_SANITIZE_STRING ) ?: '' ),
			'message_alignment' => ( strip_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_message_alignment' ) ) ?: 'center'  ),
			'internal_link' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_internal_link' ), FILTER_SANITIZE_STRING ) ?: 'none' ),
			'external_link' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_external_link' ), FILTER_SANITIZE_STRING ) ?: '' ),
			'link_text' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_link_text' ), FILTER_SANITIZE_STRING ) ?: __( 'More Information', $this->parent->_text_domain ) ),
			'link_target' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_link_target' ), FILTER_SANITIZE_STRING ) ?: __( '', $this->parent->_text_domain ) ),
			'foreground_color' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_foreground_color' ), FILTER_SANITIZE_STRING ) ?: '#ffffff' ),
			'background_color' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_background_color' ), FILTER_SANITIZE_STRING ) ?: '#cc0000' ),
			'container_css' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_container_css' ), FILTER_SANITIZE_STRING ) ?: '' ),
			'container_css_mobile' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_container_css_mobile' ), FILTER_SANITIZE_STRING ) ?: '' ),
			'last_job_run_date' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date' ), FILTER_SANITIZE_STRING ) ?: date( 'Y-m-d h:i:sa' ) ),
			'data_country' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country' ), FILTER_SANITIZE_STRING ) ?: '' ),
			'data_country_confirmed' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_confirmed' ), FILTER_SANITIZE_NUMBER_INT ) ?: 0 ),
			'data_country_recovered' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_recovered' ), FILTER_SANITIZE_NUMBER_INT ) ?: 0 ),
			'data_country_deaths' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_country_deaths' ), FILTER_SANITIZE_NUMBER_INT ) ?: 0 ),
			'data_frequency' => ( filter_var( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_data_frequency' ), FILTER_SANITIZE_NUMBER_INT ) ?: 10 ),
		];
	}

	public function get_select_options ( $type = 'pages', $selected_value ) {
		$return_html = "";

		switch( $type ) {
			case 'pages':
				$pages = [
					"none" => __( "No link", $this->parent->_token ),
					"–1" => "––––––––––––––",
					"ext" => __( "Link to another website", $this->parent->_token ),
					"–2" => "––––––––––––––",
				];
				$pages = $pages + $this->parent->get_pages();
				foreach ( $pages as $k => $v ) {
					$selected = false;
					$disabled = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					if ( $v == "––––––––––––––" ) {
						$disabled = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' ' . ( $disabled ? "disabled" : "" ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'display_pages':
				$pages = [
					"" => __( "Display on all Pages", $this->parent->_token ),
					"home" => __( "Only on Homepage", $this->parent->_token ),
				];
				foreach ( $pages as $k => $v ) {
					$selected = false;
					$disabled = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					if ( $v == "––––––––––––––" ) {
						$disabled = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' ' . ( $disabled ? "disabled" : "" ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'alignment':
				$alignments = array(
					'center' => __( 'Center', $this->parent->_text_domain ),
					'left' => __( 'Left', $this->parent->_text_domain ),
					'right' => __( 'Right', $this->parent->_text_domain ),
					'justify' => __( 'Justified', $this->parent->_text_domain ),
					'inherit' => __( 'Default', $this->parent->_text_domain ),
				);
				foreach ( $alignments as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'targets':
				$targets = array(
					'' => __( 'Default', $this->parent->_text_domain ),
					'_blank' => __( 'New tab', $this->parent->_text_domain ),
				);
				foreach ( $targets as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'header_sizes':
				$header_sizes = array(
					'h2' => __( 'Biggest (H2)', $this->parent->_text_domain ),
					'h3' => __( 'Bigger (H3)', $this->parent->_text_domain ),
					'h4' => __( 'Big (H4)', $this->parent->_text_domain ),
					'h5' => __( 'Normal (H5)', $this->parent->_text_domain ),
					'h6' => __( 'Small (H6)', $this->parent->_text_domain ),
				);
				foreach ( $header_sizes as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'display_type':
				$display_types = array(
					'none' => __( 'None', $this->parent->_text_domain ),
					'banner' => __( 'Banner', $this->parent->_text_domain ),
					'overlay' => __( 'Overlay', $this->parent->_text_domain ),
					'leaderboard' => __( 'Leaderboard', $this->parent->_text_domain ),
				);
				foreach ( $display_types as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;

			case 'frequency_times':
				$frequency_times = array(
					'10' => __( 'Every 10 minutes', $this->parent->_text_domain ),
					'30' => __( 'Every 30 minutes', $this->parent->_text_domain ),
					'60' => __( 'Every hour', $this->parent->_text_domain ),
					'120' => __( 'Every 2 hours', $this->parent->_text_domain ),
					'360' => __( 'Every 6 hours', $this->parent->_text_domain ),
					'1440' => __( 'Every 24 hours', $this->parent->_text_domain ),
				);
				foreach ( $frequency_times as $k => $v ) {
					$selected = false;
					if ( $k == $selected_value ) {
						$selected = true;
					}
					$return_html .= '<option ' . selected( $selected, true, false ) . ' value="' . sanitize_text_field( $k ) . '">' . $v . '</option>';
				}
			break;
		}

		return $return_html;
	}

	public function run_jobs() {
		$option_values = $this->get_option_values();
		$minutes_before_next_run = $option_values[ 'data_frequency' ];
		$country = explode( "|", $option_values[ 'data_country' ] );
		$last_job_run_date = new DateTime( $option_values[ 'last_job_run_date' ] );
		$today_date = new DateTime();
		$diff = $today_date->diff( $last_job_run_date );
		$minutes = $diff->i;
		if( $minutes >= $minutes_before_next_run ) {
			$this->parent->update_data( $country[ 0 ] );
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', $today_date->format( 'Y-m-d h:i:sa' ) );
		}
	}

	public function check_install() {
		$today_date = new DateTime();

		if( strip_tags( get_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date' ) ) == "" ) {
			update_option( Orchestrated_Corona_Virus_Banner()->_token . '_last_job_run_date', $today_date->format( 'Y-m-d h:i:sa' ) );
		}
	}

	public function time_elapsed_string($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);
	
		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;
	
		$string = array(
			'y' => __( 'year', $this->parent->_text_domain ),
			'm' => __( 'month', $this->parent->_text_domain ),
			'w' => __( 'week', $this->parent->_text_domain ),
			'd' => __( 'day', $this->parent->_text_domain ),
			'h' => __( 'hour', $this->parent->_text_domain ),
			'i' => __( 'minute', $this->parent->_text_domain ),
			's' => __( 'second', $this->parent->_text_domain ),
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}
	
		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . __( ' ago', $this->parent->_text_domain ) : __( 'just now', $this->parent->_text_domain );
	}

	/**
	 * Main Orchestrated_Corona_Virus_Banner Instance
	 *
	 * Ensures only one instance of Orchestrated_Corona_Virus_Banner is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Orchestrated_Corona_Virus_Banner
	 * @return Main Orchestrated_Corona_Virus_Banner instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} 


	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', $this->parent->_text_domain ), $this->parent->_version );
	}


	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', $this->parent->_text_domain ), $this->parent->_version );
	}
}
