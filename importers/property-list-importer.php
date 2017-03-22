<?php
/**
 * This class handles taking a listing_data structure and insert/updates it into a wordpress
 * post with its associated meta data and categories.
 */


class PC_PropertyList_Importer {

	public $mappedFields;

	public function __construct() {

		//TODO: this needs to be moved as a configuration option in the ADMIN settings
		$this->mappedFields = array(
			'_propertylist_lister_id' => 'listerId',
			'_propertylist_lister_name' => 'listerName',
			'_propertylist_lister_company' => 'listerCompany',
			'_propertylist_lister_Email' => 'listerEmail',
			'_propertylist_lister_Mobile' => 'listerMobile',
			'_propertylist_last_modified' => 'lastModified',						
			'_propertylist_created' => 'created',						
		);
	}

	public function import($propertyListData) {

		if (!$propertyListData)
	      return false;

	  	
		// We search by the custom field 'property_list_id' which stores the id of the property-list from Property Compass
		$args = array(
			'meta_query' => array(
		       array(
		           'key' => '_propertylist_id',
		           'value' => $propertyListData->id
		       )
			),
			'posts_per_page' => 1,
			'post_type' => 'pc-property-list',
			'post_status' => 'any'
		);

	    $propertyList = get_posts($args);

    	if ( $propertyList ) {
    		$property_list_id = $propertyList[0]->ID;
    		$this->logit("UPDATE -> " . $property_list_id);
		} else {
			$this->logit("INSERT -> " . $propertyListData->id);
			$isNew = true;			
		}

		 $post = array(
		      'ID'            => $property_list_id,
		      'post_title'    => $this->get_title($propertyListData),
		      'post_content'  => $propertyListData->description,
		      'post_type'     => 'pc-property-list',
		      'post_status'   => ($propertyList) ? $propertyList[0]->post_status : 'publish' //if it exists keep the post status unchanged
		   );

 		$property_list_id = wp_insert_post( $post );

 		/*$tags = $this->get_tags($listing_data);
 		if (!empty($tags)) {
 			wp_set_post_tags($listing_id, $tags);
 		}*/

		if ( $property_list_id ) {
      		update_post_meta( $property_list_id, '_propertylist_id', $propertyListData->id );

			foreach($this->mappedFields as $k => $id ){
				$v = $this->get_mapped_value($propertyListData, $id);
				if ($v) {
					update_post_meta( $property_list_id, $k, $v);
				}
			}

			//If this is import is an Update
			// 1. Get all listingId's currently stored in DB
			// 2. Cross reference those against what is being imported
			// 3. Any listings that exist in DB but don't exist in import... delete. 
			if (!$isNew) {

				//1. get all listing Ids
				$existingListingIds = $this->get_meta_values('_listing_id', 'pc-listing'); //returns "{listingId}|{postId}""				
				$currentListingIds = $this->arrobj_column($propertyListData->listings, 'listingId');
				
				foreach($existingListingIds as $existing) {
					$exArr = explode("|", $existing);

					//2. Cross reference import data to ensure listing still exists
					$stillExistsInPropertyList = array_search($exArr[0], $currentListingIds, true);
					if($stillExistsInPropertyList === false){ 

						//3. Listing doesn't exist in import... remove from Property List
						print_r(">> DELETE listing no longer in property list - " . $exArr[0]. "\n");
						wp_delete_post( $exArr[1], true); 
					}
				}

			} // END remove
			

			//INSERT all listings from Property List
			$listingsImporter = new PC_Listing_Importer();
			foreach($propertyListData->listings as $listing) {
				$listing_post_id = $listingsImporter->import($listing);
				update_post_meta( $listing_post_id, '_listing_propertylist_id', $propertyListData->id );
				update_post_meta( $listing_post_id, '_listing_propertylist_post_id', $property_list_id );
				print_r(">> IN - " . $listing->id . " to post " . $listing_post_id . "\n");
			}

			
			$listing_ids = array_map(function ($l) { return $l->listingId; }, $propertyListData->listings);				
			update_post_meta( $property_list_id, '_propertylist_listings_ids', $listing_ids );

	      // Remove if you don't need to import tags and make sure
	      // the tag name is correct if you do
	      //wp_set_object_terms( $developer_id, $developer_data->tags, 'developer_tag' );
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

	public function arrobj_column($items, $column_key, $index_key = null) {
	  // clap the specified column as resulted value
	  $values = array_map(
	    function($item, $key) { return is_object($item) ? $item->{$key} : $item[$key];}, 
	    $items, 
	    array_fill(0, count($items), $column_key)
	  );  
	  // return the values if no index source is specified
	  if (!$index_key) return $values;
	  // clap the specified column as resulted index
	  $indices = array_map(
	    function($item, $key) { return is_object($item) ? $item->{$key} : $item[$key];}, 
	    $items, 
	    array_fill(0, count($items), $index_key)
	  );  
	  // inject the indices onto values
	  $values = array_combine($indices, $values);
	  // return
	  return $values;
	}

	/**
     * Get the formatted title.
     */
    public function get_title($listing) {
        return esc_attr($listing->name);
    }

	function get_meta_values( $key = '', $type = 'post', $status = 'publish' ) {
	    global $wpdb;
	    if( empty( $key ) )
	        return;
	    $r = $wpdb->get_col( $wpdb->prepare( "
	        SELECT CONCAT(pm.meta_value, '|', p.ID) FROM {$wpdb->postmeta} pm
	        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	        WHERE pm.meta_key = '%s' 
	        AND p.post_status = '%s' 
	        AND p.post_type = '%s'
	    ", $key, $status, $type ) );
	    return $r;
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