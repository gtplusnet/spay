var custom_js = new custom_js();

function custom_js() 
{
    init();

    function init() 
    {
        $(document).ready(function() 
        {
            document_ready();
        });
    }

    function document_ready() 
    {
        scroll_up();
        header_fixed();
    }

    function scroll_up() 
    {
        /*scroll up*/
        $(window).scroll(function() 
        {
            if ($(this).scrollTop() > 700) 
            {
                $('.scroll-up').fadeIn();
            } else 
            {
                $('.scroll-up').fadeOut();
            }
        });

        $('.scroll-up').click(function() 
        {
            $("html, body").animate(
            {
                scrollTop: 0
            }, 700);
            return false;
        });
    }

    function header_fixed() 
    {
        $window = $(window);
        $window.scroll(function() 
        {
            $scroll_position = $window.scrollTop();
            if ($scroll_position > 100) 
            {
                $('.header-container').addClass('header-fixed');

                header_height = $('.your-header').innerHeight();
                $('body').css('padding-top', header_height);
            } else 
            {
                $('.header-container').removeClass('header-fixed');
                $('body').css('padding-top', '0');
            }
        });
    }
}