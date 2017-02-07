<?php

class CloudSwipe_Wp {
    public static $environment = "production";
    public static $urls = [
        "production" => "https://api.cloudswipe.com/v1/",
        "staging" => "https://api.southchicken.com/v1/",
        "development" => "http://api.cloudswipe.dev/v1/"
    ];

    public static function url() {
        return static::$urls[static::$environment];
    }

    public static function set_environment( $environment ) {
        static::$environment = $environment;
    }

    public static function set_secret_key( $secret_key ) {
        Json_Api_Wp_Resource::auth( $secret_key );
    }
}
