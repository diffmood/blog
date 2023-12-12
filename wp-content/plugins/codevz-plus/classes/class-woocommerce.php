<?php if ( ! defined( 'ABSPATH' ) ) {exit;} // Exit if accessed directly.

/**
 * Woocommerce compatibility.
 */

class Xtra_Woocommerce {

	public function __construct() {

		add_action( 'init', [ $this, 'init' ], 11 );

	}

	/**
	 * Init WooCommerce actions and filters.
	 * 
	 * @return string
	 */
	public function init() {

		// Products brands taxonomy.
		register_taxonomy( 'codevz_brands', 'product', [
			'labels' => [
				'name' 			=> esc_html__( 'Brands', 'codevz' ),
				'singular_name' => esc_html__( 'Brand', 'codevz' )
			],
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => true,
			'show_tagcloud'              => true,
			'rewrite'					 => [ 'slug' => 'product-brand', 'with_front' => false ]
		] );

		// Enqueue fragments JS.
		add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 11 ); 

		// Number of products per page & columns.
		if ( ! class_exists( 'Woocommerce_Products_Per_Page' ) ) {

			add_filter( 'loop_shop_columns', [ $this, 'columns' ], 11 );
			add_filter( 'loop_shop_per_page', [ $this, 'loop_shop_per_page' ], 101, 1 );
			add_filter( 'woocommerce_product_query', [ $this, 'products_per_page' ], 11 );

			// Number of products browser request.
			if ( isset( $_REQUEST['ppp'] ) ) {
				wc_setcookie( 'woocommerce_products_per_page', intval( $_REQUEST['ppp'] ), time() + DAY_IN_SECONDS * 2, apply_filters( 'wc_session_use_secure_cookie', false ) );
			}

			// Show products per page dropdown.
			add_action( 'woocommerce_before_shop_loop', [ $this, 'products_per_page_dropdown' ], 99 );

		}

