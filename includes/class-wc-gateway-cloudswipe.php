<?php

class WC_Gateway_CloudSwipe extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'cloudswipe_wc';
        $this->has_fields         = false;
        $this->order_button_text  = __( 'Proceed to secure payment', 'wc-cloudswipe' );
        $this->method_title       = __( 'CloudSwipe', 'wc-cloudswipe' );
        $this->method_description = __( 'Securely accept credit card payments - PCI Compliant', 'wc-cloudswipe' );
        $this->supports           = array( 'products' );
        $this->init_form_fields();
        $this->init_settings();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_cloudswipe', array( $this, 'payment_notification' ) );
        add_action( 'woocommerce_api_cloudswipe_slurp_url', array( $this, 'slurp_url' ) );

        // Define values set by the user
        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );

        CloudSwipe_Wp::set_environment( "production" );
        CloudSwipe_Wp::set_secret_key( $this->settings['secret_key'] );
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'     => array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable CloudSwipe', 'wc-cloudswipe' ),
                'default' => 'yes'
            ),
			'secret_key' => array(
				'title'       => __( 'Secret Key', 'wc-cloudswipe' ),
				'type'        => 'text',
				'description' => __( 'The CloudSwipe Secret Key for your store.', 'wc-cloudswipe' ),
				'default'     => '',
				'desc_tip'    => true,
			),
            'title'           => array(
                'title'       => __( 'Title', 'wc-cloudswipe' ),
                'type'        => 'text',
                'description' => __( 'The title the user sees for this payment method during checkout', 'wc-cloudswipe' ),
                'default'     => __( 'Credit Card', 'wc-cloudswipe' ),
                'desc_tip'    => true,
            ),
            'description'     => array(
                'title'       => __( 'Description', 'wc-cloudswipe' ),
                'type'        => 'text',
                'description' => __( 'The description the user sees for this payment method during checkout', 'wc-cloudswipe' ),
                'default'     => __( 'Pay securely with your credit card', 'wc-cloudswipe' ),
                'desc_tip'    => true,
            )
        );
    }

    public function process_payment( $order_id ) {
        try {
            $wc_order = wc_get_order( $order_id );
            $cs_customer = CloudSwipe_WC_Customer::build_from_wc_order( $wc_order );
            $cs_line_items = CloudSwipe_WC_Line_Items::build_from_wc_order( $wc_order );
            $cs_line_totals = CloudSwipe_WC_Line_Totals::build_from_wc_order( $wc_order );
            $cs_metadata = CloudSwipe_WC_Metadata::build_from_wc_order( $wc_order );
            $cs_invoice = CloudSwipe_WC_Invoice::create(array (
                'total' => $wc_order->get_total() * 100,
                'currency' => $wc_order->get_order_currency(),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'return_url' => WC()->api_request_url( 'wc_gateway_cloudswipe' ),
                'customer' => $cs_customer->to_array(),
                'line_items' => $cs_line_items->to_array(),
                'line_totals' => $cs_line_totals->to_array(),
                'metadata' => $cs_metadata->to_array()
            ));

            CloudSwipe_WC_Log::write("Invoice: " . print_r($cs_invoice, true));

			$result = array(
				'result'   => 'success',
				'redirect' => $cs_invoice->links['pay']
			);

			return $result;
        } catch ( Json_Api_Wp_Exception $e ) {
			wc_add_notice( __( 'CloudSwipe Error:', 'wc-cloudswipe' ) . $e->getMessage(), 'error' );
        }
    }

    /**
     * Process payment notification for real after successful invoice payment
     */
    public function payment_notification() {
        if ( isset( $_GET['invoice_id'] ) ) {
            try {
                $cs_invoice = CloudSwipe_WC_Invoice::get_one( $_GET['invoice_id'] );
                $wc_order = wc_get_order( $cs_invoice->attributes['metadata']['wc_order_id'] );

                // Mark order complete.
                $wc_order->payment_complete();

                // Empty cart and clear session.
                WC()->cart->empty_cart();

                wp_redirect( $this->get_return_url( $wc_order ) );
                exit;

            } catch ( CloudSwipe_WC_Exception $e ) {
                wc_add_notice( __( 'Payment error:', 'wc-cloudswipe' ) . $e->getMessage(), 'error' );
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
