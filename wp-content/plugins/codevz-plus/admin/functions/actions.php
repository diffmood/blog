<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Get icons from admin ajax
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'codevz_framework_get_icons' ) ) {
  function codevz_framework_get_icons() {

    $jsons = apply_filters( 'codevz/load/icons/json', glob( CODEVZ_FRAMEWORK_DIR . '/fields/icon/*.json' ) );

    if( ! empty( $jsons ) ) {

      foreach ( $jsons as $path ) {

        $object = codevz_get_icon_fonts( $path );

        if( is_object( $object ) ) {

          echo ( count( $jsons ) >= 2 ) ? '<h4 class="codevz-icon-title">'. $object->name .'</h4>' : '';

          foreach ( $object->icons as $icon ) {
            echo '<a class="codevz-icon-tooltip" data-codevz-icon="'. $icon .'" title="'. $icon .'"><span class="codevz-icon codevz-selector"><i class="'. $icon .'"></i></span></a>';
          }

        } else {
          echo '<h4 class="codevz-icon-title">'. esc_html__( 'Error! Can not load json file.', 'codevz' ) .'</h4>';
        }

      }

    }

    die();
  }
  add_action( 'wp_ajax_codevz-framework-get-icons', 'codevz_framework_get_icons' );
}

/**
 *
 * Export options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'codevz_framework_export_options' ) ) {
  function codevz_framework_export_options() {

    if( isset( $_GET['export'] ) && isset( $_GET['wpnonce'] ) && wp_verify_nonce( $_GET['wpnonce'], 'csf_backup' ) ) {

      header('Content-Type: plain/text');
      header('Content-disposition: attachment; filename=backup-options-'. gmdate( 'd-m-Y' ) .'.txt');
      header('Content-Transfer-Encoding: binary');
      header('Pragma: no-cache');
      header('Expires: 0');

      echo codevz_encode_string( get_option( $_GET['export'] ) );

    }

    die();
  }
  add_action( 'wp_ajax_codevz-framework-export-options', 'codevz_framework_export_options' );
}

/**
 *
 * Import options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'codevz_framework_import_options' ) ) {
  function codevz_framework_import_options() {

    if( isset( $_POST['unique'] ) && ! empty( $_POST['value'] ) && isset( $_POST['wpnonce'] ) && wp_verify_nonce( $_POST['wpnonce'], 'csf_backup' ) ) {
      update_option( $_POST['unique'], codevz_decode_string( $_POST['value'] ) );
    }

    die();
  }
  add_action( 'wp_ajax_codevz-framework-import-options', 'codevz_framework_import_options' );
}

/**
 *
 * Reset options
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'codevz_framework_reset_options' ) ) {
  function codevz_framework_reset_options() {

    if( isset( $_POST['unique'] ) && isset( $_POST['wpnonce'] ) && wp_verify_nonce( $_POST['wpnonce'], 'csf_backup' ) ) {

      delete_option( $_POST['unique'] );

      delete_option( 'codevz_primary_color' );
      delete_option( 'codevz_secondary_color' );

    }

    die();
  }
  add_action( 'wp_ajax_codevz-framework-reset-options', 'codevz_framework_reset_options' );
}

/**
 *
 * Set icons for wp dialog
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if( ! function_exists( 'codevz_framework_set_icons' ) ) {
  function codevz_framework_set_icons() {
    ?>
    <div id="codevz-modal-icon" class="codevz-modal codevz-modal-icon">
      <div class="codevz-modal-table">
        <div class="codevz-modal-table-cell">
          <div class="codevz-modal-overlay"></div>
          <div class="codevz-modal-inner">
            <div class="codevz-modal-title">
              <?php esc_html_e( 'Add Icon', 'codevz' ); ?>
              <div class="codevz-modal-close codevz-icon-close"></div>
            </div>
            <div class="codevz-modal-header codevz-text-center">
              <input type="text" placeholder="<?php esc_html_e( 'Search a Icon...', 'codevz' ); ?>" class="codevz-icon-search" />
            </div>
            <div class="codevz-modal-content"><div class="codevz-icon-loading"><?php esc_html_e( 'Loading...', 'codevz' ); ?></div></div>
          </div>
        </div>
      </div>
    </div>
    <?php
  }
  add_action( 'admin_footer', 'codevz_framework_set_icons' );
  add_action( 'customize_controls_print_footer_scripts', 'codevz_framework_set_icons' );
  // CODEVZ.
  add_action( 'elementor/editor/footer', 'codevz_framework_set_icons' );
}
