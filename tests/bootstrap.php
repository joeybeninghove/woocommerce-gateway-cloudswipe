<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/woocommerce-gateway-secure-hosted-payments.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-exception.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-model.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-address.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-validator.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-invoice.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-api.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-env.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-line-item.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-shp-line-total.php';

require $_tests_dir . '/includes/bootstrap.php';
