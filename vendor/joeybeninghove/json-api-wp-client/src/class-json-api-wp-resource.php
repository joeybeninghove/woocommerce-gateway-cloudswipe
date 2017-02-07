<?php

class Json_Api_Wp_Resource {
    public $id;
    public $type;
    public $base_url;
    public $attributes = [];
    public $links = [];

    public static $username;
    public static $password;

    public function __construct( $base_url, $type ) {
        $this->base_url = $base_url;
        $this->type = $type;
    }

    public function has_id() {
        return !empty( $this->id );
    }

    public static function auth( $username, $password="" ) {
        static::$username = $username;
        static::$password = $password;
    }

    public static function create( $attributes=[] ) {
        $resource = new static();
        $resource->attributes = $attributes;
        $http = new Json_Api_Wp_Http();
        $response = $http->post( $resource );
        $json = json_decode( $response["body"], true );

        return static::load_one( $json );
    } 

    public static function get_all() {
        $resource = new static();
        $http = new Json_Api_Wp_Http();
        $response = $http->get( $resource );
        $json = json_decode( $response["body"], true );

        return static::load_all( $json );
    }

    public static function get_one( $id ) {
        $resource = new static();
        $resource->id = $id;
        $http = new Json_Api_Wp_Http();
        $response = $http->get( $resource );
        $json = json_decode( $response["body"], true );

        return static::load_one( $json );
    }

    public static function load_one( $json ) {
        $resource = new static();
        $resource->id = $json["data"]["id"];
        $resource->type = $json["data"]["type"];
        $resource->attributes = $json["data"]["attributes"];
        $resource->links = $json["links"];
        return $resource;
    }

    public static function load_all( $json ) {
        $resources = [];
        foreach ( $json["data"] as $data ) {
            $resource = new static();
            $resource->id = $data["id"];
            $resource->type = $data["type"];
            $resource->attributes = $data["attributes"];
            $resource->links = $data["links"];
            $resources[] = $resource;
        }
        return $resources;
    }

    public function to_array() {
        $array = [];

        if ( $this->id )
            $array["data"]["id"] = $this->id;

        $array["data"]["type"] = $this->type;
        $array["data"]["attributes"] = $this->attributes;

        return $array;
    }

    public function to_json() {
        return json_encode( $this->to_array() );
    }
}
