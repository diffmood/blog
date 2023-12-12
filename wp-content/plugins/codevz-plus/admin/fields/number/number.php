<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Field: Number
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! class_exists( 'Codevz_Field_number' ) ) {
  class Codevz_Field_number extends Codevz_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '' ) {
      parent::__construct( $field, $value, $unique, $where );
    }

    public function output() {

      echo $this->element_before();
      $unit = ( isset( $this->field['unit'] ) ) ? '<em>'. $this->field['unit'] .'</em>' : '';
      echo '<input type="number" name="'. $this->element_name() .'" value="'. $this->element_value().'"'. $this->element_class() . $this->element_attributes() .'/>'. $unit;
      echo $this->element_after();

    }

  }
}
