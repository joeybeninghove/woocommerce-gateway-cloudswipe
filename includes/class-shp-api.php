<?php

class Shp_Api {

    /**
     * Return an array with basic authorization headers
     *
     * @param string $secret_key
     * @return array
     */
    public function basic_auth_args( $secret_key ) {
        Shp_Log::write( "Creating auth headers with secret key: $secret_key" );

        $auth = array( 'Authorization' => 'Basic ' . base64_encode( $secret_key . ':' ) );
        $args = array( 'headers' => $auth );

        return $args;
    }

    /**
     * Given the invoice order number, return the woocommerce order id
     *
     * @param string $invoice_number
     * @param string $secret_key
     * @return string WooCommerce order id
     */
    public function find_order_id_by_invoice_number( $invoice_number, $secret_key ) {
        $wc_order_id = null;
        $invoice_url = Shp_Env::invoices() . $invoice_number;
        $response = wp_remote_get( $invoice_url, $this->basic_auth_args( $secret_key ) );

        if ( ! is_wp_error( $response ) ) {
            $body = json_decode( $response['body'], true );
            if ( isset( $body['content'] ) ) {
                $content = json_decode( $body['content'], true );
                if ( isset( $content['meta_data']['wc_order_id'] ) ) {
                    $wc_order_id = $content['meta_data']['wc_order_id'];
                }
                else {
                    Shp_Log::write( 'WooCommerce order id not found in invoice: ' . print_r( $body, true ) );
                }
            }
            else {
                Shp_Log::write( 'WooCommerce order content not found in invoice: ' . print_r( $body, true ) );
            }
        }
        else {
            Shp_Log::write( 'Unable to look up invoice by order number: ' . $invoice_url . "\n" . $response->get_error_message() );
        }

        if ( ! isset( $wc_order_id ) || $wc_order_id < 1 ) {
            throw new Shp_Exception( "Unable to look up order number by invoice number: $invoice_number" . "\n" . $invoice_url );
        }

        return $wc_order_id;
    }

    /**
     * Create an invoice with the given order information
     *
     * @params array $order_data
     * @params string $secret_key
     * @return string Secure Hosted Payments URL to payment page
     */
    public function create_invoice( $order_data, $secret_key ) {
        $args = $this->basic_auth_args( $secret_key );
        $invoice_json = json_encode( $this->flatten_data( $order_data ) );
        $args['body'] = $invoice_json;

        Shp_Log::write( 'Sending these args over the API: ' . print_r( $args, true ) );

        $response = wp_remote_post( Shp_Env::invoices(), $args );

        if ( ! is_wp_error( $response ) ) {
            Shp_Log::write( 'Create invoice response: ' . print_r( $response, true ) );
            if ( $response['response']['code'] == 201 ) {
                $body = json_decode( $response['body'], true );
                return $body['payment_url'];
            }
        }
        else {
            $error = $response->get_error_message();
            throw new Shp_Exception("Unable to create invoice on Secure Hosted Payments: $error");
        }
    }

    /**
     * Take an array which may contain objects and turn it into an assoc array that can
     * be used for JSON
     *
     * @param array $data
     * @return array
     */
    public function flatten_data( $data, $key = null ) {
        $invoice_data = array();

        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
               $value = $this->flatten_data( $value, $key );
               $invoice_data[ $key ] = $value;
            }
            else {
                if ( is_object( $value ) ) {
                    $value = $value->get_data();
                }

                if ( isset( $key ) ) {
                    $invoice_data[ $key ] = $value;
                }
                else {
                    $invoice_data[] = $value;
                }
            }
        }

        return $invoice_data;
    }
}
