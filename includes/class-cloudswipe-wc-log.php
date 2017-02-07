<?php

class CloudSwipe_WC_Log {
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
