<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/woocommerce-gateway-cloudswipe.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-exception.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-model.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-address.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-validator.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-invoice.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-api.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-env.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-line-item.php';
require_once dirname( dirname( __FILE__ ) ) . '/includes/class-cs-line-total.php';

require $_tests_dir . '/includes/bootstrap.php';
