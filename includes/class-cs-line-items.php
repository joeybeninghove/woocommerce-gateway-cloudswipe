<?php

class Cs_Line_Items {
    public $headers, $rows;

    public function __construct() {
        $this->headers = array( 'Name', 'SKU', 'Quantity', 'Total' );
        $this->rows = array();
    }

    public static function build_from_wc_order( $wc_order ) {
        $cs_line_items = new static();

        $items = $wc_order->get_items();
        foreach( $items as $item ) {
            $product = $wc_order->get_product_from_item( $item );

            $cs_line_items->rows[] = array (
                $item['name'],
                $product->get_sku(),
                $item['qty'],
                strval( new Cs_Price(
                    $wc_order->get_item_subtotal( $item, false, true ),
                    $wc_order->get_order_currency()
                ))
            );
        }

        return $cs_line_items;
    }

    public function toArray() {
        return array(
            'header' => $this->headers,
            'rows' => $this->rows
        );
    }
}
