<?php

class Cs_Model {

    protected $data;
    protected $errors;
    protected $validation_rules;

    /**
     * $data: An assoc array of fields for the model
     *
     * $errors: An assoc array of error messages from data validation.
     *   The keys are the keys from the $data array that have failed
     *   validation and the values are the error messages for that field.
     *
     * $validation_rules: An array of validation rules for the model's $data
     *   The keys are the same as the keys for the $data array and the values
     *   are the validation rules: 'required|in:billing,shipping'
     */
    public function __construct( $data = array(), $validation_rules = array() ) {
        $this->data             = $data;
        $this->validation_rules = $validation_rules;
        $this->errors           = array();
    }

    /**
     * set_validation_rules
     *
     * Provide an array of validation rules where the keys match
     * the keys from the $data array and the values are the
     * validation rules formatted as: 'required|in:billing,shipping'
     *
     * @param array $validation_rules
     */
    public function set_validation_rules( $validation_rules ) {
        $this->validation_rules = $validation_rules;
    }

    public function validate() {
        $validator = new Cs_Validator( $this->data );

        if ( is_array( $this->validation_rules ) ) {
            foreach ( $this->validation_rules as $field => $rules ) {
                $validator->check( $field, $rules );
            }
        }

        $this->errors = $validator->get_errors();

        return ! $this->has_errors();
    }

    public function has_errors() {
        return count( $this->errors ) > 0;
    }

    public function get_errors() {
        return $this->errors;
    }

    public function clear_errors() {
        $this->errors = array();
    }

    public function get_data() {
        return $this->data;
    }

    public function __get( $name ) {
        if ( array_key_exists( $name, $this->data ) ) {
            return $this->data[ $name ];
        }
    }

    public function __set( $name, $value ) {
        if ( array_key_exists( $name, $this->data ) ) {
            $this->data[ $name ] = $value;
        }
    }

}
