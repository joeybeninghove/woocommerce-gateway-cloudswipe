<?php

class CsInvoiceTest extends WP_UnitTestCase {

    /** @test */
    public function it_validates_an_invoice () {
        $invoice = $this->build_invoice();
        $isValid = $invoice->validate();
        $this->assertTrue( $isValid );
    }

    private function build_invoice () {
        $invoice = new Cs_Invoice();
        $invoice->first_name = 'John';
        $invoice->last_name = 'Doe';
        $invoice->email = 'john.doe@reality66.com';
        $invoice->total = 20.00;
        $invoice->customer_ip_address = '127.0.0.1';

        $widget = array (
            'name' => 'Widget',
            'sku' => 'wdg-01',
            'quantity' => 2,
            'total' => 20
        );
        $invoice->add_line_item( $widget );

        $invoice->add_line_total( 'Shipping', 5.00 );
        $invoice->add_line_total( 'Tax', '3.00' );

        $billing_address = array (
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'company'     => 'Reality66 LLC',
            'address_1'   => 'PO Box 224',
            'city'        => 'New Kent',
            'state'       => 'VA',
            'postal_code' => '23089',
            'country'     => 'USA',
            'phone'       => '888-888-8888',
            'email'       => 'john.doe@reality66.com',
            'type'        => 'billing'
        );
        $invoice->add_address( $billing_address, 'billing' );

        return $invoice;
    }

}
