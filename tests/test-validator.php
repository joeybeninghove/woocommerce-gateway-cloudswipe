<?php

class ValidatorTest extends WP_UnitTestCase {

    /** @test */
    public function it_should_return_an_array_with_one_validation_item() {
        $rule_string = 'required';
        $validator = new Shp_Validator();
        $validations = $validator->parse_rules( $rule_string );
        $validation = array_shift( $validations );
        $this->assertEquals( 'required', $validation->function );
    }

    /** @test */
    public function it_should_return_an_array_with_one_validation_item_with_parameters() {
        $rule_string = 'in:billing,shipping';
        $validator = new Shp_Validator();
        $validations = $validator->parse_rules( $rule_string );
        $validation = array_shift( $validations );
        $this->assertEquals( 'in', $validation->function );
        $this->assertEquals( 'billing', $validation->params[0] );
        $this->assertEquals( 'shipping', $validation->params[1] );
    }

    /** @test */
    public function it_should_return_an_array_with_two_validation_items() {
        $rule_string = 'required|in:billing,shipping';
        $validator = new Shp_Validator();
        $validations = $validator->parse_rules( $rule_string );

        // First validation item should be 'required' with no parameters
        $validation = array_shift( $validations );
        $this->assertEquals( 'required', $validation->function );
        $this->assertEquals( array(), $validation->params );

        // Second validation item should be 'in' with two parametes
        $validation = array_shift( $validations );
        $this->assertEquals( 'in', $validation->function );
        $this->assertEquals( 'billing', $validation->params[0] );
        $this->assertEquals( 'shipping', $validation->params[1] );
    }

    /** @test */
    public function it_should_require_a_required_field() {
        $data = array( 'name' => '' );

        $validator = new Shp_Validator( $data );
        $is_valid = $validator->check( 'name', 'required' );
        $this->assertFalse( $is_valid );

        $errors = $validator->get_errors();
        $this->assertEquals( 'name is required', $errors['name'] );

    }

    /** @test */
    public function it_should_require_a_value_in_the_given_list() {
        $field = 'type';
        $data = array( $field => 'something_incorrect' );

        $validator = new Shp_Validator( $data );
        $is_valid = $validator->check( $field, 'in:billing,shipping' );
        $this->assertFalse( $is_valid );

        $errors = $validator->get_errors();
        $this->assertEquals( "$field has an invalid value", $errors[ $field ] );
    }

    /** @test */
    public function it_should_accept_a_value_in_the_given_list() {
        $field = 'type';
        $data = array( $field => 'billing' );

        $validator = new Shp_Validator( $data );
        $is_valid = $validator->check( $field, 'in:billing,shipping' );
        $this->assertTrue( $is_valid );

        $errors = $validator->get_errors();
        $this->assertEquals( array(), $errors, 'Expecting the errors array to be empty' );
    }

    /** @test */
    public function it_should_allow_a_custom_error_message_to_be_set_for_failed_validation() {
        $field = 'name';
        $data = array( $field => '' );
        $error_message = 'You must identify yourself!';

        $validator = new Shp_Validator( $data );
        $validator->check( $field, "required|message:$error_message" );

        $errors = $validator->get_errors();
        $this->assertEquals( $error_message, $errors[ $field ] );
    }

    /** @test */
    public function it_should_not_set_a_custom_error_message_if_the_data_is_valid() {
        $field = 'name';
        $data = array( $field => 'Lee' );
        $error_message = 'You must identify yourself!';

        $validator = new Shp_Validator( $data );
        $validator->check( $field, "required|message:$error_message" );

        $errors = $validator->get_errors();
        $this->assertArrayNotHasKey( $field, $errors );
    }

    /** @test */
    public function it_should_pass_validation_when_other_validation_has_already_failed() {
        $data = array( 'name' => '', 'email' => 'test@person.com' );

        $validator = new Shp_Validator( $data );
        $name_is_valid = $validator->check( 'name', 'required' );
        $this->assertFalse( $name_is_valid );

        $email_is_valid = $validator->check( 'email', 'required' );
        $this->assertTrue( $email_is_valid );

        $errors = $validator->get_errors();
        $this->assertArrayHasKey( 'name', $errors );
        $this->assertArrayNotHasKey( 'email', $errors );
    }

    /** @test */
    public function it_should_require_a_numeric_value() {
        $field = 'total';
        $data = array( $field => '' );
        $error_message = "$field must be a number";

        $validator = new Shp_Validator( $data);
        $validator->check( $field, "numeric|message:$error_message" );

        $errors = $validator->get_errors();
        $this->assertArrayHasKey( $field, $errors );
        $this->assertEquals( $error_message, $errors[ $field ] );
    }

    /** @test */
    public function it_should_accept_a_numeric_value() {
        $field = 'total';
        $data = array( $field => 10.50 );

        $validator = new Shp_Validator( $data);
        $validator->check( $field, "numeric" );

        $errors = $validator->get_errors();
        $this->assertArrayNotHasKey( $field, $errors );
    }

}
