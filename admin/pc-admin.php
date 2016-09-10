<?php

class PC_Admin
{
	public function __construct() {
		add_action('admin_init', array($this, 'pc_admin_init'));
		add_action('admin_menu', array($this, 'setup_menus'));
	}

	public function setup_menus() {
		 // Add our top level menu page.
        add_menu_page('Property Compass Settings', 'Property Compass', 'manage_options', 'property-compass-settings',
	        function() {
	            if (!current_user_can('manage_options')) {
	                wp_die(__('You are not allowed to access this page'));
	            }
	            require_once dirname(__FILE__) . "/pc-admin-main.tpl.php";
	        });

        // Add our sub menu pages.
        add_submenu_page(
            $parent_slug = 'property-compass-settings',
            $page_title = 'Configuration',
            $menu_title = 'Configuration',
            $capability = 'manage_options',
            $menu_slug ='pc-api-configuration',
            function() {

                if (!current_user_can('manage_options')) {
                    wp_die(__('You are not allowed to access this page'));
                }

                echo "<div class='wrap'>";
                echo "<div id='icon-plugins' class='icon32'></div>";
                echo "<h2>Configuration</h2>";
                settings_errors();

                echo '<form method="post" action="options.php">';
                settings_fields('property-compass-settings');
                do_settings_sections('pc-api-configuration');
                submit_button();

                echo '</form></div>';
            }
        );
	}

	 /**
     * Callback for admin_init hook.
     */
    public function pc_admin_init() {
        $this->add_api_settings();
    }


    /**
     * API configuration
     */
    public function add_api_settings() {
        add_settings_section(
            $id = 'pc-api-security-section',
            $title = 'Security',
            function() {},
            $page = 'pc-api-configuration'
        );

        add_settings_field(
            $id = 'property_compass_api_key',
            $title = 'API Key',
            function() {
                echo "<input required class='medium-text' type='text' name='property_compass_api_key' value='" . get_option('property_compass_api_key') . "'/>"
                ."<p class='description' id='property_compass_api_description'>This is a shared key with Property Compass to secure access to the plugin API.<br /> It's used as the signing key to create a HMAC keyed-hash which is then sent in a HTTP header with each API call.";
            },
            $page = 'pc-api-configuration',
            $section = 'pc-api-security-section'
        );


        // Register the settings with WP.
        register_setting('property-compass-settings', 'property_compass_api_key');
    }
}