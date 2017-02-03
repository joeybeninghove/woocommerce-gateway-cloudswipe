<?php

class WC_Gateway_Secure_Hosted_Payments extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'secure_hosted_payments_wc';
        $this->has_fields         = false;
        $this->order_button_text  = __( 'Proceed to secure payment', 'wc-shp' );
        $this->method_title       = __( 'Secure Hosted Payments', 'wc-shp' );
        $this->method_description = __( 'Securely accept credit card payments - PCI Compliant', 'wc-shp' );
        $this->supports           = array( 'products' );
        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_secure_hosted_payments', array( $this, 'payment_notification' ) );
        add_action( 'woocommerce_api_shp_slurp_url', array( $this, 'slurp_url' ) );

        // Define values set by the user
        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
    }


    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'     => array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Secure Hosted Payments', 'wc-shp' ),
                'default' => 'yes'
            ),
			'secret_key' => array(
				'title'       => __( 'Secret Key', 'wc-shp' ),
				'type'        => 'text',
				'description' => __( 'The Secure Hosted Payments access key for your store.', 'wc-shp' ),
				'default'     => '',
				'desc_tip'    => true,
			),
            'title'           => array(
                'title'       => __( 'Title', 'wc-shp' ),
                'type'        => 'text',
                'description' => __( 'The title the user sees for this payment method during checkout', 'wc-shp' ),
                'default'     => __( 'Credit Card', 'wc-shp' ),
                'desc_tip'    => true,
            ),
            'description'     => array(
                'title'       => __( 'Description', 'wc-shp' ),
                'type'        => 'text',
                'description' => __( 'The description the user sees for this payment method during checkout', 'wc-shp' ),
                'default'     => __( 'Pay securely with your credit card', 'wc-shp' ),
                'desc_tip'    => true,
            )
        );
    }

    public function process_payment( $order_id ) {
        $wc_order = wc_get_order( $order_id );

        $shp_invoice = new Shp_Invoice();
        $shp_invoice->remote_order_id = $order_id;
        $shp_invoice->first_name = $wc_order->billing_first_name;
        $shp_invoice->last_name = $wc_order->billing_last_name;
        $shp_invoice->email = $wc_order->billing_email;
        $shp_invoice->total = number_format( $wc_order->get_total(), 2, '.', '' );
        $shp_invoice->customer_ip_address = $_SERVER['REMOTE_ADDR'];
        $shp_invoice->add_meta( 'wc_order_id', $order_id );
		$shp_invoice->add_meta( 'return_url', WC()->api_request_url( 'wc_gateway_secure_hosted_payments' ) );
        $shp_invoice->add_meta( 'currency', $wc_order->get_order_currency());

        $this->add_line_items( $shp_invoice, $wc_order );
        $this->add_line_totals( $shp_invoice, $wc_order );
        $this->add_addresses( $shp_invoice, $wc_order );

		try {
            $secret_key = $this->settings['secret_key'];
			$payment_url = $shp_invoice->create( $secret_key );

			$result = array(
				'result'   => 'success',
				'redirect' => $payment_url
			);

			return $result;
		} catch ( Shp_Exception $e ) {
			wc_add_notice( __( 'Secure Payment Error:', 'wc-shp' ) . $e->getMessage(), 'error' );
		}
    }

    /**
     * Add line items to invoice
     */
    public function add_line_items( $shp_invoice, $wc_order ) {
        $items = $wc_order->get_items();
        foreach( $items as $item ) {
            $product = $wc_order->get_product_from_item( $item );

            $line_item = array (
                'name' => $item['name'],
                'sku'  => $product->get_sku(),
                'quantity' => $item['qty'],
                'total' => $wc_order->get_item_subtotal( $item, false, true )

            );

            $shp_invoice->add_line_item( $line_item );
        }
    }

    /**
     * Add line totals to invoice
     */
    public function add_line_totals( $shp_invoice, $wc_order ) {
        $subtotal_label = __( 'Subtotal', 'wc-shp' );
        $subtotal_total  = number_format( $wc_order->get_subtotal(), 2, '.', '' );

        $shipping_label = __( 'Shipping', 'wc-shp' );
        $shipping_total = number_format( $wc_order->get_total_shipping(), 2, '.', '' );

        $tax_label      = __( 'Tax', 'wc-shp' );
        $tax_total      = number_format( $wc_order->get_total_tax(), 2, '.', '' );

        $discount_label = __( 'Discount', 'wc-shp' );
        $discount_total = number_format( $wc_order->get_total_discount(), 2, '.', '' );

        $line_totals = array (
            $subtotal_label => $subtotal_total,
            $shipping_label => $shipping_total,
            $tax_label      => $tax_total,
            $discount_label => $discount_total
        );

        foreach ( $line_totals as $label => $amount ) {
            if ( $amount > 0 ) {
                $shp_invoice->add_line_total( $label, $amount );
            }
        }
    }

    public function add_addresses( $shp_invoice, $wc_order ) {

        // Billing address
        $billing_data = array (
            'first_name'  => $wc_order->billing_first_name,
            'last_name'   => $wc_order->billing_last_name,
            'company'     => $wc_order->billing_company,
            'address_1'   => $wc_order->billing_address_1,
            'address_2'   => $wc_order->billing_address_2,
            'city'        => $wc_order->billing_city,
            'state'       => $wc_order->billing_state,
            'postal_code' => $wc_order->billing_postcode,
            'country'     => $wc_order->billing_country,
            'phone'       => $wc_order->billing_phone,
            'email'       => $wc_order->billing_email
        );

        $shp_invoice->add_address( $billing_data, 'billing' );

        // Shipping address
        $shipping_data = array (
            'first_name'  => $wc_order->shipping_first_name,
            'last_name'   => $wc_order->shipping_last_name,
            'company'     => $wc_order->shipping_company,
            'address_1'   => $wc_order->shipping_address_1,
            'address_2'   => $wc_order->shipping_address_2,
            'city'        => $wc_order->shipping_city,
            'state'       => $wc_order->shipping_state,
            'postal_code' => $wc_order->shipping_postcode,
            'country'     => $wc_order->shipping_country
        );

        $shp_invoice->add_address( $shipping_data, 'shipping' );
    }

    // Process payment notification for real after successful invoice payment
    public function payment_notification() {
        if ( isset( $_GET['invoice_number'] ) ) {
            try {
                $invoice_number = $_GET['invoice_number'];
                $secret_key = $this->settings['secret_key'];
                $shp_api = new Shp_Api();
                $wc_order_number = $shp_api->find_order_id_by_invoice_number( $invoice_number, $secret_key );
                $wc_order = wc_get_order( $wc_order_number  );

                // Mark order complete.
                $wc_order->payment_complete();

                // Empty cart and clear session.
                WC()->cart->empty_cart();

                wp_redirect( $this->get_return_url( $wc_order ) );
                exit;

            } catch ( Shp_Exception $e ) {
                wc_add_notice( __( 'Payment error:', 'wc-shp' ) . $e->getMessage(), 'error' );
            }
        } elseif ( isset( $_POST['page_id'] ) ) {
            if ( isset( $_POST['access_key'] ) && $_POST['access_key'] == $this->access_key ) {
                wp_update_post( array( 'ID' => $_POST['page_id'], 'post_status' => 'private' ) );
            }
        }
    }

    public function slurp_url() {
        global $woocommerce;
        echo $woocommerce->cart->get_cart_url();
        exit;
    }

}
