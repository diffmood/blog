<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Metabox Class
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! class_exists( 'Codevz_Framework_Metabox' ) ) {
  class Codevz_Framework_Metabox extends Codevz_Framework_Abstract {

    /**
     *
     * options
     * @access public
     * @var array
     *
     */
    public $options = array();

    // run metabox construct
    public function __construct( $options ) {

      $this->options  = apply_filters( 'codevz/options/metabox', $options );

      $this->addAction( 'add_meta_boxes', 'add_meta_box' );
      $this->addAction( 'save_post', 'save_meta_box', 999, 2 );

      $this->addEnqueue( $this->options );

    }

    // instance
    public static function instance( $options = array() ) {
      return new self( $options );
    }

    // add metabox
    public function add_meta_box( $post_type ) {

      foreach ( $this->options as $value ) {
        add_meta_box( $value['id'], $value['title'], array( &$this, 'add_meta_box_content' ), $value['post_type'], $value['context'], $value['priority'], $value );
      }

    }

    // add metabox content
    public function add_meta_box_content( $post, $callback ) {

      global $post, $codevz_framework, $typenow;

      wp_nonce_field( 'codevz-metabox', 'codevz-metabox-nonce' );

      $args       = $callback['args'];
      $unique     = $args['id'];
      $sections   = $args['sections'];
      $meta_value = get_post_meta( $post->ID, $unique, true );
      $has_nav    = ( count( $sections ) >= 2 && $args['context'] != 'side' ) ? true : false;
      $show_all   = ( ! $has_nav ) ? ' codevz-show-all' : '';
      $timenow    = round( microtime(true) );
      $errors     = ( isset( $meta_value['_transient']['errors'] ) ) ? $meta_value['_transient']['errors'] : array();
      $section    = ( isset( $meta_value['_transient']['section'] ) ) ? $meta_value['_transient']['section'] : false;
      $expires    = ( isset( $meta_value['_transient']['expires'] ) ) ? $meta_value['_transient']['expires'] : 0;
      $timein     = codevz_timeout( $timenow, $expires, 20 );
      $section_id = ( $timein && $section ) ? $section : '';
      $section_id = codevz_get_var( 'codevz-section', $section_id );

      // add erros
      $codevz_framework['errors'] = ( $timein ) ? $errors : array();

      do_action( 'codevz/html/metabox/before' );

      echo '<div class="csf codevz-metabox">';

        echo '<input type="hidden" name="'. $unique .'[_transient][section]" class="codevz-section-id" value="'. $section_id .'">';

        echo '<div class="codevz-wrapper'. $show_all .'">';

          if( $has_nav ) {

            echo '<div class="codevz-nav">';

              echo '<ul>';
              $num = 0;
              foreach( $sections as $value ) {

                if( ! empty( $value['typenow'] ) && $value['typenow'] !== $typenow ) { continue; }

                $tab_icon = ( ! empty( $value['icon'] ) ) ? '<i class="codevz-icon '. $value['icon'] .'"></i>' : '';

                if( isset( $value['fields'] ) ) {
                  $active_section = ( ( empty( $section_id ) && $num === 0 ) || $section_id == $unique .'_'. $value['name'] ) ? ' class="codevz-section-active"' : '';
                  echo '<li><a href="#"'. $active_section .' data-section="'. $unique .'_'. $value['name'] .'">'. $tab_icon . $value['title'] .'</a></li>';
                } else {
                  echo '<li><div class="codevz-seperator">'. $tab_icon . ( isset( $value['title'] ) ? $value['title'] : '' ) .'</div></li>';
                }

                $num++;
              }
              echo '</ul>';

            echo '</div>';

          }

          echo '<div class="codevz-content">';

            echo '<div class="codevz-sections">';
            $num = 0;
            foreach( $sections as $v ) {

              if( ! empty( $v['typenow'] ) && $v['typenow'] !== $typenow ) { continue; }

              if( isset( $v['fields'] ) ) {

                $active_content = ( ( empty( $section_id ) && $num === 0 ) || $section_id === $unique .'_'. $v['name'] ) ? 'codevz-onload' : 'hidden';

                echo '<div id="codevz-tab-'. $unique .'_'. $v['name'] .'" class="codevz-section '. $active_content .'">';
                echo ( isset( $v['title'] ) ) ? '<div class="codevz-section-title"><h3>'. $v['title'] .'</h3></div>' : '';

                foreach ( $v['fields'] as $field_key => $field ) {

                  $default    = ( isset( $field['default'] ) ) ? $field['default'] : '';
                  $elem_id    = ( isset( $field['id'] ) ) ? $field['id'] : '';
                  $elem_value = ( is_array( $meta_value ) && isset( $meta_value[$elem_id] ) ) ? $meta_value[$elem_id] : $default;
                  echo codevz_add_field( $field, $elem_value, $unique, 'metabox' );

                }
                echo '</div>';

              }

              $num++;
            }
            echo '</div>';

            echo '<div class="clear"></div>';

            if( ! empty( $args['show_restore'] ) ) {

              echo '<div class=" codevz-metabox-restore">';
              echo '<label>';
              echo '<input type="checkbox" name="'. $unique .'[_restore]" />';
              echo '<span class="button codevz-button-restore">'. esc_html__( 'Restore', 'codevz' ) .'</span>';
              echo '<span class="button codevz-button-cancel">'. sprintf( '<small>( %s )</small> %s', esc_html__( 'update post for restore ', 'codevz' ), esc_html__( 'Cancel', 'codevz' ) ) .'</span>';
              echo '</label>';
              echo '</div>';

            }

          echo '</div>';

          if ( $has_nav ) {
            echo '<div class="codevz-nav-background"></div>';
          }

          echo '<div class="clear"></div>';

        echo '</div>';

      echo '</div>';

      do_action( 'codevz/html/metabox/after' );

    }

    // save metabox
    public function save_meta_box( $post_id, $post ) {

      if ( wp_verify_nonce( codevz_get_var( 'codevz-metabox-nonce' ), 'codevz-metabox' ) ) {

        $errors = array();
        $post_type = codevz_get_var( 'post_type' );

        foreach ( $this->options as $request_value ) {

          if( in_array( $post_type, (array) $request_value['post_type'] ) ) {

            $request_key = $request_value['id'];
            $request = codevz_get_var( $request_key, array() );

            // ignore _nonce
            if( isset( $request['_nonce'] ) ) {
              unset( $request['_nonce'] );
            }

            // sanitize and validate
            foreach( $request_value['sections'] as $key => $section ) {

              if( ! empty( $section['fields'] ) ) {

                foreach( $section['fields'] as $field ) {

                  if( ! empty( $field['id'] ) ) {

                    // sanitize
                    if( ! empty( $field['sanitize'] ) ) {

                      $sanitize = $field['sanitize'];

                      if( function_exists( $sanitize ) ) {

                        $value_sanitize = codevz_get_vars( $request_key, $field['id'] );
                        $request[$field['id']] = call_user_func( $sanitize, $value_sanitize );

                      }

                    }

                    // validate
                    if( ! empty( $field['validate'] ) ) {

                      $validate = $field['validate'];

                      if( function_exists( $validate ) ) {

                        $value_validate = codevz_get_vars( $request_key, $field['id'] );
                        $has_validated  = call_user_func( $validate, array( 'value' => $value_validate, 'field' => $field ) );

                        if( ! empty( $has_validated ) ) {

                          $meta_value = get_post_meta( $post_id, $request_key, true );

                          $errors[$field['id']] = array( 'code' => $field['id'], 'message' => $has_validated, 'type' => 'error' );
                          $default_value = isset( $field['default'] ) ? $field['default'] : '';
                          $request[$field['id']] = ( isset( $meta_value[$field['id']] ) ) ? $meta_value[$field['id']] : $default_value;

                        }

                      }

                    }

                    // auto sanitize
                    if( ! isset( $request[$field['id']] ) || is_null( $request[$field['id']] ) ) {
                      $request[$field['id']] = '';
                    }

                  }

                }

              }

            }

            $request['_transient']['expires']  = round( microtime(true) );

            if( ! empty( $errors ) ) {
              $request['_transient']['errors'] = $errors;
            }

            $request = apply_filters( 'codevz/save/metabox', $request, $request_key, $post );

            if( empty( $request ) || ! empty( $request['_restore'] ) ) {

              delete_post_meta( $post_id, $request_key );

            } else {

              update_post_meta( $post_id, $request_key, $request );

            }

          }

        }

      }

    }

  }
}
