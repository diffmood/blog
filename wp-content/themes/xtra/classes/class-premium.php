<?php if ( ! defined( 'ABSPATH' ) ) {exit;} // Cannot access pages directly.

/**
 * Premium functions.
 * 
 * @since 4.4.5
 */

if ( ! class_exists( 'Codevz_Core_Premium' ) ) {

	class Codevz_Core_Premium extends Codevz_Core_Theme {

		// Class instance.
		private static $instance = null;

		public function __construct() {

			add_action( 'admin_init', [ $this, 'admin_init' ], 11 );
			add_action( 'after_setup_theme', [ $this, 'white_label_check' ] );
			add_action( 'customize_save_after', [ $this, 'white_label' ] );
			add_filter( 'pre_set_site_transient_update_themes', [ $this, 'update' ] );

		}

		// Instance.
		public static function instance() {

			if ( self::$instance === null ) {

				self::$instance = new self();

			}

			return self::$instance;
		}

		/**
		 * Redirect to dashboard after theme activated.
		 * 
		 * @return -
		 */
		public function admin_init() {

			// Current page.
			global $pagenow;

			// Redirect after theme activation.
			if ( isset( $_GET['activated'] ) && $pagenow == 'themes.php' ) {

				wp_redirect( esc_url_raw( admin_url( 'admin.php?page=theme-activation' ) ) );

			}

		}

		/**
		 * Check white labeled themes after update.
		 * 
		 * @return -
		 */
		public function white_label_check() {

			// White label after update.
			$white_label = self::option( 'white_label_theme_name' );

			if ( $white_label ) {

				$theme = wp_get_theme();

				if ( empty( $theme->parent() ) && $white_label !== $theme->get( 'Name' ) ) {

					self::white_label();

				}

			}

		}

		/**
		 * Theme automatic update
		 * 
		 * @since 2.7.0
		 */
		public static function update( $transient ) {

			// Original theme slug from config.
			$theme_slug = sanitize_title_with_dashes( apply_filters( 'codevz_config_name', false ) );

			// Get new versions
			$versions = get_transient( 'codevz_versions' );

			if ( empty( $versions ) ) {

				$request = wp_remote_get( self::$api . 'versions.json' );

				if ( ! is_wp_error( $request ) ) {
					$body = wp_remote_retrieve_body( $request );
					$versions = json_decode( $body, true );
					set_transient( 'codevz_versions', $versions, 60 );
				}

			}

			// There is no new update or its child theme, so skip!
			if ( ! isset( $versions['themes'][ $theme_slug ] ) ) {
				return $transient;
			}

			// Current theme
			$theme = wp_get_theme();

			// Slug and version.
			if ( ! empty( $theme->parent() ) ) {

				$current_theme = sanitize_title_with_dashes( $theme->get( 'Template' ) );

				$old_version = $theme->parent()->Version;

			} else {

				$current_theme = sanitize_title_with_dashes( $theme->get( 'Name' ) );

				$old_version = $theme->get( 'Version' );

			}

			$new_version = $versions['themes'][ $theme_slug ]['version'];

			// Compate versions and inform WordPress about new update
			if ( $old_version != $new_version && version_compare( $old_version, $new_version, '<' ) ) {

				if ( $theme_slug === 'xtra' ) {
					$theme_zip = self::$api . $theme_slug . '.zip';
				} else {
					$theme_zip = self::$api . 'themes/' . $theme_slug . '.zip';
				}

				$transient->response[ $current_theme ] = [
					'theme' 		=> $current_theme,
					'new_version' 	=> $versions['themes'][ $theme_slug ]['version'],
					'url' 			=> str_replace( 'api/', '', self::$api ),
					'package' 		=> $theme_zip
				];

			} else if ( isset( $transient->response[ $current_theme ] ) ) {

				unset( $transient->response[ $current_theme ] );

			}

			return $transient;

		}

		/**
		 * Theme white label
		 * 
		 * @since 3.2.0
		 */
		public static function white_label() {

			if ( ! self::$plugin ) {
				return;
			}

			$dir 			= trailingslashit( get_template_directory() );
			$basename 		= basename( $dir );

			$name 			= self::option( 'white_label_theme_name' );
			$desc 			= self::option( 'white_label_theme_description' );
			$link 			= self::option( 'white_label_link', 'https://codevz.com/' );
			$author 		= self::option( 'white_label_author', 'Codevz' );
			$author_link 	= $link;
			$slug 			= sanitize_title_with_dashes( $name );
			$screenshot 	= self::option( 'white_label_theme_screenshot', self::$url . 'assets/img/screenshot.png' );

			$is_child_theme = is_child_theme();

			if ( empty( $name ) ) {
				return;
			}

			// WP_Filesystem.
			$wpfs = Codevz_Plus::wpfs();

			// Get theme version.
			$theme = wp_get_theme();
			$ver = empty( $theme->parent() ) ? $theme->get( 'Version' ) : $theme->parent()->Version;

			$information = '/*
		Theme Name:   ' . $name . '
		Theme URI:    ' . $link . '
		Description:  ' . $desc . '
		Version:      ' . $ver . '
		Author:       ' . $author . '
		Author URI:   ' . $author_link . '
		License:      GPLv2
		License URI:  http://gnu.org/licenses/gpl-2.0.html
		Tags:         one-column, two-columns, right-sidebar, custom-menu, rtl-language-support, sticky-post, translation-ready
	*/

	/*
		PLEASE DO NOT edit this file, if you want add custom CSS go to Theme Options > Additional CSS
	*/';

			// Save style.css
			$result = $wpfs->put_contents( $dir . 'style.css', $information, FS_CHMOD_FILE );

			// Replace image.
			$new_image = $wpfs->get_contents( $screenshot );
			$result = $wpfs->put_contents( $dir . 'screenshot.png', $new_image, FS_CHMOD_FILE );
			$result = $wpfs->put_contents( str_replace( '/' . $basename . '/', '/' . $slug . '-child/screenshot.png', $dir ), $new_image, FS_CHMOD_FILE );

			// Rename folder name.
			$new_name = str_replace( '/' . $basename . '/', '/' . $slug . '/', $dir );
			rename( $dir, $new_name );

			// Check child theme.
			if ( $is_child_theme ) {

				// Child theme.
				$child = '/*
			Theme Name:	' . $name . ' Child
			Theme URI:	' . $link . '
			Description:' . $desc . '
			Author:		' . $author . '
			Author URI:	' . $author_link . '
			Template:	' . strtolower( $name ) . '
			Version:	1.0
		*/

		/*
			PLEASE DO NOT edit this file, if you want add custom CSS go to Theme Options > Additional CSS
		*/';

				$new_name = str_replace( '/' . $basename . '/', '/' . $slug . '-child/', $dir );
				$child_dir = str_replace( '/' . $basename . '/', '/' . $basename . '-child/', $dir );
				rename( $child_dir, $new_name );

				$result = $wpfs->put_contents( str_replace( '/' . $basename . '/', '/' . $slug . '-child/style.css', $dir ), $child, FS_CHMOD_FILE );

				// Activate child theme.
				switch_theme( $slug . '-child' );

			} else {

				// Theme activate.
				switch_theme( $slug );
			}

		}

	}

	Codevz_Core_Premium::instance();

}
