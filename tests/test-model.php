<?php

class ModelTest extends WP_UnitTestCase {

    /** @test */
    public function it_should_accept_a_data_array() {
        $data = array (
            'name' => '8GB USB Drive',
            'total' => 12.50
        );
        $model = new Shp_Model( $data );
        $data = $model->get_data();
        $this->assertArrayHasKey( 'name', $data );
        $this->assertArrayHasKey( 'total', $data );
        $this->assertEquals( '8GB USB Drive', $data['name'] );
        $this->assertEquals( 12.50, $data['total'] );
    }

    /** @test */
    public function it_should_set_a_scalar_value_for_a_given_key_in_the_data_array() {
        $data = array (
            'name' => '',
            'total' => 0
        );
        $name = 'Example Product Name';
        $model = new Shp_Model( $data );

        $model->name = $name;

        $data = $model->get_data();
        $this->assertEquals( $name, $data['name'] );
    }

}
