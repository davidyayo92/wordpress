// Horizontal gallery slider.
jQuery( '.ast-single-product-thumbnails' ).flexslider( {
    animation: "slide",
    controlNav: false,
    animationLoop: false,
    slideshow: false,
    itemWidth: 80,
    itemMargin: 15,
    maxItems: 4,
    asNavFor: '.woocommerce-product-gallery',
    selector: '.woocommerce-product-gallery-thumbnails__wrapper > div',
} );