		// AJAX mini cart content.
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'cart' ], 11, 1 );

		// Number of  related products per page.
		add_filter( 'woocommerce_upsell_display_args', [ $this, 'related_products' ], 11 );
		add_filter( 'woocommerce_output_related_products_args', [ $this, 'related_products' ], 11 );

		// Customize products HTML and add quickview and wihlist.
		add_filter( 'woocommerce_post_class', [ $this, 'product_classes' ] );
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'single_icons' ], 20 );
		add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'woocommerce_before_shop_loop_item_title_low' ], 9 );
		add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'woocommerce_before_shop_loop_item_title_high' ], 11 );

		// Single Wrap.
		add_action( 'woocommerce_before_single_product_summary', [ $this, 'before_single' ], 11 );
		add_action( 'woocommerce_after_single_product_summary', [ $this, 'after_single' ], 1 );

		// Cart item removal AJAX.
		add_action( 'wp_ajax_xtra_remove_item_from_cart', [ $this, 'remove_item_from_cart' ] );
		add_action( 'wp_ajax_nopriv_xtra_remove_item_from_cart', [ $this, 'remove_item_from_cart' ] );

		// Quickview AJAX function.
		add_action( 'wp_ajax_xtra_quick_view', [ $this, 'quickview' ] );
		add_action( 'wp_ajax_nopriv_xtra_quick_view', [ $this, 'quickview' ] );

		// Get wishlist & compare page content via AJAX.
		add_action( 'wp_ajax_xtra_wishlist_content', [ $this, 'wishlist_content' ] );
		add_action( 'wp_ajax_nopriv_xtra_wishlist_content', [ $this, 'wishlist_content' ] );
		add_action( 'wp_ajax_xtra_compare_content', [ $this, 'compare_content' ] );
		add_action( 'wp_ajax_nopriv_xtra_compare_content', [ $this, 'compare_content' ] );

		// Wishlist shortcode.
		add_shortcode( 'cz_wishlist', [ $this, 'wishlist_shortcode' ] );

		// Compare shortcode.
		add_shortcode( 'cz_compare', [ $this, 'compare_shortcode' ] );

		// Quickview popup content.
		add_filter( 'woocommerce_product_loop_end', [ $this, 'quickview_popup' ] );

		// Modify checkout page.
		add_action( 'woocommerce_checkout_after_customer_details', [ $this, 'checkout_before' ] );
		add_action( 'woocommerce_checkout_after_order_review', [ $this, 'after_single' ] );

		// Add back to store button on WooCommerce cart page.
		add_action( 'woocommerce_cart_actions', [ $this, 'continue_shopping' ] );

		// Modify products query.
		add_action( 'woocommerce_product_query', [ $this, 'products_query' ], 10, 2 );

		// Out of stock badge.
		add_action( 'woocommerce_after_shop_loop_item_title', [ $this, 'out_of_stock' ] );

		// Remove product description h2 tab
		add_filter( 'woocommerce_product_description_heading', '__return_null' );

	}

	public function wp_enqueue_scripts() {

		wp_enqueue_script( 'wc-cart-fragments' );

	}

	/**
	 * Get WooCommerce cart in header.
	 * 
	 * @return string
	 */
	public function cart( $fragments ) {

		$wc = WC();
		$count = $wc->cart->cart_contents_count;
		$total = $wc->cart->get_cart_total();

		ob_start(); ?>
			<div class="cz_cart">
				<?php if ( $count > 0 || Codevz_Plus::option( 'woo_show_zero_count' ) ) { ?>
				<span class="cz_cart_count"><?php echo esc_html( $count ); ?></span>
				<?php } ?>
				<div class="cz_cart_items"><div>
			        <?php if ( $wc->cart->cart_contents_count == 0 ) { ?>
				    	<div class="cart_list">
				    		<div class="item_small xtra-empty-cart"><?php echo esc_html( Codevz_Plus::option( 'woo_no_products', 'No products in the cart' ) ); ?></div>
				    	</div>
				    <?php $fragments['.cz_cart'] = ob_get_clean(); return $fragments; } else { ?>
			        	<div class="cart_list">

			        		<div class="item_small xtra-empty-cart hidden"><?php echo esc_html( Codevz_Plus::option( 'woo_no_products', 'No products in the cart' ) ); ?></div>
			        		
			        		<?php foreach( $wc->cart->cart_contents as $cart_item_key => $cart_item ) {
			        			$id = $cart_item['product_id'];
			        			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			        		?>
					            <div class="item_small">
					                <a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
					                	<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'codevz_600_600' ), $cart_item, $cart_item_key ) ); ?>
					                </a>
					                <div class="cart_list_product_title cz_tooltip_up">
					                    <h3><a href="<?php echo esc_url( get_permalink( $id ) ); ?>"><?php echo wp_kses_post( get_the_title( $id ) ); ?></a></h3>
					                    <div class="cart_list_product_quantity"><?php echo wp_kses_post( $cart_item['quantity'] ); ?> x <?php echo wp_kses_post( $wc->cart->get_product_subtotal( $cart_item['data'], 1 ) ); ?> </div>
					                    <a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="remove" data-product_id="<?php echo esc_attr( $id ); ?>" data-title="<?php echo esc_html__( 'Remove', 'codevz' ); ?>"><i class="fa czico-198-cancel"></i></a>
					                </div>
					            </div>
			        		<?php } ?>
			        	</div>
				        
				        <div class="cz_cart_buttons clr">
							<a href="<?php echo esc_url( get_permalink(get_option('woocommerce_cart_page_id')) ); ?>"><?php echo esc_html( do_shortcode( Codevz_Plus::option( 'woo_cart', 'Cart' ) ) ); ?> <span><?php echo wp_kses_post( $wc->cart->get_cart_total() ); ?></span></a>
							<a href="<?php echo esc_url( get_permalink(get_option('woocommerce_checkout_page_id')) ); ?>"><?php echo esc_html( do_shortcode( Codevz_Plus::option( 'woo_checkout', 'Checkout' ) ) ); ?></a>
				        </div>
			        <?php } ?>
				</div></div>
			</div>
		<?php 

		$fragments['.cz_cart'] = ob_get_clean();

		return $fragments;
	}

	/**
	 * WooCommerce products columns
	 * 
	 * @return string
	 */
	public function columns() {

		return Codevz_Plus::option( 'woo_col', 4 );

	}

	/**
	 * WooCommerce products per page
	 * 
	 * @return object
	 */
	public function products_per_page( $query ) {

		$query->set( 'posts_per_page', $this->loop_shop_per_page() );

	}

	/**
	 * WooCommerce products per page
	 * 
	 * @return int
	 */
	public function loop_shop_per_page( $per_page = '' ) {

		$to = Codevz_Plus::option( 'woo_items_per_page', $per_page );

		if ( isset( $_REQUEST['ppp'] ) ) {

			$per_page = $_REQUEST['ppp'];

		} else if ( isset( $_COOKIE['woocommerce_products_per_page'] ) ) {

			$per_page = $_COOKIE['woocommerce_products_per_page'];

		} else {

			$per_page = $to;

		}

		if ( $per_page == 0 ) {
			$per_page = $to;
		}

		return intval( $per_page );

	}

	/**
	 * WooCommerce show products per page dropdown.
	 * 
	 * @return string
	 */
	public function products_per_page_dropdown() {

		if ( ! Codevz_Plus::option( 'woo_ppp_dropdown' ) ) {
			return;
		}

		global $wp_query;

		// Set the products per page options (e.g. 4, 8, 12)
		if ( Codevz_Plus::option( 'woo_col', 4 ) % 2 == 0 ) {
			$numbers = [ 4, 8, 16, 24, 32 ];
		} else {
			$numbers = [ 6, 9, 15, 21, 27 ];
		}

		// Get action URL.
		$cat = $wp_query->get_queried_object();

		if ( isset( $cat->term_id ) && isset( $cat->taxonomy ) ) {
			$action = get_term_link( $cat->term_id, $cat->taxonomy );
		} else {
			$action = get_the_permalink( get_option( 'woocommerce_shop_page_id' ) );
		}

		// Set action url if option behaviour is true
		// Paste QUERY string after for filter and orderby support
		$query_string = ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . add_query_arg( array( 'ppp' => false ), $_SERVER['QUERY_STRING'] ) : null;
		$action = $action . $query_string;

		// Only show on product categories
		if ( ! woocommerce_products_will_display() ) :
			return;
		endif;

		$ppp = $this->loop_shop_per_page( 6 );

		?><form method="post" action="<?php echo esc_url( $action ); ?>" class="codevz-products-per-page"><?php

			?><select name="ppp" onchange="this.form.submit()">

				<option value="0" <?php echo in_array( $ppp, $numbers ) ? 'selected="selected"' : ''; ?>><?php echo esc_html__( 'Products per page', 'codevz' ); ?></option>

				<?php

				foreach( $numbers as $key => $value ) :

					?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $ppp ); ?>><?php
						esc_html( printf( esc_html__( '%s products per page', 'codevz' ), $value ) );
					?></option><?php

				endforeach;

			?></select><?php

			// Keep query string vars intact
			foreach ( $_GET as $key => $val ) :

				if ( 'ppp' === $key || 'submit' === $key ) :
					continue;
				endif;
				if ( is_array( $val ) ) :
					foreach( $val as $inner_val ) :
						?><input type="hidden" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $inner_val ); ?>" /><?php
					endforeach;
				else :
					?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $val ); ?>" /><?php
				endif;
			endforeach;

		?></form><?php

	}

	/**
	 * WooCommerce products per page
	 * 
	 * @return array
	 */
	public function related_products( $args ) {

		$columns = (int) Codevz_Plus::option( 'woo_related_col' );

		$args['columns'] 		= $columns;
		$args['posts_per_page'] = $columns;

		return $args;

	}

	/**
	 * Wishlist container shortcode.
	 * 
	 * @return string
	 */
	public function wishlist_shortcode( $a, $c = '' ) {
		return '<div class="woocommerce xtra-wishlist xtra-icon-loading" data-empty="' . esc_html__( 'Your wishlist list is empty.', 'codevz' ) . '" data-nonce="' . wp_create_nonce( 'xtra_wishlist_content' ) . '"></div>';
	}

	/**
	 * Compare container shortcode.
	 * 
	 * @return string
	 */
	public function compare_shortcode( $a, $c = '' ) {
		return '<div class="woocommerce xtra-compare xtra-icon-loading" data-empty="' . esc_html__( 'Your products compare list is empty.', 'codevz' ) . '" data-nonce="' . wp_create_nonce( 'xtra_compare_content' ) . '"></div>';
	}

	/**
	 * Get wishlist products via AJAX.
	 * 
	 * @return string
	 */
	public function wishlist_content() {

		if ( empty( $_POST['ids'] ) && empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'xtra_wishlist_content' ) ) {
			wp_die( '<b>' . esc_html__( 'Server error, Please reload page ...', 'codevz' ) . '</b>' );
		}

		if ( isset( $_POST['check'] ) ) {

			$new = '';

			$ids = explode( ',', $_POST['ids'] );

			foreach( $ids as $id ) {

				if ( $id && $id !== 'undefined' ) {

					$id = str_replace( ' ', '', $id );

					$post = get_post( $id );

					if ( ! empty( $post->post_title ) ) {

						$new .= $id . ',';

					}

				}

			}

			wp_die( esc_html( $new ) );

		}

		wp_die( do_shortcode( '[products ids="' . esc_html( $_POST['ids'] ) . '" columns="3"]' ) );

	}

	/**
	 * Get compare products via AJAX.
	 * 
	 * @return string
	 */
	public function compare_content() {

		if ( empty( $_POST['ids'] ) && empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'xtra_compare_content' ) ) {
			wp_die( '<b>' . esc_html__( 'Server error, Please reload page ...', 'codevz' ) . '</b>' );
		}

		$out = '';

		if ( isset( $_POST['check'] ) ) {

			$ids = explode( ',', $_POST['ids'] );

			foreach( $ids as $id ) {

				if ( $id && $id !== 'undefined' ) {

					$id = str_replace( ' ', '', $id );

					$post = get_post( $id );

					if ( ! empty( $post->post_title ) ) {

						$out .= $id . ',';

					}

				}

			}

			wp_die( esc_html( $out ) );

		} else {

			$ids = explode( ',', $_POST['ids'] );

			$out .= '<table class="cz-compare"><tbody>';

			$tr = [

				'general' 			=> [ 'td' => '', 'title' => '' ],
				'price' 			=> [ 'td' => '', 'title' => esc_html__( 'Price', 'codevz' ) ],
				'brand' 			=> [ 'td' => '', 'title' => esc_html__( 'Brand', 'codevz' ) ],
				'desc' 				=> [ 'td' => '', 'title' => esc_html__( 'Description', 'codevz' ) ],
				'sku' 				=> [ 'td' => '', 'title' => esc_html__( 'Product SKU', 'codevz' ) ],
				'availablity' 		=> [ 'td' => '', 'title' => esc_html__( 'Availablity', 'codevz' ) ],
				'sold_individually' => [ 'td' => '', 'title' => esc_html__( 'Individual sale', 'codevz' ) ],
				'tax_status' 		=> [ 'td' => '', 'title' => esc_html__( 'Tax status', 'codevz' ) ],
				'weight' 			=> [ 'td' => '', 'title' => esc_html__( 'Weight', 'codevz' ) ],
				'length' 			=> [ 'td' => '', 'title' => esc_html__( 'Length', 'codevz' ) ],
				'height' 			=> [ 'td' => '', 'title' => esc_html__( 'Height', 'codevz' ) ],
				'width' 			=> [ 'td' => '', 'title' => esc_html__( 'Width', 'codevz' ) ],
				'average_rating' 	=> [ 'td' => '', 'title' => esc_html__( 'Average rating', 'codevz' ) ],
				'review_count' 		=> [ 'td' => '', 'title' => esc_html__( 'Review count', 'codevz' ) ],

			];

			foreach( $ids as $id ) {

				if ( $id && $id !== 'undefined' ) {

					$id = str_replace( ' ', '', $id );

					$product = wc_get_product( $id );

					$tr[ 'general' ][ 'td' ] .= '<td><a href="' . get_permalink( $product->get_id() ) . '">' . $product->get_image() . '<h4 data-id="' . $id . '">' . get_the_title( $id ) . '</h4></a>' . do_shortcode( '[add_to_cart id=' . $product->get_id() . ' show_price="false"]' ) . '</td>';

					$cx = get_woocommerce_currency_symbol();

					$price = $product->get_regular_price();
					$sale = $product->get_sale_price();

					$is_equal = $price == $sale;

					if ( ! $price && ( $product->is_type( 'variable' ) || $product->is_type( 'variation' ) ) ) {

						$price = $cx . ( (int) $product->get_variation_regular_price() );
						$price .= $is_equal ? '' : ' - ' . $cx . ( (int) $product->get_variation_regular_price( 'max' ) );

						$cx = '';

					}

					$tr[ 'price' ][ 'td' ] .= '<td>' . ( $sale ? '<del><span>' . $cx . '</span>' . $price . '</del> ' . '<span>' . $cx . '</span>' . $sale : '<span>' . $cx . '</span>' . $price ) . '</td>';

					$brands  = (array) get_the_terms( $product->get_id(), 'codevz_brands', true );

					if ( ! empty( $brands[ 0 ]->term_id ) ) {

						$term_meta = get_term_meta( $brands[ 0 ]->term_id, 'codevz_brands', true );

						$tr[ 'brand' ][ 'td' ] .= '<td>';

						$tr[ 'brand' ][ 'td' ] .= empty( $term_meta[ 'brand_logo' ] ) ? '' : '<a href="' . get_term_link( $brands[ 0 ]->term_id ) . '">' . wp_get_attachment_image( $term_meta[ 'brand_logo' ], 'full' ) . '</a>';

						$tr[ 'brand' ][ 'td' ] .= '</td>';

					}

					$tr[ 'desc' ][ 'td' ] .= '<td>' . ( $product->get_short_description() ? $product->get_short_description() : '<i class="fa fa-times"></i>' ) . '</td>';

					$tr[ 'sku' ][ 'td' ] .= '<td>' . ( $product->get_sku() ? $product->get_sku() : '<i class="fa fa-times"></i>' ) . '</td>';

					$tr[ 'availablity' ][ 'td' ] .= '<td>' . ( ( $product->get_stock_quantity() || ! $product->get_manage_stock() ) ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>' ) . $product->get_stock_quantity() . ' ' . ucwords( $product->get_stock_status() ) . '</td>';

					$tr[ 'sold_individually' ][ 'td' ] .= '<td>' . ( $product->get_sold_individually() ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>' ) . '</td>';

					$tr[ 'tax_status' ][ 'td' ] .= '<td>' . ( $product->get_tax_status() ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>' ) . ucwords( $product->get_tax_status() ) . '</td>';

					if ( $product->get_attributes() ) {

						foreach ( $product->get_attributes() as $attr ) {

							$name = $attr->get_name();
							$options = $attr->get_options();

							$tr[ $name ][ 'title' ] = ucwords( $name );

							$tr[ $name ][ 'td' ] = '';

							$tr[ $name ][ 'td' ] .= '<td>';

							foreach ( $options as $key => $val ) {
								$tr[ $name ][ 'td' ] .= $key ? ', ' . $val : $val;
							}

							$tr[ $name ][ 'td' ] .= '</td>';

						}

					}

					if ( $product->get_weight() ) {
						$tr[ 'weight' ][ 'td' ] .= '<td>' . $product->get_weight() . ' ' . get_option( 'woocommerce_weight_unit' ) . '</td>';
					}

					if ( $product->get_length() ) {
						$tr[ 'length' ][ 'td' ] .= '<td>' . $product->get_length() . ' ' . get_option( 'woocommerce_dimension_unit' ) . '</td>';
					}

					if ( $product->get_height() ) {
						$tr[ 'height' ][ 'td' ] .= '<td>' . $product->get_height() . ' ' . get_option( 'woocommerce_dimension_unit' ) . '</td>';
					}

					if ( $product->get_width() ) {
						$tr[ 'width' ][ 'td' ] .= '<td>' . $product->get_width() . ' ' . get_option( 'woocommerce_dimension_unit' ) . '</td>';
					}

					$tr[ 'average_rating' ][ 'td' ] .= '<td>' . ( $product->get_average_rating() ? '<i class="fa fa-star"></i>' . $product->get_average_rating() : '<i class="fa fa-times"></i>' ) . '</td>';

					$tr[ 'review_count' ][ 'td' ] .= '<td>' . ( $product->get_review_count() ? $product->get_review_count() : '<i class="fa fa-times"></i>' ) . '</td>';

				}

			}

			foreach( $tr as $class => $inner ) {

				if ( empty( $inner['td'] ) ) {
					continue;
				}

				$out .= '<tr class="cz-compare-tr-' . esc_attr( $class ) . '">';

				if ( $class === 'general' ) {
					$inner['title'] = '';
				}

				$out .= '<th>' . esc_html( $inner['title'] ) . '</th>';

				$out .= empty( $inner['td'] ) ? '' : $inner['td'];

				$out .= '</tr>';

			}

			$out .= '</tbody></table><ul class="hide"><li></li></ul>';

			wp_die( $out );

		}

	}

	/**
	 * Add wishlist icon into single product page.
	 * 
	 * @return string
	 */
	public function single_icons() {

		$product_id  = get_the_id();

		if ( Codevz_Plus::option( 'woo_wishlist' ) ) {

			echo '<div class="xtra-product-icons xtra-product-icons-wishlist cz_tooltip_up" data-id="' . $product_id . '">';
			echo '<i class="fa fa-heart-o xtra-add-to-wishlist" data-title="' . esc_html__( 'Add to wishlist', 'codevz' ) . '"></i>';
			echo '</div>';

		}

		if ( Codevz_Plus::option( 'woo_compare' ) ) {

			echo '<div class="xtra-product-icons xtra-product-icons-compare cz_tooltip_up" data-id="' . $product_id . '">';
			echo '<i class="fa fa-shuffle xtra-add-to-compare" data-title="' . esc_html__( 'Add for compare', 'codevz' ) . '"></i>';
			echo '</div>';

		}

		$brands  = (array) get_the_terms( $product_id, 'codevz_brands', true );

		if ( ! empty( $brands[ 0 ]->term_id ) ) {

			$term_meta = get_term_meta( $brands[ 0 ]->term_id, 'codevz_brands', true );

			echo '<div class="codevz-product-brands">';

			echo empty( $term_meta[ 'brand_logo' ] ) ? '' : '<a href="' . get_term_link( $brands[ 0 ]->term_id ) . '">' . wp_get_attachment_image( $term_meta[ 'brand_logo' ], 'full' ) . '</a>';

			echo '</div>';

		}

		echo '<div class="clr"></div>';

	}

	/**
	 * AJAX remove product from header cart.
	 * 
	 * @return string
	 */
	public function remove_item_from_cart() {

		$wc = WC();
		$cart = $wc->instance()->cart;
		$cart_id = $cart->generate_cart_id( $_POST['id'] );
		$cart_item_id = $cart->find_product_in_cart( $cart_id );

		if ( $cart_item_id ) {
			$cart->set_quantity( $cart_item_id, 0 );
		}

		// Return cart content
		wp_die( WC_AJAX::get_refreshed_fragments() );
	}

	/**
	 * Add extra custom classes to products.
	 * 
	 * @return array
	 */
	public function product_classes( $classes ) {

		// Check array.
		if ( ! is_array( $classes ) ) {
			return $classes;
		}

		// Product ID.
		$id = get_the_id();

		// Current query.
		global $wp_query;

		// Check single product class name.
		if ( is_single() && $wp_query->post->ID === $id ) {
			return $classes;
		}

		// Hover effect name.
		$hover = Codevz_Plus::option( 'woo_hover_effect' );

		if ( $hover ) {

			$product = new WC_Product( $id );
			$attachment_ids = $product->get_gallery_image_ids();

			// Check gallery first image.
			if ( is_array( $attachment_ids ) && isset( $attachment_ids[0] ) ) {

				$classes[] = 'cz_image';
				$classes[] = 'cz_image_' . esc_attr( $hover );

			}
		}

		return $classes;
	}

	public function quickview() {
		if ( ! isset( $_POST['id'] ) && ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'xtra_quick_view' ) ) {
			wp_die( '<b>' . esc_html__( 'Server error, Please reload page and try again ...', 'codevz' ) . '</b>' );
		}

		echo '<div class="xtra-qv-product-content">';
		$content = do_shortcode( '[product_page id="' . $_POST['id'] . '"] ' );
		echo str_replace( 'data-src=', 'src=', $content );
		
		echo '</div>';

		echo '<script src="' . plugins_url( 'assets/js/zoom/jquery.zoom.min.js', WC_PLUGIN_FILE ) . '"></script>';
		echo '<script src="' . plugins_url( 'assets/js/flexslider/jquery.flexslider.min.js', WC_PLUGIN_FILE ) . '"></script>';
		echo '<link media="all" href="' . plugins_url( 'codevz-plus/assets/css/share.css' ) . '" rel="stylesheet"/>';

		?><script type='text/javascript'>
		/* <![CDATA[ */
		var wc_single_product_params = <?php echo json_encode( array(
			'flexslider' => apply_filters(
				'woocommerce_single_product_carousel_options',
				array(
					'rtl'            => Codevz_Plus::$is_rtl,
					'animation'      => 'slide',
					'smoothHeight'   => true,
					'directionNav'   => false,
					'controlNav'     => 'thumbnails',
					'slideshow'      => false,
					'animationSpeed' => 500,
					'animationLoop'  => false, // Breaks photoswipe pagination if true.
					'allowOneSlide'  => false,
				)
			),
			'zoom_enabled' => apply_filters( 'woocommerce_single_product_zoom_enabled', get_theme_support( 'wc-product-gallery-zoom' ) ),
			'zoom_options' => apply_filters( 'woocommerce_single_product_zoom_options', array() ),
			'photoswipe_enabled' => false,
			'flexslider_enabled' => apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) ),
		) ); ?>;
		/* ]]> */
		</script><?php

		echo '<script src="' . plugins_url( 'assets/js/frontend/single-product.min.js', WC_PLUGIN_FILE ) . '"></script>';
		echo '<script src="' . plugins_url( 'assets/js/frontend/add-to-cart-variation.min.js', WC_PLUGIN_FILE ) . '"></script>';
		
		wp_die();
	}

	public function woocommerce_before_shop_loop_item_title_low() {
		echo '<div class="xtra-product-thumbnail">';

		$product_id = get_the_ID();

		$wishlist = Codevz_Plus::option( 'woo_wishlist' );
		$compare = Codevz_Plus::option( 'woo_compare' );
		$quick_view = Codevz_Plus::option( 'woo_quick_view' );

		if ( $wishlist || $quick_view ) {

			$center = Codevz_Plus::option( 'woo_wishlist_qv_center' ) ? ' xtra-product-icons-center' : '';
			$center .= $center ? ' cz_tooltip_up' : ( ( Codevz_Plus::$is_rtl || is_rtl() ) ? ' cz_tooltip_right' : ' cz_tooltip_left' );

			echo '<div class="xtra-product-icons' . $center . '" data-id="' . $product_id . '">';
			echo $wishlist ? '<i class="fa fa-heart-o xtra-add-to-wishlist" data-title="' . esc_html__( 'Add to wishlist', 'codevz' ) . '"></i>' : '';
			echo $compare ? '<i class="fa fa-shuffle xtra-add-to-compare" data-title="' . esc_html__( 'Add for compare', 'codevz' ) . '" data-nonce="' . wp_create_nonce( 'xtra_compare' ) . '"></i>' : '';
			echo $quick_view ? '<i class="fa czico-146-search-4 xtra-product-quick-view" data-title="' . esc_html__( 'Quick view', 'codevz' ) . '" data-nonce="' . wp_create_nonce( 'xtra_quick_view' ) . '"></i>' : '';
			echo '</div>';

		}

		$hover = Codevz_Plus::option( 'woo_hover_effect' );

		if ( $hover && class_exists( 'WC_Product' ) ) {

			$product = new WC_Product( $product_id );
			$attachment_ids = $product->get_gallery_image_ids();

			if ( is_array( $attachment_ids ) && isset( $attachment_ids[0] ) ) {

				echo '<div class="cz_image_in">';
				echo '<div class="cz_main_image">';

			}
		}
	}

	public function woocommerce_before_shop_loop_item_title_high() {

		$hover = Codevz_Plus::option( 'woo_hover_effect' );

		if ( $hover && class_exists( 'WC_Product' ) ) {

			$product = new WC_Product( get_the_ID() );
			$attachment_ids = $product->get_gallery_image_ids();

			if ( is_array( $attachment_ids ) && isset( $attachment_ids[0] ) ) {

				echo '</div><div class="cz_hover_image">';

				echo Codevz_Plus::lazyload( Codevz_Plus::get_image( $attachment_ids[0], 'woocommerce_thumbnail' ) );

				echo '</div></div>';

			}

		}

		echo '</div>';

	}

	/**
	 * Quick view popup content.
	 * 
	 * @return string
	 */
	public function quickview_popup( $content = '' ) {

		if ( Codevz_Plus::option( 'woo_quick_view' ) ) {

			$content .= do_shortcode( '[cz_popup id_popup="xtra_quick_view" id="cz_xtra_quick_view" icon="fa czico-198-cancel" sk_icon="color:#ffffff;"][/cz_popup]' );

		}

		return $content;

	}

	/**
	 * Modify checkout page and add wrap to order details.
	 * 
	 * @return string
	 */
	public function checkout_before() {
		echo '<div class="xtra-woo-checkout-details cz_sticky_col">';
	}

	/**
	 * Single product add wrap div.
	 * 
	 * @return string
	 */
	public function before_single() {
		echo '<div class="xtra-single-product clr">';
	}

	public function after_single() {
		echo '</div>';
	}

	/**
	 * Continue shopping button in cart page.
	 * 
	 * @return string
	 */
	public function continue_shopping() {
		echo '<a class="button wc-backward" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . Codevz_Plus::option( 'woo_continue_shopping', esc_html__( 'Continue shopping', 'codevz' ) ) . '</a>';
	}

	/**
	 * Modify products query.
	 * 
	 * @return object
	 */
	public function products_query( $query, $instance ) {

		// Products order.
		$order = Codevz_Plus::option( 'woo_order' );

		if ( $order ) {
			$query->set( 'order', esc_attr( $order ) );
		}

		// Products order by.
		$orderby = Codevz_Plus::option( 'woo_orderby' );

		if ( $orderby ) {
			$query->set( 'orderby', esc_attr( $orderby ) );
		}

	}

	/**
	 * Out of stock button title.
	 * 
	 * @return string
	 */
	public function out_of_stock() {

		if ( Codevz_Plus::option( 'woo_sold_out_badge' ) ) {

			global $product;

			if ( ! $product->is_in_stock() ) {
				echo '<span class="xtra-outofstock">' . Codevz_Plus::option( 'woo_sold_out_title', esc_html__( 'Sold out', 'codevz' ) ) . '</span>';
			}
			
		}

	}

}

new Xtra_Woocommerce;