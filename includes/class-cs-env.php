<?php

class Cs_Env {

    public static $api_v1 = 'http://api.cloudswipe.dev/v1/';

    public static function invoices() {
        return self::$api_v1 . 'invoices/';
    }

}
