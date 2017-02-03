<?php

class Shp_Line_Total extends Shp_Model {

    /**
     * __construct
     *
     * Optionally set the attributes of the line item by passing
     * an assoc array of values to overwrite the default values.
     *
     * @param array $attributes
     */
    public function __construct( $name, $total ) {
        $data = array (
            'name'  => $name,
            'total' => $total
        );

        $validation_rules = array(
            'name'  => 'required',
            'total' => 'required'
        );

        parent::__construct( $data, $validation_rules );
    }

}

