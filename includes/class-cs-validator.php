<?php

class Cs_Validator {

    protected $errors;

    /**
     * __construct
     *
     * Construct the validator with the data array from the model
     * being validated.
     *
     * @param array $data
     */
    public function __construct( $data = array() ) {
        $this->data = $data;
        $this->errors = array();
    }

    /**
     * Return true if all of the validation rules pass, otherwise
     * return false.
     *
     * @param string $field The name of the key in the $data array for the model
     * @param string $rules The validation rules for the given field
     * @return boolean
     */
    public function check( $field, $rules ) {
        $is_valid = false;
        $validations = $this->parse_rules( $rules );

        // Check all the validation rules for the given field
        if ( is_array( $validations ) && count( $validations ) ) {
            foreach( $validations as $validation ) {
                $this->{ $validation->function }( $field,  $validation->params );
            }
        }

        // If no errors have been set of the given field the validation passes
        if ( ! isset( $this->errors[ $field ] ) ) {
            $is_valid = true;
        }

        return $is_valid;
    }

    /**
     * Return the errors array containing error messages for
     * the model's data fields
     *
     * The keys are the keys from the model's data felds and the
     * values are the error messages.
     *
     * @return array
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * The $rule_string is a list of validation requirements in the format
     * function:param1,param2|function:param1,param2
     *
     * The function parameters are optional and not always present. For
     * example, a rule string might look like this:
     *   required|in:value1,value2
     *
     * Return an array of validation objects:
     *   $validation = new stdClass();
     *   $validation->function = (string) function name
     *   $validation->params   = (array)  list of params for function
     *
     * @param string $rule_string
     * @return array Array of stdClass validation objects
     */
    public function parse_rules( $rule_string ) {
        $validations = array();
        $rules = explode( '|', $rule_string );

        foreach ( $rules as $rule ) {
            $function = $rule;
            $params = array();

            // Check to see if the validation function has parameters
            if ( false !== strpos( $rule, ':' ) ) {
                list( $function, $param_string ) = explode( ':', $rule );
                $params = explode( ',', $param_string );
            }

            $validation = new stdClass();
            $validation->function = $function;
            $validation->params = $params;
            $validations[] = $validation;
        }

        return $validations;
    }

    public function required( $field, $params ) {
        if ( isset( $this->data[ $field ] ) && empty( $this->data[ $field ] ) ) {
            $this->errors[ $field ] = "$field is required";
        }

    }

    /**
      * The $field must contain a value listed in the $allowed_values array
      *
      * @param string $field
      * @param array $allowed_values
     */
    public function in( $field, $allowed_values ) {
        if ( isset( $this->data[ $field ] ) && ! empty ( $this->data[ $field ] ) ) {
            if ( ! in_array( $this->data[ $field ], $allowed_values  ) ) {
                $this->errors[ $field ] = "$field has an invalid value";
            }
        }
    }

    public function numeric( $field, $params ) {
        if ( isset( $this->data[ $field ] ) && ! is_numeric( $this->data[ $field ] ) ) {
           $this->errors[ $field ] = "$field must be a number";
        }
    }

    /**
     * Set a custom error message for a validation failure
     *
     * @param string $field
     * @param array $value
     */
    public function message( $field, $value ) {
        if ( ! empty( $this->errors[ $field ] ) ) {
            $this->errors[ $field ]  = array_shift( $value );
        }
    }

}
