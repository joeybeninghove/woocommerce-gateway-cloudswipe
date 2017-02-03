<?php

class Shp_Env {

    public static $api_v1 = 'https://api.southchicken.com/v1/';

    public static function invoices() {
        return self::$api_v1 . 'invoices/';
    }

}
