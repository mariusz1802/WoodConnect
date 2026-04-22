$(function() {
    $('#slider .slides').responsiveSlides({
        timeout : 3000,
        pauseControls : false,
        pager: false,
        auto: true,
        nav: true,
        pause : false
    });
    $('.filter select').change(function() {
        $(this).parents('form').submit();
    });
    $('.quick-contact-wrap').addClass('quick-contact-open');
    setTimeout(function() {
        $('.quick-contact-wrap').removeClass('quick-contact-open');
    }, 5000);
    $('a[data-rel^=gallery]').lightcase({
        transition: 'scrollHorizontal',
        maxWidth: 2000,
        maxHeight: 2000,
        labels: {
            'errorMessage': 'Nie znaleziono pliku...',
            'sequenceInfo.of': ' / ',
            'close': 'Close',
            'navigator.prev': 'Prev',
            'navigator.next': 'Next',
            'navigator.play': 'Play',
            'navigator.pause': 'Pause'
        }
    });
    $('header menu .search').click(function() {
        var par = $(this).parents('header');
        if (par.is('.show-search')) {
            par.removeClass('show-search');
        } else {
            par.find('input[name=query]').focus();
            par.addClass('show-search');
        }
        return false;
    });
    $('#slider .down').click(function() {
        $('html, body').animate({
            scrollTop: $('#cnt').offset().top-$('header').height()
        }, 300);

        return false;
    });
    $('.newsletter-flash, .info-box').each(function() {
        lightcase.start({
            href : '#' + this.id
        });
    })
    $('.rate-input.editable').each(function() {
        var me = $(this), activate = function(cnt) {
            me.find('.rate').each(function() {
                cnt = parseInt(cnt);
                if ($(this).data('val')<=cnt) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            })
        };
        me.hover(function(){}, function() {
            activate($('#' + me.data('id')).attr('value'));
        }).find('.rate').hover(function() {
            activate($(this).data('val'))
        }).click(function() {
            $('#' + me.data('id')).attr('value', $(this).data('val'));
        });

    });

    $('.attribute > h2').on('click', function() {
        var attr =$(this).parent();
        if (attr.is('.attribute-open')) {
            attr.removeClass('attribute-open').find('.attribute-content').slideUp('fast');
        } else {
            attr.parent().find('.attribute-open').removeClass('attribute-open').find('.attribute-content').slideUp('fast');
            attr.addClass('attribute-open').find('.attribute-content').slideDown('fast');
        }

        return false;
    });
    if (window.location.hash == '#go') {
        $('#slider .down').click();
    }
    $('.contact-form .form-group input, .contact-form  .form-group textarea').change(function() {
        if ($(this).prop('value') && $(this).prop('value').length > 0) {
            $(this).parent().addClass('has-value');
        } else {
            $(this).parent().removeClass('has-value');
        }
    }).keyup(function() {$(this).change();}).change();

    $(window).scroll({
        previousTop : $(window).scrollTop(),
        first : true
    }, function(ev) {
        var currentTop = $(window).scrollTop(), h = $('header');
        if ($(window).width() < 1250) {
            //h.addClass('force-up');
            h.removeClass('down').removeClass('up').removeClass('smaller');
            ev.data.previousTop = currentTop;
            return;
        } else {
            //h.removeClass('force-up');
        }
        if (currentTop > 100) {
            h.addClass('smaller');
        } else {
            h.removeClass('smaller');
        }
        if (ev.data.first || currentTop == ev.data.previousTop) {
            ev.data.previousTop = currentTop;
            ev.data.first = false;
            return;
        }
        if (currentTop < ev.data.previousTop) {
            //if (!h.is('.up')) {
                h.removeClass('down').addClass('up');
           // }
        } else if(currentTop > 300) {
            //if (!h.is('.down')) {
                h.removeClass('up').addClass('down');
            //}
        }
        ev.data.previousTop = currentTop;
    }).resize(function() {
        $(this).scroll();
    }).scroll();

    var calc = function(event) {
        var sl = $(this).find('.gallery-slide'), max = $(this).width(), slideWidth = sl.width(), curr = sl.offset().left, delta = event.distX ? event.distX : event.deltaX;
        curr += delta;
        if (curr > 0 || max > slideWidth) {
            curr = 0;
        } else if (curr + slideWidth - max < 0) {
            curr = (slideWidth - max) * (-1);
        }
        if (curr == 0) {
            $(this).find('.prev').hide();
        } else {
            $(this).find('.prev').show();
        }
        if (slideWidth > max) {
            if (curr == (slideWidth - max) * (-1)) {
                $(this).find('.next').hide();
            } else {
                $(this).find('.next').show();
            }
        }
        sl.stop().animate({
            left:  curr + 'px'
        });
    };
    $.each(postStart, function(x, tmp) {
        tmp();
    });
    $('.open-menu').click(function() {
        if (!$('header').is('.show-menu')) {
            $('header').addClass('show-menu').css({
                position : 'absolute',
                top: $(jQuery.browser.webkit ? "body": "html").get(0).scrollTop + 'px'
            });
        } else {
            $('header').removeClass('show-menu').css({
                position: 'fixed',
                top : '0px'
            });
        }
        return false;
    });
    $('#outer').on('click', '.show-menu menu a', function() {

        if ($(this).parent().find('ul').get().length) {
            if (!$(this).parent().is('.opened')) {
                $(this).parent().addClass('opened');
            } else {
                $(this).parent().removeClass('opened');
            }
            return false;
        }
    });
    window.initMap = function() {
        $('.map').each(function() {
            var m = this;
            var address = {
                    lat : parseFloat(m.getAttribute('data-lat')),
                    lng : parseFloat(m.getAttribute('data-lng'))
            };
            if (address.lat == 0 || address.lng == 0) {
                return;
            }

            var myOptions = {
                zoom: 16,
                center: address,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: false
            }
            map = new google.maps.Map(m, myOptions);
            var infoWindow = new google.maps.InfoWindow({
                content : '<div class="text">'+m.getAttribute('data-content') + '</div>'
            });
            var marker = new google.maps.Marker({
                map: map,
                //icon : base_path + 'media/images/marker.png',
                position: address
            });
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });

        });
    };
    if ($('.map').get().length) {
        var s = document.createElement('script');
        s.async = 'async';
        s.defer = 'defer';
        s.type = 'text/javascript';
        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + $('.map:eq(0)').data('key') + '&callback=initMap';
        document.body.appendChild(s);
    }

    /*$('html,body').on('click', '.download-link', function() {
        lightcase.start({
            href : '#window-' + $(this).parents('.files').attr('id')
        });
        return false;
    });*/


});