<?php

class Cs_Address extends Cs_Model {

    /**
     * __construct
     *
     * Optionally set the attributes of the line item by passing
     * an assoc array of values to overwrite the default values.
     *
     * The default type of billing can be set to shipping.
     *
     * @param array $data
     */
    public function __construct( $attrs = array() ) {

        $data = array (
            'first_name'  => '',
            'last_name'   => '',
            'company'     => '',
            'address_1'   => '',
            'address_2'   => '',
            'city'        => '',
            'state'       => '',
            'postal_code' => '',
            'country'     => '',
            'phone'       => '',
            'email'       => '',
            'type'        => 'billing',
        );
        $data = array_merge ( $data, $attrs );

        $validation_rules = array(
            'first_name'  => 'required',
            'last_name'   => 'required',
            'address_1'   => 'required',
            'postal_code' => 'required',
            'type'        => 'required|in:billing,shipping'

        );

        parent::__construct( $data, $validation_rules );
    }

}
