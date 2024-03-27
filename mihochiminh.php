<?php 

/** ======= Thêm Lượt Bán và Review vào sản phẩm ====== */

add_action( 'woocommerce_single_product_summary', 'bbloomer_product_sold_count', 9 );
function bbloomer_product_sold_count() {
   global $product;
   $units_sold = $product->get_total_sales();
   if ( $units_sold ) echo '<div class="woocommerce-product-sold">' . sprintf( __( '%s <span class="woocommerce-product-sold-title"">Đã Bán</span>', 'woocommerce' ), $units_sold ) . '</div>';
   $rating_count = $product->get_rating_count();
   if ($units_sold > 0 && $rating_count > 0) echo '<div class="woocommerce-product-sold-line">|</div>';
}

add_action( 'woocommerce_after_shop_loop_item_title', 'wc_product_sold_count' );
function wc_product_sold_count() {
 global $product;
 	?> <div class="woocommerce-sold-rating"> <?php
		if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes' ) {
			$rating_count = $product->get_rating_count();
			$review_count = $product->get_review_count();
			$average      = $product->get_average_rating();
			if ($rating_count > 0) {
				echo '<div class="woocommerce-product-rating"><span class="average-rating">' . wc_get_rating_html($average, $rating_count) . '</span></div>';	
			}
 		}
		$units_sold = get_post_meta( $product->get_id(), 'total_sales', true );
		if ($units_sold > 0) echo '<div class="woocommerce-product-sold">' . sprintf( __( '%s <span class="woocommerce-product-sold-title"">Đã bán</span>', 'woocommerce' ), $units_sold ) . '</div>';
	?> </div> <?php
}

/** ======= Thay đổi giá <= 10 thành Đang cập nhật ====== */
function wc_custom_get_price_0_html( $price, $product ) {
    if ( $product->get_price() <= 10 ) {
        if ( $product->is_on_sale() && $product->get_regular_price() ) {
            $regular_price = wc_get_price_to_display( $product, array( 'qty' => 1, 'price' => $product->get_regular_price() ) );
            $price = wc_format_price_range( $regular_price, __( 'Free!', 'woocommerce' ) );
        } else {
            $price = '<span class="amount price_update">' . __( 'Giá liên hệ', 'woocommerce' ) . '</span>';
        }
    }
    return $price;
}
add_filter( 'woocommerce_get_price_html', 'wc_custom_get_price_0_html', 10, 2 );

function oft_custom_get_price_html( $price, $product ) {
    if ( !is_admin() && !$product->is_in_stock()) {
       $price = '<span class="amount price_update">' . __( 'Giá liên hệ', 'woocommerce' ) . '</span>';
    }
    return $price;
}
add_filter( 'woocommerce_get_price_html', 'oft_custom_get_price_html', 99, 2 );

/** ======= Automatically add IDs to headings ======= */
function auto_id_headings( $content ) {
	$content = preg_replace_callback( '/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', function( $matches ) {
		if ( ! stripos( $matches[0], 'id=' ) ) :
			$matches[0] = $matches[1] . $matches[2] . ' id="' . sanitize_title( $matches[3] ) . '">' . $matches[3]. $matches[4];
		endif;
		return $matches[0];
	}, $content );
    return $content;
}
add_filter( 'the_content', 'auto_id_headings' );

/**
 * Xem thêm nội dung sản phẩm
 * Note: Nếu bạn để tab dạng section trong chi tiết sản phẩm của Flatsome thì tìm và thay thế toàn bộ
 * .single-product div#tab-description thành .product-page-sections .product-section:nth-child(1) > .row > .large-10
 * Hoặc ngược lại
 */
add_action('wp_footer','custom_readmore');
function custom_readmore(){
	if (is_product()) { 
    ?>
    <script>
        (function($){
            $(window).on('load', function(){
                if($('.product-page-sections .product-section:nth-child(1) > .row > .large-10').length > 0){
                    let wrap = $('.product-page-sections .product-section:nth-child(1) > .row > .large-10');
                    let wrap_height = wrap.height();
                    let init_height = 800;
                    if(wrap_height > init_height){
                        wrap.addClass('des-less-height');
                        wrap.append(function(){
                            return '<div class="custom-readmore"><a title="Xem thêm" href="javascript:void(0);">Xem thêm nội dung sản phẩm</a></div>';
                        });
                        wrap.append(function(){
                            return '<div class="custom-readless" style="display: none;"><a title="Thu gọn" href="javascript:void(0);">Thu gọn nội dung sản phẩm</a></div>';
                        });
                        $('body').on('click','.custom-readmore', function(){
                            wrap.removeClass('des-less-height');
                            $('body .custom-readmore').hide();
                            $('body .custom-readless').show();
                        });
                        $('body').on('click','.custom-readless', function(){
                            wrap.addClass('des-less-height');
                            $('body .custom-readless').hide();
                            $('body .custom-readmore').show();
                        });
                    }
                }
            });
        })(jQuery);
    </script>
    <?php
    }
}

