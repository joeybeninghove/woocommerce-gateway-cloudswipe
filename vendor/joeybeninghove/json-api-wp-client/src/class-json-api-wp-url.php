<?php

class Json_Api_Wp_Url {
    public static function get_one( $resource ) {
        return "{$resource->base_url}{$resource->type}/{$resource->id}";
    }

    public static function get_all( $resource ) {
        return "{$resource->base_url}{$resource->type}";
    }

    public static function create( $resource ) {
        return "{$resource->base_url}{$resource->type}";
    }
}
