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

        add_filter( 'admin_post_thumbnail_html', array($this, 'thumbnail_url_field' ));
        add_action( 'save_post', array($this, 'thumbnail_url_field_save'), 10, 2 );
        add_filter( 'post_thumbnail_html', array($this, 'thumbnail_external_replace'), 10, PHP_INT_MAX );
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
                'rewrite' => array('slug' => 'listing', 'with_front' => FALSE),
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
          'image' => __('Image', 'image'),
          'type' => __('Type', 'type'),
          'status' => __('Status', 'status'),
	        'category' => __('Category', 'category'),
	        'project-name' => __('Project', 'project-name')
	        );
        return array_merge($columns, $new_columns);
    }

    function custom_pc_listing_column($column, $post_id) {
         switch ( $column ) {
            case 'image':
              $img = $this->get_thumbnail_url($post_id);
              if ( is_string( $img ) )
                    echo '<img src="' . $img . '" width="250"></img>';
                else
                    _e( 'Unable to get listing type');
            break;
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
            break;
            case 'project-name':
                $projectName = get_post_meta($post_id, '_listing_project_name', true);
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
        $listing_type = get_post_meta($post->ID, '_listing_type', true);
        //$listing_category = get_post_meta($post->ID, 'extraFields_pcSaleStatus', true);
        require_once "listing_summary.tpl.php";
    }

     function get_listing_status($post_ID) {
        return get_post_meta($post_ID, '_listing_status', true);
     }

     function get_listing_category($post_ID) {
        return get_post_meta($post_ID, '_listing_category', true);
     }

     function get_listing_type($post_ID) {
        return get_post_meta($post_ID, '_listing_type', true);
     }

     function get_thumbnail_url($post_ID) {
        return get_post_meta($post_ID, '_thumbnail_ext_url', true);
     }

     function url_is_image( $url ) {
      if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
          return FALSE;
      }
      $ext = array( 'jpeg', 'jpg', 'gif', 'png' );
      $info = (array) pathinfo( parse_url( $url, PHP_URL_PATH ) );
      return
       isset( $info['extension'] )
          && in_array( strtolower( $info['extension'] ), $ext, TRUE );
  }

function thumbnail_url_field( $html ) {
    global $post;
    $value = get_post_meta( $post->ID, '_thumbnail_ext_url', TRUE ) ? : "";
    $nonce = wp_create_nonce( 'thumbnail_ext_url_' . $post->ID . get_current_blog_id() );
    $html .= '<input type="hidden" name="thumbnail_ext_url_nonce" value="' 
        . esc_attr( $nonce ) . '">';
    $html .= '<div><p>' . __('Or', 'txtdomain') . '</p>';
    $html .= '<p>' . __( 'Enter the url for feature image', 'txtdomain' ) . '</p>';
    $html .= '<p><input type="url" name="thumbnail_ext_url" value="' . $value . '"></p>';
    if ( ! empty($value) && $this->url_is_image( $value ) ) {
        $html .= '<p><img style="max-width:150px;height:auto;" src="' 
            . esc_url($value) . '"></p>';
        $html .= '<p>' . __( 'Leave url blank to remove.', 'pc-listing' ) . '</p>';
    }
    $html .= '</div>';
    return $html;
}

function thumbnail_url_field_save( $pid, $post ) {
    $cap = $post->post_type === 'page' ? 'edit_page' : 'edit_post';
    if (
        ! current_user_can( $cap, $pid )
        || ! post_type_supports( $post->post_type, 'thumbnail' )
        || defined( 'DOING_AUTOSAVE' )
    ) {
        return;
    }
    $action = 'thumbnail_ext_url_' . $pid . get_current_blog_id();
    $nonce = filter_input( INPUT_POST, 'thumbnail_ext_url_nonce', FILTER_SANITIZE_STRING );
    $url = filter_input( INPUT_POST,  'thumbnail_ext_url', FILTER_VALIDATE_URL );
    if (
        empty( $nonce )
        || ! wp_verify_nonce( $nonce, $action )
        || ( ! empty( $url ) && ! $this->url_is_image( $url ) )
    ) {
        return;
    }
    if ( ! empty( $url ) ) {
        update_post_meta( $pid, '_thumbnail_ext_url', esc_url($url) );
        if ( ! get_post_meta( $pid, '_thumbnail_id', TRUE ) ) {
            update_post_meta( $pid, '_thumbnail_id', 'by_url' );
        }
    } elseif ( get_post_meta( $pid, '_thumbnail_ext_url', TRUE ) ) {
        delete_post_meta( $pid, '_thumbnail_ext_url' );
        if ( get_post_meta( $pid, '_thumbnail_id', TRUE ) === 'by_url' ) {
            delete_post_meta( $pid, '_thumbnail_id' );
        }
    }
}

function thumbnail_external_replace( $html, $post_id ) {
    $url =  get_post_meta( $post_id, '_thumbnail_ext_url', TRUE );
    if ( empty( $url ) || ! $this->url_is_image( $url ) ) {
        return $html;
    }
    $alt = get_post_field( 'post_title', $post_id ) . ' ' .  __( 'thumbnail', 'txtdomain' );
    $attr = array( 'alt' => $alt );
    $attr = apply_filters( 'wp_get_attachment_image_attributes', $attr, NULL );
    $attr = array_map( 'esc_attr', $attr );
    $html = sprintf( '<img src="%s"', esc_url($url) );
    foreach ( $attr as $name => $value ) {
        $html .= " $name=" . '"' . $value . '"';
    }
    $html .= ' />';
    return $html;
}

}


