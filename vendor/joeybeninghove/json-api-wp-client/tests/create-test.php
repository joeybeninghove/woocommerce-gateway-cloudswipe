<?php

include( "/Users/joey/Sites/wp/wp-load.php" );

require __DIR__ . '/../vendor/autoload.php';

class TestResource extends Json_Api_Wp_Resource {
    public function __construct() {
        parent::__construct(
            "http://api.cloudswipe.dev/v1/", "invoices"
        );
    }
}

Json_Api_Wp_Http::$timeout = 15;
TestResource::auth( "sk_store_4ac40d63c965132addbf0c19" );

try {
    $resource = TestResource::create([
        "description" => "Test Invoice from json-api-wp-client library",
        "total" => 1234,
        "currency" => "USD"
    ]);
    print_r( $resource );

    $resource = TestResource::get_one( $resource->id );
    print_r( $resource );
} catch ( Json_Api_Wp_Exception $e ) {
    print_r( $e->get_status_code() );
}
