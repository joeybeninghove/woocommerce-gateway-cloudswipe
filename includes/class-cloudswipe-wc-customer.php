<?php

class CloudSwipe_WC_Customer {
    public $name, $email, $billing_address, $shipping_address;

    public static function build_from_wc_order( $wc_order ) {
        $cs_customer = new static();

        $cs_customer->name = $wc_order->get_formatted_billing_full_name();
        $cs_customer->email = $wc_order->get_billing_email();
        $cs_customer->billing_address =
            CloudSwipe_WC_Address::build_from_wc_order(
                $wc_order, 'billing'
            );
        $cs_customer->shipping_address =
            CloudSwipe_WC_Address::build_from_wc_order(
                $wc_order, 'shipping'
            );

        return $cs_customer;
    }

    public function to_array() {
        return array(
            'name' => $this->name,
            'email' => $this->email,
            'billing_address' => $this->billing_address->to_array(),
            'shipping_address' => $this->shipping_address->to_array()
        );
    }
}
