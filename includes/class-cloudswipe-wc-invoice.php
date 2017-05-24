<?php

class CloudSwipe_WC_Invoice extends CloudSwipe_Wp_Invoice {
    public static function create( $attributes=[] ) {
        CloudSwipe_WC_Log::debug( 'Sending these attributes over the API: ' . print_r( $attributes, true ) );

        return parent::create( $attributes );
    }
}
