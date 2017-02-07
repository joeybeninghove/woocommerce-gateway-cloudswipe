<?php

class CloudSwipe_Wp_Resource extends Json_Api_Wp_Resource {
    public function __construct( $type ) {
        parent::__construct( CloudSwipe_Wp::url(), $type );
    }
}
