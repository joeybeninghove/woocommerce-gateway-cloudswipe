<?php

class Cs_Metadata {
    public $data;

    public function __construct() {
        $this->data = array();
    }

    public static function build_from_wc_order( $wc_order ) {
        $cs_metadata = new static();

        $cs_metadata->data['wc_order_id'] = $wc_order->id;

        return $cs_metadata;
    }

    public function toArray() {
        return $this->data;
    }
}
