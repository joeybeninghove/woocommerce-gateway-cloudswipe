<?php

class CloudSwipe_WC_Address {
    public $name, $company, $line1, $line2,
        $city, $state, $zip, $country, $phone;

    public static function build_from_wc_order( $wc_order, $address_type ) {
        $cs_address = new static();
        $wc_address = $wc_order->get_address( $address_type );

        if ( $address_type == "billing" ) {
            $cs_address->name = $wc_order->get_formatted_billing_full_name();
        } elseif ( $address_type == "shipping") {
            $cs_address->name = $wc_order->get_formatted_shipping_full_name();
        }

        $cs_address->company = $wc_address['company'];
        $cs_address->line1 = $wc_address['address_1'];
        $cs_address->line2 = $wc_address['address_2'];
        $cs_address->city = $wc_address['city'];
        $cs_address->state = $wc_address['state'];
        $cs_address->zip = $wc_address['postcode'];
        $cs_address->country = $wc_address['country'];
        $cs_address->phone = $wc_address['phone'];

        return $cs_address;
    }

    public function to_array() {
        return array(
            'name' => $this->name,
            'company' => $this->company,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
            'phone' => $this->phone
        );
    }
}
