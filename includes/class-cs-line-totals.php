<?php

class Cs_Line_Totals {
    public $rows;

    public function __construct() {
        $this->rows = array();
    }

    public static function build_from_wc_order( $wc_order ) {
        $cs_line_totals = new static();

        $subtotal_label = __( 'Subtotal', 'wc-cs' );
        $subtotal_total = strval( new Cs_Price(
            $wc_order->get_subtotal(),
            $wc_order->get_order_currency()
        ));

        $shipping_label = __( 'Shipping', 'wc-cs' );
        $shipping_total = strval( new Cs_Price(
            $wc_order->get_total_shipping(),
            $wc_order->get_order_currency()
        ));

        $tax_label = __( 'Tax', 'wc-cs' );
        $tax_total = strval( new Cs_Price(
            $wc_order->get_total_tax(),
            $wc_order->get_order_currency()
        ));

        $discount_label = __( 'Discount', 'wc-cs' );
        $discount_total = strval( new Cs_Price(
            $wc_order->get_total_discount(),
            $wc_order->get_order_currency()
        ));

        $cs_line_totals->rows[]= array ( $subtotal_label, $subtotal_total );
        $cs_line_totals->rows[]= array ( $shipping_label, $shipping_total );
        $cs_line_totals->rows[]= array ( $tax_label, $tax_total );
        $cs_line_totals->rows[]= array ( $discount_label, $discount_total );

        return $cs_line_totals;
    }

    public function toArray() {
        return array(
            'rows' => $this->rows
        );
    }
}
