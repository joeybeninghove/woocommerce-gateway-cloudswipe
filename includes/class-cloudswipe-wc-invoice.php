<?php

class CloudSwipe_WC_Invoice extends CloudSwipe_Wp_Invoice {
    public static function create( $attributes=[] ) {
        CloudSwipe_WC_Log::write( 'Sending these args over the API: ' . print_r( $args, true ) );

        try {
            return parent::create( $attributes );
        } catch ( Json_Api_Wp_Exception $e ) {
            throw new CloudSwipe_WC_Exception( $e->response );
        }
    }
}
