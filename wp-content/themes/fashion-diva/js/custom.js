jQuery(document).ready(function($){    
    
    var rtl, mrtl, winWidth;
    
    if( fashion_diva_data.rtl == '1' ){
        rtl = true;
        mrtl = false;
    }else{
        rtl = false;
        mrtl = true;
    }
    winWidth = $(window).width();
    
    //banner layout two
    $('#banner-slider-two').owlCarousel({
        loop       : true,
        nav        : true,
        items      : 1,
        dots       : true,
        autoplay   : true,
        animateOut : '',
        navText    : '',
        center     : true,
        rtl        : rtl,
        lazyLoad   : true,
        responsive : {
            1200: {
                margin: 80,
                stagePadding: 234,
            },
            1025: {
                margin: 50,
                stagePadding: 150,
            },
            768: {
                margin: 20,
                stagePadding: 50
            }
        }
    });

    // $('.site-header .form-holder').prepend('<button class="btn-close-form"><span></span></button>');

    $('.btn-close-form').click(function(){
        $(this).parent('.form-holder').hide("fast");
    });

    $(window).keyup(function(event){
        if(event.key == 'Escape') {
            $('.form-holder').hide("fast");    
        }
    });

    //secondary menu
    $('.secondary-nav ul li.menu-item-has-children').append('<button><i class="fa fa-angle-down"></i></button>');
    $('.secondary-nav ul li button').click(function(){
        $(this).prev().slideToggle();
        $(this).toggleClass('active');
    });

    $('#secondary-toggle-button').click(function(){
        $('.secondary-nav').toggleClass('menu-active');
        $(this).toggleClass('close');
    });
       
});