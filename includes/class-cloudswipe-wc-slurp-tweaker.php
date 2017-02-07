<?php

class CloudSwipe_WC_Slurp_Tweaker {

    protected static $instance;

    /**
     * The Slurp Tweaker should only be loaded one time
     *
     * @since 1.0.0
     * @static
     * @return Slurp Tweaker instance
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_filter( 'the_title', array( $this, 'filter_slurp_title' ) );
    }

    /**
     * Allow slurps to change the title of the slurped page
     *
     * The slurp request can pass the query string parameter cloudswipe-title to
     * change the displayed page title from "Cart" to a custom value. This
     * filter is only run on the cart page content area. If the cloudswipe-title
     * parameter is not in the query string, the title is not modified.
     *
     * @param  string $title
     * @param  int    $id
     * @return string The modified title
     */
    public function filter_slurp_title( $title, $id = null ) {
        if ( is_cart() && in_the_loop() ) {
            if ( isset( $_GET['cloudswipe-title'] ) ) {
                $title = $_GET['cloudswipe-title'];
            }
        }

        return $title;
    }

}

CloudSwipe_WC_Slurp_Tweaker::instance();
