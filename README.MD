Property Compass - Wordpress Plugin
===================


This plugin provides an API endpoint in a Wordpress website specifically for the Property Compass platform to integrate with.



## API

The base API URL is:

    /propertycompass/v1/listings/<ACTION>


### Actions


#### **POST**:  /upload

Endpoint to receive JSON payloads of listing information.

> **Notes:**

> - successful uploads are saved to:
	> `/wp-content/uploads/propertycompass/queue`
	
> - an authentication header is sent **X-PC-Signature** containing a HMAC code generated using the API  key
	> `X-PC-Signature: sha1=13cd736bb0d65c3f045e3e4dc2d7e95dfd51b15d`
	
> - only supports **application/json** content-types

#### **GET**:  /process

Endpoint to initiate import process of queued uploads

> **Notes:**

> - processes files from the upload queue folder:
	> `/wp-content/uploads/propertycompass/queue`
	
> - this endpoint should be routinely hit by a **cron** script every interval

> - importer checks for any existing posts with the listing Id
	>> If found the existing post is updated
	>> If not found a new post is created


## Post Type
This plugin creates a custom post type called **pc-listing** to store imported listing information. 

## Listing Mapping
At some point the mapping will be added as a configuration option. For now the mapping is defined in the importer classes. 

The first value represents the **wp_postmeta** key to use and the second value is the property name from the Property Compass listing structure.

### For Listings
```php	
		$this->mappedFields = array(
			'sting_locality' => 'property:address:locality',
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
      '_listing_sub_type' => 'subType'
		);
```

### For Property Lists
```php


```

## Property Compass Listing Structue
This is a sample of the PC JSON listing structure as of **September 2016** and can be used as a reference when needing to add additional listing information to Wordpress.

