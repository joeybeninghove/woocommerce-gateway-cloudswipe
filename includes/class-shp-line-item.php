<?php

class Shp_Line_Item extends Shp_Model {

    /**
     * __construct
     *
     * Optionally set the attributes of the line item by passing
     * an assoc array of values to overwrite the default values.
     *
     * @param array $attributes
     */
    public function __construct( $attrs = array() ) {
        $data = array (
            'name'     => '',
            'sku'      => '',
            'quantity' => '',
            'total'    => ''
        );
        $data = array_merge ( $data, $attrs );

        $validation_rules = array(
           'name' => 'required',
           'total' => 'required'
        );

        parent::__construct( $data, $validation_rules );
    }

}
