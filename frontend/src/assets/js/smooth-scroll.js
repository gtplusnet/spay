// $(document).ready(function() 
// {
// 	$('a[href*=#]').bind('click', function(e) 
// 	{
// 		e.preventDefault(); // prevent hard jump, the default behavior

// 		var target = $(this).attr("href"); // Set the target as variable

// 		// perform animated scrolling by getting top-position of target-element and set it as scroll target
// 		$('html, body').stop().animate(
// 		{
// 			scrollTop: $(target).offset().top

// 		}, 600, function()
// 		{
// 			location.hash = target; //attach the hash (#jumptarget) to the pageurl
// 		});

// 		return false;
// 	});
// });

(function($) 
{
    'use strict'; // Start of use strict

    // $('.navigation__link').on('click', function(event) 
    // {
    //     var $anchor = $(this);

    //     $('html, body').stop().animate({
    //         scrollTop: ($($anchor.attr('href')).offset().top - 30)
    //     }, 1250, 'easeInOutExpo'); //1250
        
    //     event.preventDefault();
    // });

    $('.navigation__link').on('click', function(event) 
    {
        var $anchor = $(this);

        $('html, body').stop().animate(
        {
            scrollTop: ($($anchor.attr('href')).offset().top - -1)
        }, 1250, 'easeInOutExpo');
        
        event.preventDefault();
    });

})(jQuery);

$(window).scroll(function() 
{
	var scrollDistance = $(window).scrollTop();

	//Show/hide menu on scroll
	// if (scrollDistance >= 850) {
	// 		$('nav').fadeIn("fast");
	// } else {
	// 		$('nav').fadeOut("fast");
	// }

	// Assign active class to nav links while scolling
	$('.page-section').each(function(i) 
	{
		if ($(this).position().top <= scrollDistance) 
		{
			$('.navbar-nav a.active').removeClass('active');
			$('.navbar-nav a').eq(i).addClass('active');
		}
	});

}).scroll();