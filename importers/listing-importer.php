<?php
/**
 * This class handles taking a listing_data structure and insert/updates it into a wordpress
 * post with its associated meta data and categories.
 */


class PC_Listing_Importer {

	public $mappedFields;

	public function __construct() {

		//TODO: this needs to be moved as a configuration option in the ADMIN settings
		$this->mappedFields = array(
			'_listing_street' => 'property:address:street',
			'_listing_locality' => 'property:address:locality',
			'_listing_region' => 'property:address:region',
			'_listing_street_number' => 'property:address:streetNumber',
			'_listing_sub_number' => 'property:address:subNumber',
			'_listing_country' => 'property:address:country',
			'_listing_post_code' => 'property:address:postCode',
			'_listing_price' => 'price:from',
			'_listing_price_display' => 'price:display',
			'_listing_beds' => 'property:features:bedrooms',
			'_listing_baths' => 'property:features:bathrooms',
			'_listing_carports' => 'property:features:carports',
			'_listing_garages' => 'property:features:garages',
			'_listing_project_name' => 'project:name',
			'_listing_project_image' => 'project:titleImageUrl',
			'_listing_category' => 'property:category',
			'_listing_status' => 'status',
			'_listing_type' => 'type',
			'_listing_sub_type' => 'subType'
		);

	}



	public function import($listing_data) {

		if (!$listing_data)
	      return false;

		// We search by the custom field 'listing_id' which stores the id of the listing from Property Compass
		$args = array(
			'meta_query' => array(
		       array(
		           'key' => 'listing_id',
		           'value' => $listing_data->id
		       )
			),
			'posts_per_page' => 1,
			'post_type' => 'pc-listing',
			'post_status' => 'any'
		);

	    $listing = get_posts($args);

    	if ( $listing ) {
    		$listing_id = $listing[0]->ID;
    		$this->logit("UPDATE -> " . $listing_id);
		} else {
			$this->logit("INSERT -> " . $listing_data->id);
		}

		 $listing_post = array(
		      'ID'            => $listing_id,
		      'post_title'    => $this->get_title($listing_data),
		      'post_content'  => $listing_data->description,
		      'post_type'     => 'pc-listing',
		      'post_status'   => ( $listing ) ? $listing[0]->post_status : 'publish'
		   );

 		$listing_id = wp_insert_post( $listing_post );

 		$tags = $this->get_tags($listing_data);
 		if (!empty($tags)) {
 			wp_set_post_tags($listing_id, $tags);
 		}

		if ( $listing_id ) {
      		update_post_meta( $listing_id, '_listing_id', $listing_data->id );

      		if (!empty($listing_data->property->images)) {
      			$images = array_map(function ($ar) {return $ar->url;}, $listing_data->property->images);	
				$delimImages = implode("|", $images);

				//Featured image is first image in array
  				if (!empty($images)) {
  					update_post_meta( $listing_id, '_thumbnail_ext_url', $images[0] );
  					update_post_meta( $listing_id, '_thumbnail_id', 'by_url' );
  				}
      		}
      		update_post_meta( $listing_id, 'images', $delimImages );

			foreach($this->mappedFields as $k => $id ){
				$v = $this->get_mapped_value($listing_data, $id);
				if ($v) {
					update_post_meta( $listing_id, $k, $v);
				}
			}

      		//update_post_meta( $listing_id, 'json', addslashes( json_decode($listing_data)) );

	      // Remove if you don't need to import tags and make sure
	      // the tag name is correct if you do
	      //wp_set_object_terms( $developer_id, $developer_data->tags, 'developer_tag' );
	      //$this->generate_background_images( $developer_data->full_name, $developer_data->avatar->large_url );
    	}

    	return true;
	}
	
	/**
	 * Loops through a mapping to retrieve the value from the listing object
	 * @param  [type] $listing the listing object being processed
	 * @param  [type] $map     a delimetered string (':') representing a property mapping
	 */
	public function get_mapped_value($listing, $map) {
		$mapping = explode(':', $map);
		$i = 0;
		$tmp = $listing;
		while ($i < count($mapping)) {
			$p = $mapping[$i];
			$tmp = $tmp->$p;
			$i++;
		}

		return $tmp;
	}

	/**
     * Get the formatted title.
     */
    public function get_title($listing) {
        return esc_attr($listing->headline);
    }

    /**
     * Get a list of tags for a listing
     */
    public function get_tags($listing) {
    	return array_filter(array ($listing->type, $listing->subType, $listing->property->category, $listing->property->construction));
    }

    private function logit($mesg) {
    	return print_r("" . $mesg . "\n");
    }

}