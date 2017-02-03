<?php

class Shp_Invoice extends Shp_Model {


    /**
     * __construct
     *
     * Optionally set the attributes of the line item by passing
     * an assoc array of values to overwrite the default values.
     *
     * @param array $data
     */
    public function __construct( $attrs = array() ) {

        $data = array (
            'type' => 'woo',
            'remote_order_id' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'total' => 0,
            'customer_ip_address' => '',
            'content' => array(
                'billing_address' => '',
                'shipping_address' => '',
                'line_items' => array(),
                'line_totals' => array(),
                'meta_data' => array(),
            )
        );
        $data = array_merge ( $data, $attrs );

        $validation_rules = array(
            'status'    => 'required',
            'currency'  => 'required',
            'total'     => 'required|numeric'
        );

        parent::__construct( $data, $validation_rules );
    }

    public function add_line_item( $options = array() ) {
        $line_item = new Shp_Line_Item( $options );
        if ( $line_item->validate() ) {
            $this->data['content']['line_items'][] = $line_item;
        }
    }

    public function add_line_total( $name, $total) {
        $line_total = new Shp_Line_Total( $name, $total );
        if ( $line_total->validate() ) {
            $this->data['content']['line_totals'][] = $line_total;
        }
    }

    public function add_address($attrs = array(), $type = 'billing' ) {
        $address = new Shp_Address( $attrs );
        if ( $address->validate() ) {
            $this->data['content'][$type . '_address'] = $address;
        }
        else {
            Shp_Log::write( 'Invalid address not added to invoice: ' . print_r( $address->get_data(), true ) );
        }
    }

    public function add_meta( $key, $value ) {
        $this->data['content']['meta_data'][ $key ] = $value;
    }

    public function create( $secret_key ) {
        $api = new Shp_Api();
        $payment_url = $api->create_invoice( $this->data, $secret_key );
        return $payment_url;
    }

}
