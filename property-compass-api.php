<?php

/*
 * Plugin Name: Property Compass : API
 * Plugin URI: http://www.propertycompass.com.au/integrations/wordpress
 * Description: This plugin provides a HTTP API for Property Compass to communicate with.
 * Version: 0.1
 * Author: Property Compass
 * Author URI: propertycompass.com.au
 */
require_once dirname(__FILE__) . '/importers/listing-importer.php';
require_once dirname(__FILE__) . '/importers/property-list-importer.php';
require_once dirname(__FILE__) . '/admin/pc-admin.php';
require_once dirname(__FILE__) . '/config/pc-post-types.php';
defined( 'ABSPATH' ) or die( 'Access denied!' );

class PC_API {
	public function __construct() {
		$this->setup();
	}

	/**
	 * This stuff happens on every page load.
	 */
	public function setup() {
		add_action('init', function() {
			//specify the endpoint details
			
			$post_types = new PC_PostTypes();
			$post_types->create_listing_post_type();

			$regex = 'propertycompass/v1/([^/]+)/([^/]+)/?';
			$location = 'index.php?_api_controller=$matches[1]&_api_action=$matches[2]';
			$priority = 'top';

			//add the rule
			add_rewrite_rule( $regex, $location, $priority );

			add_filter( 'query_vars', function($vars) {
				array_push($vars, '_api_controller');
				array_push($vars, '_api_action');
				return $vars;
			});
			
			add_filter( 'template_include', function($template) {

				$controller = get_query_var('_api_controller', null);
				$action = get_query_var('_api_action', null);

				if($controller && $action) {
					$template = __DIR__ . '/api/v1/api.php';
				}
				return $template;
			}, 99 );
		});
	}
}

new PC_API();
new PC_Admin();