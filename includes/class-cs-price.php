<?php

class Cs_Price {
    public $amount, $currency_code;

	public function __construct( $amount, $currency_code ) {
        $this->amount = number_format( $amount, 2, '.', '' );
        $this->currency_code = $currency_code;
    }

    public function currency_symbol() {
        return html_entity_decode(
            get_woocommerce_currency_symbol(
                $this->currency_code
            )
        );
    }

    public function __toString() {
        return "{$this->currency_symbol()}{$this->amount}";
    }
}