/**
 * Note: Hiển thị Block trong code php
 * echo do_shortcode ('[block id="bottom-product-image-gallery"]');
 */

/** ========== BẢO KIM ==========  **/
/**
@ Chèn CSS và Javascript vào theme
@ sử dụng hook wp_enqueue_scripts() để hiển thị nó ra ngoài front-end
**/
function wp_include_bk_css_js() {
	/**
	$handle: Tên của style (Tên này phải đặt duy nhất)
	$src: Đường dẫn đến file CSS
	$deps: Mảng chứa tên style phụ thuộc (Tên style phụ thuộc phải được đăng ký trước. Khi load WordPress sẽ load đối tượng được phụ thuộc trước)
	$ver: Phiên bản của style (Nếu để giá trị false, hệ thống sẽ tự lấy theo phiên bản của WordPress)
	$media: Media của style (Ví dụ: 'all', 'aural', 'braille', 'handheld', 'projection', 'print')
	**/
	wp_enqueue_style( 'bk-popup', 'https://pc.baokim.vn/css/bk.css');

	/**
	$handle: Tên của script (Tên này phải đặt tên duy nhất)
	$src: Đường dẫn tới file js
	$deps: Mảng chứa tên script phụ thuộc (Tên script phụ thuộc phải được đăng ký trước. Khi load WordPress sẽ load đối tượng được phụ thuộc trước)
	$ver: Phiên bản của script (Nếu để giá trị false, hệ thống sẽ tự lấy theo phiên bản của WordPress)
	$in_footer: Chuyển script xuống footer nếu giá trị là true
	**/

// 	wp_enqueue_script('bk-popup', 'https://pc.baokim.vn/js/bk_plus_v2.popup.js', [], false, true);
}
add_action( 'wp_enqueue_scripts', 'wp_include_bk_css_js', 20, 1);


/**
--------------
Trang chi tiết
--------------
**/

/**
Thêm nút btn và modal vào trang chi tiết
**/
function baokim_btn_detail(){
	?>
	<div class="bk-btn" style="margin-top: 10px">
	
	</div>
	<?php
}
add_action('woocommerce_after_add_to_cart_form','baokim_btn_detail');

/**
Xử lý để lấy ra dữ liệu
**/
function get_info($product){
	global $product;
	ob_start();
	$id = $product->get_id();
	$productPrice = (int)$product->get_price();
	?>
	<div style="display: none">
		<p class="bk-product-price"><?php echo $productPrice ?></p>
		<p class="bk-product-name"><?php echo the_title(); ?></p>
		<?php 
		echo get_the_post_thumbnail( $id, 'medium', array('class' =>'bk-product-image')); 
		if ( method_exists( $product, 'get_stock_status' ) ) {
            $stock_status = $product->get_stock_status(); // For version 3.0+
        } else {
            $stock_status = $product->stock_status; // Older than version 3.0
        }
        $list_stock = [
        	"instock"     => "Trong kho",
        	"outofstock"  => "Hết hàng",
        	"onbackorder" => "Đặt trước",
        	"contact"     => "Liên hệ",
        	"preorder"    => "Đặt hàng trước"
        ];
        ?>
        <p class="bk-check-out-of-stock"><?php echo isset($list_stock[$stock_status]) ? $list_stock[$stock_status] : "" ?></p>
    </div>
    <?php
    echo ob_get_clean();
}
add_action('woocommerce_after_single_product','get_info');

