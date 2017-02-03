<?php
/*
Plugin Name: Secure Hosted Payments for WooCommerce
Plugin URI: http://securehostedpayments.com
Description: Accept credit card payments securely on your WooCommerce store
Version: 1.0.2
Author: Reality66
Author URI: http://www.reality66.com

-------------------------------------------------------------------------
Copyright 2016  Reality66

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('WC_Secure_Hosted_Payments') ) {

    // Determin the path to the plugin file
    $plugin_file = __FILE__;
    if ( isset ( $plugin ) ) { $plugin_file = $plugin; }
    elseif ( isset ( $mu_plugin ) ) { $plugin_file = $mu_plugin; }
    elseif ( isset( $network_plugin ) ) { $plugin_file = $network_plugin; }

    // Define constants
    define( 'WCSHP_VERSION_NUMBER', '1.0.2' );
    define( 'WCSHP_PLUGIN_FILE', $plugin_file );
    define( 'WCSHP_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'WCSHP_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'WCSHP_DEBUG', false );

    // Register kernl update management
    require_once 'plugin_update_check.php';
    $kernl = new PluginUpdateChecker_2_0 (
       'https://kernl.us/api/v1/updates/56d0b2be97e281532fb39916/',
       __FILE__,
       'woocommerce-gateway-secure-hosted-payments',
       1
    );

    /**
     * The main plugin class should not be extended
     */
    final class WC_Secure_Hosted_Payments {

        protected $dependency_check = false;
        protected static $instance;

        /**
         * The plugin should only be loaded one time
         *
         * @since 1.0.0
         * @static
         * @return Plugin instance
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct() {
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

            // Check to see if WooCommerce is installed
            if ( $this->dependency_check() ) {
                $this->includes();
                add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
            }
        }

        public function add_gateway( $methods ) {
            $methods[] = 'WC_Gateway_Secure_Hosted_Payments';
            return $methods;
        }

        public function dependency_check() {
            $this->dependency_check = true;

            // If WooCommerce is not loaded show admin notice
            if ( ! class_exists('WooCommerce') ) {
                add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
                $this->dependency_check = false;
            }

            return $this->dependency_check;
        }

        public function dependency_notice() {
            ?>
            <div class="error">
                <p><?php _e( 'Secure Hosted Payments for WooCommerce requires the WooCommerce plugin to be installed and activated.', 'wc-shp' ); ?></p>
            </div>
            <?php
        }

        /**
         * Load the plugin text domain for translation.
         */
        public function load_plugin_textdomain() {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'wc-shp' );
            $language_path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

            load_plugin_textdomain( 'wc-shp', false, $language_path );
        }

        /**
         * Cloning is forbidden.
         *
         * @since 1.0.0
        */
        public function __clone () {
            _doing_it_wrong (
                __FUNCTION__,
                __( 'Cheatin&#8217; huh?' ),
                '1.0.0'
            );
        }

        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 1.0.0
         */
        public function __wakeup () {
            _doing_it_wrong (
                __FUNCTION__,
                __( 'Cheatin&#8217; huh?' ),
                '1.0.0'
            );
        }

        private function includes() {
            include_once WCSHP_PATH . 'includes/class-wc-gateway-secure-hosted-payments.php';
            include_once WCSHP_PATH . 'includes/class-shp-model.php';
            include_once WCSHP_PATH . 'includes/class-shp-validator.php';

            include_once WCSHP_PATH . 'includes/class-shp-api.php';
            include_once WCSHP_PATH . 'includes/class-shp-address.php';
            include_once WCSHP_PATH . 'includes/class-shp-exception.php';
            include_once WCSHP_PATH . 'includes/class-shp-invoice.php';
            include_once WCSHP_PATH . 'includes/class-shp-line-item.php';
            include_once WCSHP_PATH . 'includes/class-shp-line-total.php';
            include_once WCSHP_PATH . 'includes/class-shp-log.php';
            include_once WCSHP_PATH . 'includes/class-shp-env.php';
            include_once WCSHP_PATH . 'includes/class-shp-slurp-tweaker.php';
        }
    }
}

add_action( 'plugins_loaded', array( 'WC_Secure_Hosted_Payments', 'instance' ) );
