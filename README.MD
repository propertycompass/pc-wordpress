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

## Mapping
At some point the mapping will be added as a configuration option. For now the mapping is defined in **importers/listing-importer.php** 

The first value represents the **wp_postmeta** name to use and the second value is the property name from the Property Compass  listing structure.

```php	
		$this->mappedFields = array(
			'street' => 'property:address:street',
			'locality' => 'property:address:locality',
			'region' => 'property:address:region',
			'street_number' => 'property:address:streetNumber',
			'sub_number' => 'property:address:subNumber',
			'country' => 'property:address:country',
			'post_code' => 'property:address:postCode',
			'price' => 'price:from',
			'price_display' => 'price:display',
			'beds' => 'property.features.bedrooms',
			'baths' => 'property.features.bathrooms',
			'carports' => 'property.features.carports',
			'garages' => 'property.features.garages',
			'project_name' => 'project.name',
			'project_image' => 'project.titleImageUrl',
			'category' => 'property.category',
			'type' => 'type',
			'sub_type' => 'subType'
		);
```

## Property Compass Listing Structue
This is a sample of the PC JSON listing structure as of **September 2016** and can be used as a reference when needing to add additional listing information to Wordpress.

```json
[
  {
    "listerId": "ea7efdb1-1541-46b4-a24e-64660e733c44",
    "propertyId": "46dbed11-0c0d-4593-bbc2-21dcc884dbfe",
    "reference": "LI-000-0010-90",
    "created": "2016-06-22T08:18:25.5993019Z",
    "type": "residential",
    "subType": null,
    "status": "available",
    "statusDate": "2016-06-22T08:18:26.4993202Z",
    "headline": "Amazing Investment Opportunity",
    "description": "lorem ipsum descriptions",
    "price": {
      "from": 400000.0,
      "to": null,
      "land": null,
      "prefix": null,
      "view": "$400,000",
      "display": "$400,000",
      "hide": null
    },
    "auction": null,
    "inspections": [],
    "contacts": [
      {
        "referenceId": "016e2dd9-5c7d-41bd-9d41-9de064ed1b1d",
        "id": null,
        "email": "pcscm@nrasaustralia.com.au",
        "name": "",
        "company": null,
        "mobile": null,
        "phone": "1300 67 27 28",
        "role": "agent"
      }
    ],
    "managers": [],
    "publishTo": [
      "784d7cc6-ad6a-4663-9ba1-dc73d2f43146"
    ],
    
    "property": {
      "id": "46dbed11-0c0d-4593-bbc2-21dcc884dbfe",
      "userId": "ea7efdb1-1541-46b4-a24e-64660e733c44",
      "address": {
        "subNumber": null,
        "lotNumber": null,
        "site": null,
        "streetNumber": "1",
        "street": "Palm Meadows Dr",
        "locality": "Carrara",
        "region": "QLD",
        "postCode": "4211",
        "country": "Australia"
      },
      "type": "residential",
      "category": "apartment",
      "construction": "newhome",
      "constructionStart": "2016-07-01T00:00:00",
      "constructionEnd": null,
      "ruralFeatures": null,
      "features": {
        "bedrooms": 2,
        "bathrooms": 2,
        "carports": 1,
        "ensuite": 1,
        "toilets": null,
        "openSpaces": null,
        "livingArea": null,
        "garages": null,
        "remoteGarage": null,
        "secureParking": false,
        "airConditioning": false,
        "alarmSystem": false,
        "vacuumSystem": null,
        "intercom": null,
        "pool": null,
        "poolInGround": null,
        "poolAboveGround": null,
        "spa": null,
        "tennisCourt": false,
        "balcony": true,
        "deck": null,
        "courtyard": false,
        "outdoorEnt": null,
        "shed": null,
        "fullyFenced": null,
        "openFirePlace": null,
        "livingAreas": null,
        "insideSpa": null,
        "outsideSpa": null,
        "broadband": false,
        "builtInRobes": false,
        "dishwasher": false,
        "ductedCooling": null,
        "ductedHeating": null,
        "evaporativeCooling": null,
        "floorboards": null,
        "gasHeating": null,
        "gym": false,
        "hydronicHeating": null,
        "payTv": false,
        "reverseCycleAirCon": null,
        "rumpusRoom": null,
        "splitSystemAirCon": null,
        "splitSystemHeating": null,
        "study": null,
        "workshop": null,
        "extra": null
      },
      "ecoFriendly": {
        "solarPanels": null,
        "solarHotWater": null,
        "waterTank": null,
        "greyWaterSystem": null
      },
      "views": {
        "city": null,
        "water": null,
        "valley": null,
        "mountain": null,
        "ocean": null
      },
      "idealFor": {
        "firstHomeBuyers": null,
        "investors": null,
        "downsizing": null,
        "couples": null,
        "students": null,
        "largeFamilies": null,
        "retirees": null
      },
      "buildingDetails": {
        "area": {
          "unit": "squareMeter",
          "amount": 88.0
        }
      },
      "landDetails": null,
      "images": [
        {
          "url": "http://cdn.cpx.net.au/image?id=71951",
          "title": "Main Image",
          "modified": "0001-01-01T00:00:00",
          "format": "image/jpeg"
        },
        {
          "url": "http://cdn.cpx.net.au/image?id=71953",
          "title": "Image a",
          "modified": "0001-01-01T00:00:00",
          "format": "image/jpeg"
        }
      ],
      "floorplans": [],
      "documents": [],
      "externalLinks": [],
      "customFields": [
        {
          "name": "isNRAS",
          "value": "no"
        }
      ],
      "closed": null,
      "lastModified": "2016-06-22T08:17:25.0013845Z"
    },
    "addressDisplay": "fulladdress",
    "addressStreetView": null,
    "soldPrice": null,
    "soldDate": null,
    "dateAvailable": null,
    "rent": null,
    "externalId": "33866",
    "authority": null,
    "commercial": null,
    "rental": null,
    "project": {
      "name": "Eleve",
      "titleImageUrl": "http://cdn.cpx.net.au/image?id=71952",
      "address": {
        "lotNumber": null,
        "subNumber": null,
        "streetNumber": "559",
        "street": "Albany Hwy",
        "locality": "Victoria Park",
        "region": "WA",
        "postCode": "6100",
        "country": "AU"
      },
      "images": [
        {
          "title": "image15.jpg",
          "url": "https://propertycompass.s3.amazonaws.com/projects/dacefa2f-5233-4645-9265-dbd45f48840d/images/image01.jpg",
          "format": "image/jpeg",
          "size": 128719
        },
        {
          "title": "image14.jpg",
          "url": "https://propertycompass.s3.amazonaws.com/projects/dacefa2f-5233-4645-9265-dbd45f48840d/images/image0.jpg",
          "format": "image/jpeg",
          "size": 125012
        },
        {
          "title": "image19.jpg",
          "url": "https://propertycompass.s3.amazonaws.com/projects/dacefa2f-5233-4645-9265-dbd45f48840d/images/image02.jpg",
          "format": "image/jpeg",
          "size": 63540
        }
      ],
      "documents": [
        {
          "title": "Vote count.xlsx",
          "url": "https://propertycompass.s3.amazonaws.com/projects/dacefa2f-5233-4645-9265-dbd45f48840d/documents/document0.xlsx",
          "format": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
          "size": 99203
        }
      ]
    },
    "projectId": "dacefa2f-5233-4645-9265-dbd45f48840d",
    "propertyLists": [
      "9f289271-0d1f-4f31-b229-f681647c6873"
    ],
    "id": "4eeda7dd-9981-4779-bb0f-86e42195f954",
    "lastModified": "2016-08-31T02:45:01.5274838Z"
  }
]
```