/**
Chèn class vào trang
**/
function hook_javascript_footer() {
	?>
	<script src="https://pc.baokim.vn/js/bk_plus_v2.popup.js"></script>
	<style>
		#bk-btn-paynow, #bk-btn-installment, .bk-btn-paynow, .bk-btn-installment {
			outline: none;
		}
		#bk-modal-close, #bk-modal-notify-close {
			margin: 0;
			padding: 0;
			outline: none;
		}
		.bk-btn .bk-btn-paynow {
			width: 100%;
			line-height: 1.6rem;
			font-weight: 500;
			padding-left: 0 !important;
			padding-right: 0 !important;
		}

		.bk-btn .bk-btn-installment {
			width: calc(50% - 5px);
			line-height: 1.6rem;
			margin-right: 0px;
            font-weight: 500;
            font-size: 12px;
			float: right;
			padding-left: 0 !important;
			padding-right: 0 !important;
		}

		.bk-btn .bk-btn-installment-amigo {
			width: calc(50% - 5px);
			margin-right: 0px;
			line-height: 1.6rem;
            font-weight: 500;
            float: left;
            font-size: 12px;
			padding-left: 0 !important;
			padding-right: 0 !important;
		}
		.bk-container-fluid {
			height: 60px;
		}
	</style>
	<script type="text/javascript">
		var productQuantityClass = document.getElementsByClassName("product-quantity");
        for(var i = 0; i < productQuantityClass.length; i++) {
            if(productQuantityClass[i].querySelector('.input-text')) {
                productQuantityClass[i].querySelector('.input-text').classList.add("bk-product-qty");
            }
        }
	</script>
	<?php
}
// add_action('woocommerce_after_main_content', 'hook_javascript_footer');
add_action('woocommerce_after_single_product', 'hook_javascript_footer');

/**
Chèn class vào trang lấy thuộc tính trang chi tiết
**/
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', static function( $args ) {
	$args['class'] = 'bk-product-property';
	return $args;
}, 2 );

add_filter( 'woocommerce_variation_options_pricing', static function( $args ) {
	if(is_array( $args )) {
		$args['class'] = 'bk-product-property';
	}
	return $args;
}, 2 );



/**
--------------
Trang giỏ hàng
--------------
**/

/**
Thêm nút btn vào trang giỏ hàng
**/
// woocommerce_after_cart
function baokim_btn_cart(){
	?>
	<div class="bk-btn" style="margin-top: 10px">
	
	</div>
	<?php
}
add_action('woocommerce_proceed_to_checkout','baokim_btn_cart');

/**
Chèn modal, class vào trang cart
**/
function hook_modal_javascript_cart() {
	?>
	<script src="https://pc.baokim.vn/js/bk_plus_v2.popup.js"></script>
	<style>
		#bk-btn-paynow, #bk-btn-installment, .bk-btn-paynow, .bk-btn-installment {
			outline: none;
		}
		#bk-modal-close, #bk-modal-notify-close {
			margin: 0;
			padding: 0;
			outline: none;
		}
		.bk-btn .bk-btn-paynow {
			width: 100%;
			line-height: 1.6rem;
		}

		.bk-btn .bk-btn-installment {
			width: 100%;
			line-height: 1.6rem;
			margin-right: 0px;
		}

		.bk-btn .bk-btn-installment-amigo {
			width: 100%;
			margin-right: 0px;
			line-height: 1.6rem;
		}
		.bk-container-fluid {
			height: 60px;
		}
	</style>
	<script type="text/javascript">
		var productImageClass = document.getElementsByClassName("product-thumbnail");
        for(var i = 0; i < productImageClass.length; i++) {
            if(productImageClass[i].querySelector('img')) {
                productImageClass[i].querySelector('img').classList.add("bk-product-image");
            }
        }

        var productNameClass = document.getElementsByClassName("product-name");
        for(var i = 0; i < productNameClass.length; i++) {
            if(productNameClass[i].querySelector('a')) {
                productNameClass[i].querySelector('a').classList.add("bk-product-name");
            }
        }

        var productPriceClass = document.getElementsByClassName("product-price");
        for(var i = 0; i < productPriceClass.length; i++) {
            if(productPriceClass[i].querySelector('.amount')) {
                productPriceClass[i].querySelector('.amount').classList.add("bk-product-price");
            }
        }

        var productQuantityClass = document.getElementsByClassName("product-quantity");
        for(var i = 0; i < productQuantityClass.length; i++) {
            if(productQuantityClass[i].querySelector('.input-text')) {
                productQuantityClass[i].querySelector('.input-text').classList.add("bk-product-qty");
            }
        }
	</script>
	<?php
}
add_action('woocommerce_after_cart', 'hook_modal_javascript_cart');

add_action('wp_footer', 'wpshout_action_example'); 
function wpshout_action_example() {
	?>
	<div id='bk-modal'></div>
	<script>
		window.addEventListener("load", function(event) {
			var btnCloseModal = document.getElementById('bk-modal-close');
			btnCloseModal.addEventListener("click", function(){ 
				location.reload();
			});
			jQuery( '.variations_form' ).each( function() {
				jQuery(this).on( 'found_variation', function( event, variation ) {
					console.log(variation);//all details here
					var price = variation.display_price;//selectedprice
					document.getElementsByClassName('bk-product-price')[0].innerHTML = price;
				});
			});
		});
	</script>
	<?php
}

/** ========== END BẢO KIM ==========  **/