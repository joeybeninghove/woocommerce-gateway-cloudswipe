<?php
class Json_Api_Wp_Exception extends Exception {
    public $response, $headers;

    public function __construct ( $response ) {
        $this->response = $response["response"];
        $this->headers = $response["headers"];
    }

    public function get_status_code() {
        return $this->response["code"];
    }

    public function get_message() {
        return $this->response["message"];
    }
}
