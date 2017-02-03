<?php

class WC_Gateway_CloudSwipe extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'cloudswipe_wc';
        $this->has_fields         = false;
        $this->order_button_text  = __( 'Proceed to secure payment', 'wc-cs' );
        $this->method_title       = __( 'CloudSwipe', 'wc-cs' );
        $this->method_description = __( 'Securely accept credit card payments - PCI Compliant', 'wc-cs' );
        $this->supports           = array( 'products' );
        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_cloudswipe', array( $this, 'payment_notification' ) );
        add_action( 'woocommerce_api_cs_slurp_url', array( $this, 'slurp_url' ) );

        // Define values set by the user
        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
    }


    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'     => array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable CloudSwipe', 'wc-cs' ),
                'default' => 'yes'
            ),
			'secret_key' => array(
				'title'       => __( 'Secret Key', 'wc-cs' ),
				'type'        => 'text',
				'description' => __( 'The CloudSwipe Secret Key for your store.', 'wc-cs' ),
				'default'     => '',
				'desc_tip'    => true,
			),
            'title'           => array(
                'title'       => __( 'Title', 'wc-cs' ),
                'type'        => 'text',
                'description' => __( 'The title the user sees for this payment method during checkout', 'wc-cs' ),
                'default'     => __( 'Credit Card', 'wc-cs' ),
                'desc_tip'    => true,
            ),
            'description'     => array(
                'title'       => __( 'Description', 'wc-cs' ),
                'type'        => 'text',
                'description' => __( 'The description the user sees for this payment method during checkout', 'wc-cs' ),
                'default'     => __( 'Pay securely with your credit card', 'wc-cs' ),
                'desc_tip'    => true,
            )
        );
    }

    public function process_payment( $order_id ) {
        $wc_order = wc_get_order( $order_id );

        $cs_invoice = new Cs_Invoice();
        $cs_invoice->remote_order_id = $order_id;
        $cs_invoice->first_name = $wc_order->billing_first_name;
        $cs_invoice->last_name = $wc_order->billing_last_name;
        $cs_invoice->email = $wc_order->billing_email;
        $cs_invoice->total = number_format( $wc_order->get_total(), 2, '.', '' );
        $cs_invoice->customer_ip_address = $_SERVER['REMOTE_ADDR'];
        $cs_invoice->add_meta( 'wc_order_id', $order_id );
		$cs_invoice->add_meta( 'return_url', WC()->api_request_url( 'wc_gateway_cloudswipe' ) );
        $cs_invoice->add_meta( 'currency', $wc_order->get_order_currency());

        $this->add_line_items( $cs_invoice, $wc_order );
        $this->add_line_totals( $cs_invoice, $wc_order );
        $this->add_addresses( $cs_invoice, $wc_order );

		try {
            $secret_key = $this->settings['secret_key'];
			$payment_url = $cs_invoice->create( $secret_key );

			$result = array(
				'result'   => 'success',
				'redirect' => $payment_url
			);

			return $result;
		} catch ( Cs_Exception $e ) {
			wc_add_notice( __( 'Secure Payment Error:', 'wc-cs' ) . $e->getMessage(), 'error' );
		}
    }

    /**
     * Add line items to invoice
     */
    public function add_line_items( $cs_invoice, $wc_order ) {
        $items = $wc_order->get_items();
        foreach( $items as $item ) {
            $product = $wc_order->get_product_from_item( $item );

            $line_item = array (
                'name' => $item['name'],
                'sku'  => $product->get_sku(),
                'quantity' => $item['qty'],
                'total' => $wc_order->get_item_subtotal( $item, false, true )

            );

            $cs_invoice->add_line_item( $line_item );
        }
    }

    /**
     * Add line totals to invoice
     */
    public function add_line_totals( $cs_invoice, $wc_order ) {
        $subtotal_label = __( 'Subtotal', 'wc-cs' );
        $subtotal_total  = number_format( $wc_order->get_subtotal(), 2, '.', '' );

        $shipping_label = __( 'Shipping', 'wc-cs' );
        $shipping_total = number_format( $wc_order->get_total_shipping(), 2, '.', '' );

        $tax_label      = __( 'Tax', 'wc-cs' );
        $tax_total      = number_format( $wc_order->get_total_tax(), 2, '.', '' );

        $discount_label = __( 'Discount', 'wc-cs' );
        $discount_total = number_format( $wc_order->get_total_discount(), 2, '.', '' );

        $line_totals = array (
            $subtotal_label => $subtotal_total,
            $shipping_label => $shipping_total,
            $tax_label      => $tax_total,
            $discount_label => $discount_total
        );

        foreach ( $line_totals as $label => $amount ) {
            if ( $amount > 0 ) {
                $cs_invoice->add_line_total( $label, $amount );
            }
        }
    }

    public function add_addresses( $cs_invoice, $wc_order ) {

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

        $cs_invoice->add_address( $billing_data, 'billing' );

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

        $cs_invoice->add_address( $shipping_data, 'shipping' );
    }

    // Process payment notification for real after successful invoice payment
    public function payment_notification() {
        if ( isset( $_GET['invoice_number'] ) ) {
            try {
                $invoice_number = $_GET['invoice_number'];
                $secret_key = $this->settings['secret_key'];
                $cs_api = new Cs_Api();
                $wc_order_number = $cs_api->find_order_id_by_invoice_number( $invoice_number, $secret_key );
                $wc_order = wc_get_order( $wc_order_number  );

                // Mark order complete.
                $wc_order->payment_complete();

                // Empty cart and clear session.
                WC()->cart->empty_cart();

                wp_redirect( $this->get_return_url( $wc_order ) );
                exit;

            } catch ( Cs_Exception $e ) {
                wc_add_notice( __( 'Payment error:', 'wc-cs' ) . $e->getMessage(), 'error' );
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
