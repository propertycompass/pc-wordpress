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


