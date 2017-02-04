<?php

class Cs_Invoice {
    public static $secret_key;
    public $type = 'invoices';
    public $attributes = array();
    public $links = array();
    public $id;

    public static function auth( $secret_key ) {
        static::$secret_key = $secret_key;
    }

    public static function headers() {
        return array(
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
            'Authorization' => static::basic_auth_header()
        );
    }

    public static function basic_auth_header() {
        return 'Basic ' . base64_encode( static::$secret_key . ':' );
    }

    public static function create( $attributes ) {
        $cs_invoice = new static();
        $cs_invoice->attributes = $attributes;

        $args = array (
            'headers' => static::headers(),
            'body' => $cs_invoice->toJson()
        );

        Cs_Log::write( 'Sending these args over the API: ' . print_r( $args, true ) );

        $response = wp_remote_post( Cs_Env::invoices(), $args );

        if ( ! is_wp_error( $response ) ) {
            Cs_Log::write( 'Create invoice response: ' . print_r( $response, true ) );
            if ( $response['response']['code'] == 201 ) {
                $body = json_decode( $response['body'], true );
                $cs_invoice->id = $body['data']['id'];
                $cs_invoice->attributes = $body['data']['attributes'];
                $cs_invoice->links = $body['links'];
            }
        }
        else {
            $error = $response->get_error_message();
            throw new Cs_Exception("Unable to create invoice on CloudSwipe: $error");
        }

        return $cs_invoice;
    }

    public static function get_one( $id ) {
        $args = array (
            'headers' => static::headers()
        );

        $response = wp_remote_get( Cs_Env::invoices() . $id, $args );

        if ( $response['response']['code'] == 200 ) {
            $body = json_decode( $response['body'], true );
            $cs_invoice = new static();
            $cs_invoice->id = $body['data']['id'];
            $cs_invoice->attributes = $body['data']['attributes'];
            $cs_invoice->links = $body['links'];
            return $cs_invoice;
        }
        else {
            $error = $response->get_error_message();
            throw new Cs_Exception("Unable to retrieve invoice on CloudSwipe: $error");
        }
    }

    public function toArray() {
        return array (
            'data' => array (
                'type' => $this->type,
                'attributes' => $this->attributes
            )
        );
    }

    public function toJson() {
        return json_encode($this->toArray());
    }
}
