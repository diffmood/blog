! function( $ ) {
	"use strict";

	if ( typeof Codevz_Plus != 'object' ) {
		var Codevz_Plus = {};
	}

	Codevz_Plus.woocommerce = function( quantity, timeout ) {

		var body = $( document.body ),
			cartForm = $( '.woocommerce-cart-form__contents' );

		if ( cartForm.length && ! $( '.xtra-cart-td-prefix' ).length ) {

			var cartItem = $( '.cart_item' ),
				productText = cartForm.find( 'th.product-name' ).text(),
				priceText = cartForm.find( 'th.product-price' ).text(),
				quantityText = cartForm.find( 'th.product-quantity' ).text(),
				subTotalText = cartForm.find( 'th.product-subtotal' ).text();

			cartItem.find( 'td.product-name' ).prepend( '<div class="xtra-cart-td-prefix">' + productText + '</div>' );
			cartItem.find( 'td.product-price' ).prepend( '<div class="xtra-cart-td-prefix">' + priceText + '</div>' );
			cartItem.find( 'td.product-quantity' ).prepend( '<div class="xtra-cart-td-prefix">' + quantityText + '</div>' );
			cartItem.find( 'td.product-subtotal' ).prepend( '<div class="xtra-cart-td-prefix">' + subTotalText + '</div>' );

		}

		// Product quantity field
		if ( $( '.quantity' ).length ) {

			$( '.quantity input' ).each( function() {

				var $this = $( this ),
					grouped = $this.closest( '.woocommerce-grouped-product-list-item' ).length;

				if ( ! $this.next( '.quantity-up' ).length ) {

					$( '<div class="quantity-down">-</div>' ).insertBefore( $this );
					$( '<div class="quantity-up">+</div>' ).insertAfter( $this );

					$this.parent().on( 'click', '.quantity-up, .quantity-down', function() {

						var en 		 = $( this ),
							input 	 = en.closest( '.quantity' ).find( 'input' ),
							oldValue = parseInt( input.val() ) || 0,
							oldValue = en.hasClass( 'quantity-down' ) && oldValue <= 1 && ! grouped ? 2 : oldValue,
							oldValue = en.hasClass( 'quantity-down' ) && oldValue == 0 ? 1 : oldValue;

						input.val( en.hasClass( 'quantity-up' ) ? oldValue + 1 : oldValue - 1 ).trigger( 'change' );

					});

				}

				$this.attr( 'type', 'number' );

			});

			// Update cart fix quantity script.
			body.on( 'updated_cart_totals', function( e ) {

				Codevz_Plus.woocommerce( 2 );

			// Auto update cart.
			}).on( 'change', '.woocommerce-cart-form input.qty', function() {

				clearTimeout( timeout );

				timeout = setTimeout( function() {
					$( '[name="update_cart"]' ).trigger( 'click' );
				}, 1000 );

			});

		}

		// Only quantity
		if ( quantity ) {
			return;
		}

		// Auto x-position shop cart in header.
		if ( $( '.elms_shop_cart' ).length ) {

			body.on( 'mouseenter', '.elms_shop_cart', function() {

				var cartX 		= $( this ),
					iconX 		= cartX.find( '.shop_icon i' ),
					iconWidth 	= iconX.outerWidth(),
					dropdown  	= cartX.find( '.cz_cart_items' );

				if ( ( $( window ).width() / 2 ) > ( cartX.offset().left + 300 ) ) {

					cartX.addClass( 'inview_right' );

					var iconMl = parseFloat( iconX.css( 'marginLeft' ) );

					if ( body.hasClass( 'rtl' ) ) {
						dropdown.css( 'left', ( ( iconWidth / 2 ) - 38 + iconMl ) );
					} else {
						dropdown.css( 'left', -( ( iconWidth / 2 ) - 36 + iconMl ) );
					}

				} else {

					dropdown.css( 'right', ( ( iconWidth / 2 ) - 36 + parseFloat( iconX.css( 'marginRight' ) ) ) );

				}

			});

		}

		// Current wishlist items
		if ( $( '.xtra-add-to-wishlist' ).length || $( '.xtra-wishlist' ).length || $( '.cz_wishlist_count' ).length ) {

			var wishlist = localStorage.getItem( 'xtraWishlist' ),
				wishlistDiv = $( '.xtra-wishlist' ),
				noWishlist = '<h2 class="xtra-wishlist-empty tac">' + wishlistDiv.data( 'empty' ) + '</h2><a href="' + xtra_strings.shop_url + '" class="cz_btn" style="margin:40px auto;display:table;"><span><strong>' + xtra_strings.back_to_shop + '</strong></span></a>';

			// Wishlist shortcode
			wishlistDiv.length && wishlistDiv.each( function() {

				var en = $( this ),
					nonce = en.data( 'nonce' );

				if ( wishlist ) {

					// Check ID's.
					$.post( body.data( 'ajax' ), 'action=xtra_wishlist_content&check=1&ids=' + wishlist + '&nonce=' + nonce, function( msg ) {

						localStorage.setItem( 'xtraWishlist', msg );

						wishlist = msg;

						// Get wishlist products.
						$.post( body.data( 'ajax' ), 'action=xtra_wishlist_content&ids=' + wishlist + '&nonce=' + nonce, function( msg ) {

							en.removeClass( 'xtra-icon-loading' ).html( msg );

							$( '.product' ).prepend( '<i class="xtra-remove-from-wishlist fas fa-times"></i>' );

							var count = localStorage.getItem( 'xtraWishlist' ).replace( /\d+/g,'' ).length;

							// Count
							if ( count ) {
								$( '.cz_wishlist_count' ).show().html( count || '' );
							} else {
								$( '.cz_wishlist_count' ).hide();
								en.removeClass( 'xtra-icon-loading' ).html( noWishlist );
							}

							if ( ! en.find( 'li' ).length ) {
								en.removeClass( 'xtra-icon-loading' ).html( noWishlist );
							}

						});

					});

				} else {

					en.removeClass( 'xtra-icon-loading' ).html( noWishlist );

				}

			});

			// Set wishlist products
			if ( wishlist ) {
				wishlist = wishlist.split( ',' );

				$.each( wishlist, function( index, id ) {
					var product = $( '[data-id="' + id + '"] .xtra-add-to-wishlist' );

					if ( product.length ) {
						product.removeClass( 'fa-heart-o' ).addClass( 'fa-heart' ).attr( 'data-title', xtra_strings.added_wishlist );
					}
				});

				var count = localStorage.getItem( 'xtraWishlist' ).replace( /\d+/g,'' ).length;

				// Count
				if ( count ) {
					$( '.cz_wishlist_count' ).show().html( count );
				} else {
					$( '.cz_wishlist_count' ).hide();
					wishlistDiv.removeClass( 'xtra-icon-loading' ).html( noWishlist );
				}
			}

			// Wishlist icon.
			body.on( 'click', '.xtra-add-to-wishlist,.xtra-remove-from-wishlist', function(e) {

				var en = $( this ),
					id = en.closest( '.product' ).find( '[data-id]' ).data( 'id' ) + ',',
					ls = localStorage.getItem( 'xtraWishlist' ) || '',
					tt = en.attr( 'data-title' );

				if ( en.hasClass( 'fa-heart' ) && ! en.closest( '.xtra-wishlist' ).length ) {

					window.location.replace( xtra_strings.wishlist_url );

				} else {

					en.addClass( 'xtra-icon-loading' ).removeAttr( 'data-title' );

					setTimeout(function() {

						if ( en.hasClass( 'fa-heart' ) || en.hasClass( 'fa-times' ) ) {

							ls = ls.replace( id, '' );

							localStorage.setItem( 'xtraWishlist', ls );

							tt = xtra_strings.add_wishlist;

							en.removeClass( 'fa-heart' ).addClass( 'fa-heart-o' );

						} else if ( ls.indexOf( id ) < 0 ) {

							localStorage.setItem( 'xtraWishlist', ls + id );

							tt = xtra_strings.added_wishlist;

							$( '[data-id="' + id.replace( ',', '' ) + '"] .xtra-add-to-wishlist' ).removeClass( 'fa-heart-o' ).addClass( 'fa-heart' ).attr( 'data-title', xtra_strings.added_wishlist );

						}

						en.removeClass( 'xtra-icon-loading' );

						setTimeout( function() {

							en.attr( 'data-title', tt );

						}, 250 );

						// Wishlist page.
						if ( en.closest( '.xtra-wishlist' ).length ) {

							en.closest( 'li' ).fadeOut(function() {

								$( this ).remove();

								if ( ! wishlistDiv.find( 'li' ).length ) {
									wishlistDiv.removeClass( 'xtra-icon-loading' ).html( noWishlist );
								}

							});

						}

						var count = localStorage.getItem( 'xtraWishlist' ).replace( /\d+/g,'' ).length;

						// Count
						if ( count ) {

							$( '.cz_wishlist_count' ).show().html( count || '' );

						} else {

							$( '.cz_wishlist_count' ).hide();
							wishlistDiv.removeClass( 'xtra-icon-loading' ).html( noWishlist );

						}

					}, 1000 );

				}

				e.preventDefault();
			});

		}

		// Current compare items
		if ( $( '.xtra-add-to-compare' ).length || $( '.xtra-compare' ).length || $( '.cz_compare_count' ).length ) {

			// Compare icon.
			body.on( 'click', '.xtra-add-to-compare,.xtra-remove-from-compare', function(e) {

				var en = $( this ),
					id = en.closest( '.product' ).find( '[data-id]' ).data( 'id' ) + ',',
					ls = localStorage.getItem( 'xtraCompare' ) || '',
					tt = en.attr( 'data-title' );

				// Remove from compare.
				if ( en.attr( 'class' ).indexOf( 'remove' ) > 0 ) {

					id = en.closest( 'td' ).find( '[data-id]' ).data( 'id' ) + ',';

					en.addClass( 'xtra-icon-loading' );

					ls = ls.replace( id, '' );

					localStorage.setItem( 'xtraCompare', ls );

					window.location.replace( xtra_strings.compare_url );

					e.preventDefault();

					return false;

				}

				// ...
				if ( en.hasClass( 'fa-check' ) && ! en.closest( '.xtra-compare' ).length ) {

					window.location.replace( xtra_strings.compare_url );

				} else {

					en.addClass( 'xtra-icon-loading' ).removeAttr( 'data-title' );

					setTimeout( function() {

						if ( en.hasClass( 'fa-check' ) || en.hasClass( 'fa-times' ) ) {

							ls = ls.replace( id, '' );

							localStorage.setItem( 'xtraCompare', ls );

							tt = xtra_strings.add_compare;

							en.removeClass( 'fa-check' ).addClass( 'fa-shuffle' ).attr( 'data-title', xtra_strings.add_compare );

						} else if ( ls.indexOf( id ) < 0 ) {

							localStorage.setItem( 'xtraCompare', ls + id );

							tt = xtra_strings.added_compare;

							$( '[data-id="' + id.replace( ',', '' ) + '"] .xtra-add-to-compare' ).removeClass( 'fa-shuffle' ).addClass( 'fa-check' ).attr( 'data-title', xtra_strings.added_compare );

						}

						en.removeClass( 'xtra-icon-loading' );

						setTimeout( function() {

							en.attr( 'data-title', tt );

						}, 250 );

						var compareDiv = null;

						// Compare page.
						if ( en.closest( '.xtra-compare' ).length ) {

							en.closest( 'li' ).fadeOut(function() {

								$( this ).remove();

								if ( ! compareDiv.find( 'li' ).length ) {
									compareDiv.removeClass( 'xtra-icon-loading' ).html( noCompare );
								}

							});

						}

						var count = localStorage.getItem( 'xtraCompare' ).replace( /\d+/g,'' ).length;

						// Count
						if ( count ) {

							$( '.cz_compare_count' ).show().html( count || '' );

						} else {

							$( '.cz_compare_count' ).hide();
							compareDiv.removeClass( 'xtra-icon-loading' ).html( noCompare );

						}

					}, 1000 );

				}

				e.preventDefault();
			});

			var compare = localStorage.getItem( 'xtraCompare' ),
				compareDiv = $( '.xtra-compare' ),
				noCompare = '<h2 class="xtra-compare-empty tac">' + compareDiv.data( 'empty' ) + '</h2><a href="' + xtra_strings.shop_url + '" class="cz_btn" style="margin:40px auto;display:table;"><span><strong>' + xtra_strings.back_to_shop + '</strong></span></a>';

			// Compare shortcode
			compareDiv.length && compareDiv.each( function() {

				var en = $( this ),
					nonce = en.data( 'nonce' );

				if ( compare ) {

					// Check ID's.
					$.post( body.data( 'ajax' ), 'action=xtra_compare_content&check=1&ids=' + compare + '&nonce=' + nonce, function( msg ) {

						localStorage.setItem( 'xtraCompare', msg );

						compare = msg;

						// Get compare products.
						$.post( body.data( 'ajax' ), 'action=xtra_compare_content&ids=' + compare + '&nonce=' + nonce, function( msg ) {

							en.removeClass( 'xtra-icon-loading' ).html( msg );

							$( '.cz-compare-tr-general td' ).prepend( '<i class="xtra-remove-from-compare fas fa-times"></i>' );

							var count = localStorage.getItem( 'xtraCompare' ).replace( /\d+/g,'' ).length;

							// Count
							if ( count ) {
								$( '.cz_compare_count' ).show().html( count || '' );
							} else {
								$( '.cz_compare_count' ).hide();
								en.removeClass( 'xtra-icon-loading' ).html( noCompare );
							}

							if ( ! en.find( 'li' ).length ) {
								en.removeClass( 'xtra-icon-loading' ).html( noCompare );
							}

						});

					});

				} else {

					en.removeClass( 'xtra-icon-loading' ).html( noCompare );

				}

			});

			// Set compare products
			if ( compare ) {
				compare = compare.split( ',' );

				$.each( compare, function( index, id ) {
					var product = id ? $( '[data-id="' + id + '"] .xtra-add-to-compare' ) : 0;

					if ( product.length ) {
						product.removeClass( 'fa-shuffle' ).addClass( 'fa-check' ).attr( 'data-title', xtra_strings.added_compare );
					}
				});

				var count = localStorage.getItem( 'xtraCompare' ).replace( /\d+/g,'' ).length;

				// Count
				if ( count ) {
					$( '.cz_compare_count' ).show().html( count );
				} else {
					$( '.cz_compare_count' ).hide();
					compareDiv.removeClass( 'xtra-icon-loading' ).html( noCompare );
				}
			}

		}

		// Product quick view.
		if ( $( '.xtra-product-quick-view' ).length ) {

			body.on( 'click', '.xtra-product-quick-view', function( e ) {

				e.preventDefault();

				var x = $( this ),
					id = x.parent().data( 'id' ),
					nonce = x.data( 'nonce' ),
					popup = $( '#xtra_quick_view' ),
					content = popup.find( '.cz_popup_in > div' ),
					tt = x.attr( 'data-title' );

				popup.appendTo( 'body' );

				x.addClass( 'xtra-icon-loading' ).removeAttr( 'data-title' );

				content.html( '' );
				content.addClass( 'xtra-qv-loading' );

				popup.fadeIn( 'fast' ).delay( 1000 ).addClass( 'cz_show_popup' );

				$( 'html, body' ).addClass( 'no-scroll' );

				$.post( body.data( 'ajax' ), 'action=xtra_quick_view&id=' + id + '&nonce=' + nonce, function( msg ) {

					x.removeClass( 'xtra-icon-loading' ).attr( 'data-title', tt );
					content.removeClass().html( msg );

					// Set wishlist in quickview.
					if ( localStorage.getItem( 'xtraWishlist' ) && localStorage.getItem( 'xtraWishlist' ).indexOf( id ) >= 0 ) {

						$( '[data-id="' + id + '"] .xtra-add-to-wishlist' ).removeClass( 'fa-heart-o' ).addClass( 'fa-heart' ).attr( 'data-title', xtra_strings.added_wishlist );

					}

					// Set compare in quickview.
					if ( localStorage.getItem( 'xtraCompare' ) && localStorage.getItem( 'xtraCompare' ).indexOf( id ) >= 0 ) {

						$( '[data-id="' + id + '"] .xtra-add-to-compare' ).removeClass( 'fa-shuffle' ).addClass( 'fa-check' ).attr( 'data-title', xtra_strings.added_compare );

					}

					// Fix flex slider.
					setTimeout( function() {

						content.find( '.woocommerce-product-gallery' ).each( function() {
							$( this ).wc_product_gallery();
						} );

					}, 500 );

					// Add to cart redirect to single product on quick view popup. 
					var $button = content.find( '[name="add-to-cart"]' );
					$button.closest( 'form' ).attr( 'action', xtra_strings.shop_url + '?p=' + $button.attr( 'value' ) );

					Codevz_Plus.woocommerce( 1 );

				});

				return false;

			});

		}

		// Remove item from cart ajax.
		if ( $( '.cart_list .remove' ).length ) {
			
			body.on( 'click', '.cart_list .remove', function( e ) {

				var x = $( this );

				x.css( 'background', 'none' ).addClass( 'xtra-icon-loading' ).removeAttr( 'data-title' );

				$.post( body.data( 'ajax' ), 'action=xtra_remove_item_from_cart&id=' + x.data( 'product_id' ), function( msg ) {

					if ( $( '.cz_cart' ).find( '.woocommerce-Price-amount' ).text() == $( msg['fragments']['.cz_cart'] ).find( '.woocommerce-Price-amount' ).text() ) {

						window.location = x.attr( 'href' );

					} else {

						$( '.cz_cart' ).html( msg['fragments']['.cz_cart'] );

					}

				});

				e.preventDefault();

			});

		}

		// Append onsale badge to parent.
		$( '.products .onsale' ).codevz( 'onsale', function( x ) {

			x.appendTo( x.closest( 'a' ) );

		});

		// Fix City and state for RTL websites.
		if ( body.hasClass( 'woocommerce-checkout' ) && body.hasClass( 'rtl' ) ) {

			setTimeout( function() {

				$( '#billing_state_field' ).insertBefore( '#billing_city_field' );

			}, 500 );

		}

		// Tabs scroll to.
		if ( body.hasClass( 'single-product' ) ) {

			setTimeout( function() {

				body.on( 'click', '.wc-tabs a', function() {

					var $page = $( 'html, body' ),
						sticky = $( '.header_is_sticky' ).not( '.smart_sticky,.header_4' );

					$page.animate({

						scrollTop: $( '.woocommerce-tabs' ).offset().top - 50 - ( sticky.outerHeight() || 0 )

					}, 1000, 'easeInOutCodevz', function() {

						$page.stop();

					});

				});

			}, 3000 );

			// Product tabs in mobile.
			var $tabs = $( '.wc-tabs' );

			if ( $tabs.find( 'li' ).length >= 3 ) {

				$tabs.addClass( 'hide_on_mobile' ).before( '<select class="xtra-woo-tabs" />' );

				$tabs.find( 'li' ).each( function() {

					var $this = $( this );
					$( '.xtra-woo-tabs' ).append( '<option value="' + $this.attr( 'id' ) + '">' + $this.text() + '</option>' );

				});

				$( '.xtra-woo-tabs' ).on( 'change', function() {
					$tabs.find( '#' + this.value + ' > a' ).trigger( 'click' );
				});

			}

			// Disable product page lightbox
			$( '.woo-disable-lightbox .woocommerce-product-gallery__wrapper > div:first-child a' ).removeAttr( 'href' ).css( 'cursor', 'default' );

			// Move single onSale badge to sticky.
			$( '.xtra-single-product' ).prev( '.onsale' ).prependTo( $( '.xtra-single-product > div:first-child' ) );

			// Woo sticky column.
			$( '.woocommerce-product-gallery' ).addClass( 'cz_sticky_col' );

		} // Single product page.

	};

	Codevz_Plus.woocommerce();

}( jQuery );