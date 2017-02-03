<?php
/*
Plugin Name: WooCommerce CloudSwipe Payments
Plugin URI: https://wordpress.org/plugins/woocommerce-cloudswipe
Description: Accept credit card payments securely on your WooCommerce store
Version: 1.0.0
Author: CloudSwipe
Author URI: https://cloudswipe.com

-------------------------------------------------------------------------
Copyright 2017  CloudSwipe

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

if ( ! class_exists('WC_CloudSwipe') ) {

    // Determine the path to the plugin file
    $plugin_file = __FILE__;
    if ( isset ( $plugin ) ) { $plugin_file = $plugin; }
    elseif ( isset ( $mu_plugin ) ) { $plugin_file = $mu_plugin; }
    elseif ( isset( $network_plugin ) ) { $plugin_file = $network_plugin; }

    // Define constants
    define( 'WCCS_VERSION_NUMBER', '1.0.2' );
    define( 'WCCS_PLUGIN_FILE', $plugin_file );
    define( 'WCCS_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'WCCS_URL',  WP_PLUGIN_URL . '/' . basename(dirname($plugin_file)) . '/' );
    define( 'WCCS_DEBUG', false );

    /**
     * The main plugin class should not be extended
     */
    final class WC_CloudSwipe {

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
            $methods[] = 'WC_Gateway_CloudSwipe';
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
                <p><?php _e( 'CloudSwipe for WooCommerce requires the WooCommerce plugin to be installed and activated.', 'wc-cs' ); ?></p>
            </div>
            <?php
        }

        /**
         * Load the plugin text domain for translation.
         */
        public function load_plugin_textdomain() {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'wc-cs' );
            $language_path = dirname( plugin_basename( __FILE__ ) ) . '/languages/';

            load_plugin_textdomain( 'wc-cs', false, $language_path );
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
            include_once WCCS_PATH . 'includes/class-wc-gateway-cloudswipe.php';
            include_once WCCS_PATH . 'includes/class-cs-model.php';
            include_once WCCS_PATH . 'includes/class-cs-validator.php';

            include_once WCCS_PATH . 'includes/class-cs-api.php';
            include_once WCCS_PATH . 'includes/class-cs-address.php';
            include_once WCCS_PATH . 'includes/class-cs-exception.php';
            include_once WCCS_PATH . 'includes/class-cs-invoice.php';
            include_once WCCS_PATH . 'includes/class-cs-line-item.php';
            include_once WCCS_PATH . 'includes/class-cs-line-total.php';
            include_once WCCS_PATH . 'includes/class-cs-log.php';
            include_once WCCS_PATH . 'includes/class-cs-env.php';
            include_once WCCS_PATH . 'includes/class-cs-slurp-tweaker.php';
        }
    }
}

add_action( 'plugins_loaded', array( 'WC_CloudSwipe', 'instance' ) );
