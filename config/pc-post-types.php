<?php

class PC_PostTypes
{
	public function __construct() {
		  // Add listing metaboxes.
        add_action('admin_init', array($this, 'add_listing_summary_metaboxes'));
        add_action('save_post', array($this, 'save_listing_summary_metaboxes'));
        add_filter( 'manage_edit-pc-listing_columns', array($this, 'pc_listing_columns') );
        add_action( 'manage_pc-listing_posts_custom_column' , array($this, 'custom_pc_listing_column'), 10, 2 );

        add_action( 'admin_print_styles' , array($this, 'custom_admin_print_styles' ));

        apply_filters( 'disable_months_dropdown', true, "pc-listing" );
	}

  /**
     * Listing post type is used as a container for all properties. Listings
     * are linked to a project.
     */
    public function create_listing_post_type() {
        register_post_type('pc-listing',
            array(
                'has_archive' => false,
                'hierarchical' => false,
                'labels' => array(
                    'name' => __('PC Listings'),
                    'singular_name' => __('PC Listing')
                ),
                'public' => true,
                'rewrite' => array('slug' => 'listing'),
                //'taxonomies' => array('category'),
                'supports' => $this->get_listing_post_type_support(),

                //'supports'            => array( ),
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'map_meta_cap'        => true,

                'capability_type' => 'post',
                'capabilities' => array(
                    'create_posts' => 'do_not_allow', // false < WP 4.5, credit @Ewout
                )
            )
        );
    }

  function custom_admin_print_styles() {
    global $post_type;
    if( 'pc-listing' == $post_type ) {
      echo '<style>';
      echo '#posts-filter #cat{ display: none; }';
      echo '</style>';
    }
  }

	/**
     * Setup all the required post types.
     */
    public function setup() {
        //$this->create_project_post_type();
        $this->create_listing_post_type();
    }

     /**
     * Adds columns into the PC_Listing post type index page
     */
     function pc_listing_columns($columns) {

        unset( $columns['categories'] );
        unset( $columns['date'] );
	      $new_columns = array(
          'type' => __('Type', 'type'),
          'status' => __('Status', 'status'),
	        'category' => __('Category', 'category'),
	        'project-name' => __('Project', 'project-name'),

	        );
        return array_merge($columns, $new_columns);
    }

    function custom_pc_listing_column($column, $post_id) {
         switch ( $column ) {

            case 'type' :
                $type = $this->get_listing_type($post_id);
                if ( is_string( $type ) )
                    echo $type;
                else
                    _e( 'Unable to get listing type');
            break;
            case 'category' :
                $category = $this->get_listing_category($post_id);
                if ( is_string( $category ) )
                    echo $category;
                else
                    _e( 'Unable to get listing category');
            break;
            case 'status':
                $status = $this->get_listing_status($post_id);
                 if ( is_string( $status ) )
                    echo $status;
                else
                    _e( 'Unable to get listing status');
            case 'project-name':
                $projectName = get_post_meta($post_id, 'project_name', true);
                if ( is_string( $projectName ) )
                    echo $projectName;
                else
                    _e( 'Unable to get listing project name' );
            break;

        }
     }

   

    public function get_listing_post_type_support() {
        return array(
            'title',
            'editor',
            'thumbnail',
        );
    }

     /**
     * Register our listing metaboxes.
     */
    public function add_listing_summary_metaboxes() {
        

        add_meta_box(
            $id='listing_info_metabox',
            $title = 'Listing Summary',
            $callback = array($this, 'get_listing_summary_metabox'),
            $post_type = 'pc-listing',
            $content = 'side',
            $priority = 'default'
        );
    }

     /**
     * Save all our custom metabox info if it has been updated.
     */
    public function save_listing_summary_metaboxes() {
        global $post;

         if($_POST && isset($post)) {
            if($post->post_type == "pc-listing") {
                // Don't save this for now we don't want to allow it.
                //$property_key = esc_attr($_POST['property_key']);
                //update_post_meta($post->ID, 'extraFields_pcPropertyKey', $property_key);
            }
        }
    }


      public function get_listing_summary_metabox() {
        global $post;
        $listing_status = $this->get_listing_status($post->ID);
        $listing_type = get_post_meta($post->ID, 'type', true);
        //$listing_category = get_post_meta($post->ID, 'extraFields_pcSaleStatus', true);
        require_once "listing_summary.tpl.php";
    }

     function get_listing_status($post_ID) {
        return get_post_meta($post_ID, 'status', true);
     }

     function get_listing_category($post_ID) {
        return get_post_meta($post_ID, 'category', true);
     }

     function get_listing_type($post_ID) {
        return get_post_meta($post_ID, 'type', true);
     }

}


