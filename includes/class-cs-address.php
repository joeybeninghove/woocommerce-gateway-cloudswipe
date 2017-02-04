<?php

class Cs_Address {
    public $name, $company, $line1, $line2,
        $city, $state, $zip, $country, $phone;

    public static function build_from_wc_order( $wc_order, $address_type ) {
        $cs_address = new static();
        $wc_address = $wc_order->get_address( $address_type );

        $cs_address->name = $wc_order->get_formatted_billing_full_name();
        $cs_address->company = $wc_address['company'];
        $cs_address->line1 = $address['address_1'];
        $cs_address->line2 = $address['address_2'];
        $cs_address->city = $address['city'];
        $cs_address->state = $address['state'];
        $cs_address->zip = $address['postcode'];
        $cs_address->country = $address['country'];
        $cs_address->phone = $address['phone'];

        return $cs_address;
    }

    public function toArray() {
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