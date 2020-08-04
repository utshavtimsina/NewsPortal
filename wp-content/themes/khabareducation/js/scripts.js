jQuery(document).ready(function(){
	jQuery(".hm-search-button-icon").click(function() {
		jQuery(".hm-search-box-container").toggle('fast');
		jQuery(this).toggleClass("hm-search-close");
	});
});

jQuery(document).ready(function(){
	jQuery('.image-link').magnificPopup({
		type: 'image'
	});
});

/* Featured Slider */

jQuery(document).ready(function() {
	// The slider being synced must be initialized first
	jQuery('#hm-carousel').flexslider({
		animation: "slide",
		controlNav: false,
		animationLoop: false,
		slideshow: true,
		itemWidth: 135,
		itemMargin: 10,
		asNavFor: '#hm-slider'
	});

	jQuery('#hm-slider').flexslider({
		animation: "slide",
		controlNav: false,
		animationLoop: false,
		slideshow: true,
		sync: "#hm-carousel"
	});
	
	jQuery('.recent-news-slider-init').slick({
          infinite: true,
          slidesToShow: 4,
          slidesToScroll: 1,
          arrows: false,
          dots: false,
          autoplay: true,
          autoplaySpeed: 3000,
          responsive: [
    {
      breakpoint: 768,
      settings: {
         slidesToShow: 3,
      }
    },
    {
      breakpoint: 480,
      settings: {
         slidesToShow: 2,
      }
    }
  ]
    });
		
	

	 
	
	jQuery(".hm-nav-container").sticky({topSpacing:0});
});


/* Link the whole slide to the link*/
(function($) {
	$('div.hm-slider-container').on( 'click', function(e) {
		if ( $(e.target).is('span.cat-links') ) { 
			return false;
		} else {
			window.location = $(this).data('loc');
		}
	});
})(jQuery);

/* Tabs Widget */
jQuery(document).ready( function() {
	if ( jQuery.isFunction(jQuery.fn.tabs) ) {
		jQuery( ".hm-tabs-wdt" ).tabs();
	}
	jQuery('#primary, #secondary').theiaStickySidebar({
      // Settings
      additionalMarginTop: 30
    });
});