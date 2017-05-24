<?php

class CloudSwipe_WC_Log {
    public static function context() {
        return array ( 'source' => 'woo-cloudswipe-payments' );
    }

    public static function log( $level, $message ) {
        $logger = wc_get_logger();
        $logger->log( $level, $message, static::context() );
    }

    public static function debug( $message ) {
        static::log( 'debug', $message );
    }

    public static function info( $message ) {
        static::log( 'info', $message );
    }

    public static function notice( $message ) {
        static::log( 'notice', $message );
    }

    public static function warning( $message ) {
        static::log( 'warning', $message );
    }

    public static function error( $message ) {
        static::log( 'error', $message );
    }

    public static function critical( $message ) {
        static::log( 'critical', $message );
    }

    public static function alert( $message ) {
        static::log( 'alert', $message );
    }

    public static function emergency( $message ) {
        static::log( 'emergency', $message );
    }

    public static function write( $data ) {
        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
        $date = current_time('m/d/Y g:i:s A') . ' ' . get_option('timezone_string');
        $out = "========== $date ==========\nFile: $file" . ' :: Line: ' . $line . "\n$data";

        if( is_writable( WCCS_PATH ) ) {
            file_put_contents( WCCS_PATH . 'log.txt', $out . "\n\n", FILE_APPEND );
        }
    }
}
