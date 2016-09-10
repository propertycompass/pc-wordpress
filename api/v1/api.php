<?php
require_once 'listings-controller.php';


class PropertyCompassAPIV1 {
  protected $controller;
  protected $action;
  protected $apiKey;

  public function __construct() {
      $controller = get_query_var('_api_controller', null) . 'Controller';
      $action = get_query_var('_api_action', null);

      $this->apiKey = get_option('property_compass_api_key');      
      $this->require_api_key();
      $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

      if (class_exists($controller))  {
        if (!$isPost || $this->compare_keys()){
          $instance = new $controller();
          $response = $instance->$action();
        }
      }

      wp_send_json_error('Unknown API: propertycompass/' . $controller . '/' . $action);
  }

  protected function require_api_key() {
    if (!isset($this->apiKey)) {
        throw new \Exception( "API Key missing from pluging configuration." );
    }
  }

   protected function compare_keys() {
    // Signature should be in a form of algorihm=hash
    // for example: X-PC-Signature: sha1=246d2e58593645b1f261b1bbc867fe2a9fc1a682

    if ( ! isset( $_SERVER['HTTP_X_PC_SIGNATURE'] ) ) {
      wp_send_json_error('400 Unauthorised : missing');
    }

    list( $algo, $hash ) = explode( '=', $_SERVER['HTTP_X_PC_SIGNATURE'], 2 ) + array( '', '' );
    $raw_post = file_get_contents( 'php://input' );
    if ( $hash !== hash_hmac( $algo, $raw_post, $this->apiKey ) ) {
      wp_send_json_error('400 Unauthorised : invalid');
    }
    return true;
  }
}

new PropertyCompassAPIV1();


