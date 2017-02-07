<?php

include( "/Users/joey/Sites/wp/wp-load.php" );

require __DIR__ . '/../vendor/autoload.php';

Json_Api_Wp_Http::$timeout = 5;
CloudSwipe_Wp::set_environment( "development" );
CloudSwipe_Wp::set_secret_key( "sk_store_4ac40d63c965132addbf0c19" );

try {
    $invoice = CloudSwipe_Wp_Invoice::create([
        "description" => "Test Invoice from cloudswipe-wp library",
        "total" => 1234,
        "currency" => "USD"
    ]);
    print_r( $invoice );
} catch ( Json_Api_Wp_Exception $e ) {
    print_r( $e->get_message() );
}
