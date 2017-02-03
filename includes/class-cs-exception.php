<?php
class Cs_Exception extends Exception {

    /**
     * Alias getMessage as get_message to keep naming
     * convention consistent
     */
    public function get_message() {
        return parent::getMessage();
    }

}
