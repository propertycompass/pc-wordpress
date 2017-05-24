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
			'_listing_project_id' => 'projectId',
			'_listing_project_name' => 'project:name',
			'_listing_project_image' => 'project:titleImageUrl',
			'_listing_category' => 'property:category',
			'_listing_status' => 'status',
			'_listing_type' => 'type',
			'_listing_sub_type' => 'subType',
			'_listing_lat' => 'property:location:lat',
			'_listing_lon' => 'property:location:lng',
			'_listing_land_size' => 'property:landDetails:area:amount',
			'_listing_land_size_unit' => 'property:landDetails:area:unit',
			'_listing_building_size' => 'property:buildingDetails:area:amount',
			'_listing_building_size_unit' => 'property:buildingDetails:area:unit',
			'_listing_last_modified' => 'lastModified'
		);
	}

	public function import($listing_data) {

		if (!$listing_data)
	      return false;


	  	$reference_id = !is_null($listing_data->id) ? $listing_data->id : $listing_data->listingId;

		// We search by the custom field 'listing_id' which stores the id of the listing from Property Compass
		$args = array(
			'meta_query' => array(
		       array(
		           'key' => '_listing_id',
		           'value' => $reference_id
		       )
			),
			'posts_per_page' => 1,
			'post_type' => 'pc-listing',
			'post_status' => 'any'
		);

	    $listing = get_posts($args);

    	if ( $listing ) {
    		$listing_post_id = $listing[0]->ID;
    		$this->logit("UPDATE listing -> " . $listing_post_id);
		} else {
			$this->logit("INSERT listing -> " . $reference_id);
		}

	 	$listing_post = array(
	      'ID'            => $listing_post_id,
	      'post_title'    => $this->get_title($listing_data),
	      'post_content'  => $listing_data->description,
	      'post_type'     => 'pc-listing',
	      'post_status'   => ( $listing ) ? $listing[0]->post_status : 'publish'
	   	);

 		$listing_post_id = wp_insert_post( $listing_post );

 		$tags = $this->get_tags($listing_data);
 		if (!empty($tags)) {
 			wp_set_post_tags($listing_post_id, $tags);
 		}

		if ( $listing_post_id ) {
      		update_post_meta( $listing_post_id, '_listing_id', $reference_id );

      		if (!empty($listing_data->property->images)) {
      			$images = array_map(function ($ar) {return $ar->url;}, $listing_data->property->images);	
				$delimImages = implode("|", $images);

				//Featured image is first image in array
  				if (!empty($images)) {
  					update_post_meta( $listing_post_id, '_thumbnail_ext_url', $images[0] );
  					update_post_meta( $listing_post_id, '_thumbnail_id', 'by_url' );
  				}
      		}
      		
      		update_post_meta( $listing_post_id, 'images', $delimImages );
      		update_post_meta( $listing_post_id, '_listing_address', $this->get_address($listing_data));

			foreach($this->mappedFields as $k => $id ){
				$v = $this->get_mapped_value($listing_data, $id);
				if ($v) {
					update_post_meta( $listing_post_id, $k, $v);
				}
			}

      		//update_post_meta( $listing_post_id, 'json', addslashes( json_decode($listing_data)) );

	      // Remove if you don't need to import tags and make sure
	      // the tag name is correct if you do
	      //wp_set_object_terms( $developer_id, $developer_data->tags, 'developer_tag' );
	      //$this->generate_background_images( $developer_data->full_name, $developer_data->avatar->large_url );

			//Integration point to support custom actions each time a listing is imported
			do_action('pc_listing_imported', $listing_post_id, $listing_data);
    	}

    	return $listing_post_id;
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
    	if (!empty($listing->headline)) {
    		return esc_attr($listing->headline);	
    	}

    	return get_address($listing);
    }


    public function get_address($listing) {
    	$subNumber = $listing->property->address->subNumber;
		$streetNumber = $listing->property->address->streetNumber;
		$street = $listing->property->address->street;
		$locality = $listing->property->address->locality;
		$region = $listing->property->address->region;
		$postCode = $listing->property->address->postCode;

		if (!empty($subNumber)) 
			$streetNumber = $subNumber . "/" . $streetNumber;

		return $streetNumber . ' ' . $street . ' ' . $locality . ', ' . $postCode . ', '  . $region;
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