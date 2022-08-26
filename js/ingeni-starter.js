jQuery(document).ready(function() {

	console.log('loaded...');

	var hash = window.location.hash;
	console.log('hash='+hash);
	if (hash.length > 0) {
		doAnchorScroll(null, window.location);
	}

	// hide #back-top first
	jQuery("#back-top").hide();

	// fade in #back-top
	jQuery(function() {
		jQuery(window).scroll(function() {
				if (jQuery(this).scrollTop() > 100) {
						jQuery('#back-top').fadeIn();
				} else {
						jQuery('#back-top').fadeOut();
				}
		});

	});

});



// Smooth anchor nav scrolling
// Select all links with hashes
jQuery('a[href*="#"]')
  // Remove links that don't actually link to anything
  .not('[href="#"]')
  .not('[href="#0"]')
  .not('[href^="#pnl"]')
  .click(function(event) {

    // On-page links
    if (
      location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') 
      && 
      location.hostname == this.hostname
    ) {
      // Figure out element to scroll to
      var target = jQuery(this.hash);
//console.log('scrolling '+target);
      target = target.length ? target : jQuery('[name=' + this.hash.slice(1) + ']');
      // Does a scroll target exist?
      if (target.length) {
        // Only prevent default if animation is actually gonna happen
        event.preventDefault();
        jQuery('html, body').animate({
          scrollTop: target.offset().top
        }, 1000);
      }
    }
  });



// Enlarge font
jQuery( "#fontsizeminus" ).click(function(e) {
	toggleFontSize(e);
});
jQuery( "#fontsizeplus" ).click(function(e) {
	toggleFontSize(e);
});

function toggleFontSize(e) {
	jQuery( "html" ).toggleClass( "largefont" );
	console.log( "User clicked on header-font-size" );
}


function doAnchorScroll(e, id) {

	//console.log('id:'+id);
	if (id.length === 0) {
			return;
	}

	// prevent standard hash navigation (avoid blinking in IE)
	if (e) {
		e.preventDefault();
	}
	
	//console.log('id='+id);
	try {
		// Handle anchor links on the same page
		if ( id.slice(0,1) == '#') {
			var target = id;
		}
	} catch(err) {
		// Handle anchor links with a complete URL
		var target = jQuery(id.hash);
		//console.log( 'target:'+target);
		target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
	}

	//console.log('hash='+target);
	var pos = jQuery(target).offset().top;

	// If the sticky main nav will be visible, scroll down a bit so you can see
	// the start of the div
	var reverser = 80;

	if ( pos > reverser ) {
		pos = pos - (2 * reverser);
	}
	// animated top scrolling
	jQuery('html,body').animate({
			scrollTop: pos
	}, 1000);
}
