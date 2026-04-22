/*
 * Licensed by w3des.net
 */
$(function () {
    var bind_upload = function() {

        $('.gallery').each(function() {
            if ($(this).data('initialized')) {
                return;
            }
            var me = $(this);
            me.data('initialized', true);
            var list = me.find('div[data-prototype]');
            var uploader = $('<input type="file" multiple="multiple" name="files[]" style="display: none" />').appendTo('body');
            list.data('count', list.find('>div').get().length);
            var install = null;
            install = function() {
                uploader.fileupload({
                    dataType:'json',
                    autoUpload : true,
                    done : function(e, data) {
                        data.context.remove();
                        var it = $(list.data('prototype').replace(/__name__/g, list.data('count')).replace(/__thmb__/g, data.result.files[0].thmb));
                        it.find('[name*=photo],[name*=file]').attr('value', data.result.files[0].path);
                        it.find('[name*=name]').attr('value', data.result.files[0].origName);
                        list.append(it);
                        list.data('count', list.data('count')+1);
                        list.sortable('destroy').sortable({
                            handle: ".ph",
                            stop : function(e) {
                                recalc_gallery_pos($(e.target));
                            }
                        });
                        recalc_gallery_pos(list);

                        uploader.remove();
                        uploader =  $('<input type="file" multiple="multiple" name="files[]" style="display: none" />').appendTo('body');
                        install();
                    },
                    sequentialUploads: true,
                    url : me.find('.upload-button').data('url'),
                    formData : {
                        dir: me.find('.upload-button').data('dir')
                    },
                    singleFileUploads : true,
                    add: function (e, data) {
                        data.context = $('<div><strong>'+data.files[0].name+'</strong><div /></div>').appendTo(me.find('.progress'));
                        data.context.find('div').progressbar({value : 0});
                        data.submit();
                    }
                });
            }
            install();
            uploader.bind('fileuploadprogress', function (e, data) {
                data.context.find('div').progressbar({
                    value : data.loaded / data.total * 100
                });
            });
            list.sortable({
                handle: ".ph",
                stop : function(e) {
                    recalc_gallery_pos($(e.target));
                }
            });
            me.find('.upload-button').button().click(function() {
                uploader.click();
            });
            list.on('click', '.close', function() {
                $(this).parent().remove();
            })
        });
    }
        bind_upload();
        $('#topBar .user').hover(function () {
                $(this).addClass('openUser');
            }, function () {
                $(this).removeClass('openUser');
            });
        $('#menu > ul > li > a').click(function () {
                if ($(this).attr('href') != '#') {
                    return true;
                }
                if ($(this).parent().hasClass('open')) {
                    return false;
                } else {
                    $('#menu ul ul').slideUp('fast');
                    $('#menu > ul > li ').removeClass('open');
                    $(this).parent().find('> ul').slideDown('fast').parent().addClass('open');
                }
                return false;
            }).append('<span class="l"></span><span class="r"></span>');
        $('input[type=submit]').button();
        $('form').on('click', '.remove-module', function() {
            $(this).parents('.node-module').remove();
            return false;
        });
        var recalc_module_pos = function(container) {
            container.find('.node-module').each(function(index) {
                $(this).find('[pos="module-pos"]').attr('value', index);
            });
        };
        var recalc_gallery_pos = function(container) {
            container.find('.gallery-item').each(function(index) {
                $(this).find('[pos="gallery-pos"]').attr('value', index);
            });
        };
        $('form').on('click', '.add-module', function() {
            var container = $(this).parents('.node-modules');
            var num = parseInt(container.data('count')) + 1;
            container.data('count', num);
            var tmp = $($(this).data('prototype').replace(/_prototype_/g, num));
            var app = container.find('>div').append(tmp);
                $('html, body').animate({
                    scrollTop: tmp.offset().top
                }, 300);

            bind_upload();
            recalc_module_pos(container);
            return false;
        });
        setTimeout(function () {
                $('.alert').hide('slow');
            }, 5000);

        $('#content').css('min-height', $('#sidebar').height() + 100 + 'px');
        if ($('form .group').get().length > 0 ) {
            $('form').prepend('<div id="tab"></div>');
            $('form .group').each(function (i) {
                    if ($(this).is(':visible') && !$(this).attr('mod_id')) {

                        $(this).attr('num', i);
                        $('#tab').append('<span to="' + i + '"' + (i == 0 ? ' class="active"' : '') + '>' + $(this).find('h2').text() + '</span>');
                        if (i == 0) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    } else {
                        $(this).hide();
                    }
                });
            $('#tab').on('click', 'span', click_tab);
            if ($('#form_tabPosition').prop('value') != '') {
                $('#tab [to="' + $('#form_tabPosition').prop('value') + '"]').click();
            } else if (window.location.hash != '' && window.location.hash != '#') {

                $('#tab [to="' + window.location.hash.substr(1) + '"]').click();
            } else {
                if ($('#tab [to]:eq(0)').attr('to') && $('#tab [to]:eq(0)').attr('to').length > 0) {
                    window.location.hash = $('#tab [to]:eq(0)').attr('to');
                }
            }
        }
        $('.node-modules').each(function() {
            $(this).find('>div').sortable({
                handle: "legend",
                placeholder: "portlet-placeholder ui-corner-all",
                stop : function(event, ui) {
                    recalc_module_pos($(event.target));
                }
            });
            recalc_module_pos($(this));
        });

        $('form .group').append('<div class="clearFix"></div>');
            $('.date [type=date]').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        $('input.datetime').each(function() {
            var row = $(this);

            $(this).datetimepicker({
                'dateFormat' : 'yy-mm-dd',
                'timeFormat' : 'HH:mm:ss'
            });
        });
    });
function click_tab () {
    $('#tab span').removeClass('active');
    $('.group').hide();
    $(this).addClass('active');
    $('.group[num=' + $(this).attr('to') + ']').show();
    if ( $(this).attr('to') &&  $(this).attr('to').length > 0) {
        window.location.hash = $(this).attr('to');
    }
    $('#form_tabPosition').prop('value', $(this).attr('to'));
}
function str_replace (search, replace, subject, count) {
    // http://kevin.vanzonneveld.net
    // + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + improved by: Gabriel Paderni
    // + improved by: Philip Peterson
    // + improved by: Simon Willison (http://simonwillison.net)
    // + revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // + bugfixed by: Anton Ongson
    // + input by: Onno Marsman
    // + improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + tweaked by: Onno Marsman
    // + input by: Brett Zamir (http://brett-zamir.me)
    // + bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + input by: Oleg Eremeev
    // + improved by: Brett Zamir (http://brett-zamir.me)
    // + bugfixed by: Oleg Eremeev
    // % note 1: The count parameter must be passed as a string in order
    // % note 1: to find a global variable in which the result will be given
    // * example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
    // * returns 1: 'Kevin.van.Zonneveld'
    // * example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name},
    // lars');
    // * returns 2: 'hemmo, mars'
    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0, f = [].concat(search), r = [].concat(replace), s = subject, ra = Object.prototype.toString.call(r) === '[object Array]', sa = Object.prototype.toString
        .call(s) === '[object Array]';
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }

    for (i = 0, sl = s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j = 0, fl = f.length; j < fl; j++) {
            temp = s[i] + '';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length - s[i].length) / f[j].length;
            }
        }
    }
    return sa ? s : s[0];
}
function date (format, timestamp) {
    // http://kevin.vanzonneveld.net
    // + original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
    // + parts by: Peter-Paul Koch (http://www.quirksmode.org/js/beat.html)
    // + improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + improved by: MeEtc (http://yass.meetcweb.com)
    // + improved by: Brad Touesnard
    // + improved by: Tim Wiel
    // + improved by: Bryan Elliott
    //
    // + improved by: Brett Zamir (http://brett-zamir.me)
    // + improved by: David Randall
    // + input by: Brett Zamir (http://brett-zamir.me)
    // + bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + improved by: Brett Zamir (http://brett-zamir.me)
    // + improved by: Brett Zamir (http://brett-zamir.me)
    // + improved by: Theriault
    // + derived from: gettimeofday
    // + input by: majak
    // + bugfixed by: majak
    // + bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // + input by: Alex
    // + bugfixed by: Brett Zamir (http://brett-zamir.me)
    // + improved by: Theriault
    // + improved by: Brett Zamir (http://brett-zamir.me)
    // + improved by: Theriault
    // + improved by: Thomas Beaucourt (http://www.webapp.fr)
    // + improved by: JT
    // + improved by: Theriault
    // + improved by: RafaÅ‚ Kukawski (http://blog.kukawski.pl)
    // + bugfixed by: omid (http://phpjs.org/functions/380:380#comment_137122)
    // + input by: Martin
    // + input by: Alex Wilson
    // + bugfixed by: Chris (http://www.devotis.nl/)
    // % note 1: Uses global: php_js to store the default timezone
    // % note 2: Although the function potentially allows timezone info (see
    // notes), it currently does not set
    // % note 2: per a timezone specified by date_default_timezone_set().
    // Implementers might use
    // % note 2: this.php_js.currentTimezoneOffset and
    // this.php_js.currentTimezoneDST set by that function
    // % note 2: in order to adjust the dates in this function (or our other
    // date functions!) accordingly
    // * example 1: date('H:m:s \\m \\i\\s \\m\\o\\n\\t\\h', 1062402400);
    // * returns 1: '09:09:40 m is month'
    // * example 2: date('F j, Y, g:i a', 1062462400);
    // * returns 2: 'September 2, 2003, 2:26 am'
    // * example 3: date('Y W o', 1062462400);
    // * returns 3: '2003 36 2003'
    // * example 4: x = date('Y m d', (new Date()).getTime()/1000);
    // * example 4: (x+'').length == 10 // 2009 01 09
    // * returns 4: true
    // * example 5: date('W', 1104534000);
    // * returns 5: '53'
    // * example 6: date('B t', 1104534000);
    // * returns 6: '999 31'
    // * example 7: date('W U', 1293750000.82); // 2010-12-31
    // * returns 7: '52 1293750000'
    // * example 8: date('W', 1293836400); // 2011-01-01
    // * returns 8: '52'
    // * example 9: date('W Y-m-d', 1293974054); // 2011-01-02
    // * returns 9: '52 2011-01-02'
    var that = this, jsdate, f, formatChr = /\\?([a-z])/gi, formatChrCb,
    // Keep this here (works, but for code commented-out
    // below for file size reasons)
    // , tal= [],
    _pad = function (n, c) {
        if ((n = n + '').length < c) {
            return new Array((++c) - n.length).join('0') + n;
        }
        return n;
    }, txt_words = ["Sun", "Mon", "Tues", "Wednes", "Thurs", "Fri", "Satur", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    formatChrCb = function (t, s) {
        return f[t] ? f[t]() : s;
    };
    f = {
        // Day
        d : function () { // Day of month w/leading 0; 01..31
            return _pad(f.j(), 2);
        },
        D : function () { // Shorthand day name; Mon...Sun
            return f.l().slice(0, 3);
        },
        j : function () { // Day of month; 1..31
            return jsdate.getDate();
        },
        l : function () { // Full day name; Monday...Sunday
            return txt_words[f.w()] + 'day';
        },
        N : function () { // ISO-8601 day of week; 1[Mon]..7[Sun]
            return f.w() || 7;
        },
        S : function () { // Ordinal suffix for day of month; st, nd, rd, th
            var j = f.j();
            return j < 4 | j > 20 && ['st', 'nd', 'rd'][j % 10 - 1] || 'th';
        },
        w : function () { // Day of week; 0[Sun]..6[Sat]
            return jsdate.getDay();
        },
        z : function () { // Day of year; 0..365
            var a = new Date(f.Y(), f.n() - 1, f.j()), b = new Date(f.Y(), 0, 1);
            return Math.round((a - b) / 864e5) + 1;
        },

        // Week
        W : function () { // ISO-8601 week number
            var a = new Date(f.Y(), f.n() - 1, f.j() - f.N() + 3), b = new Date(a.getFullYear(), 0, 4);
            return _pad(1 + Math.round((a - b) / 864e5 / 7), 2);
        },

        // Month
        F : function () { // Full month name; January...December
            return txt_words[6 + f.n()];
        },
        m : function () { // Month w/leading 0; 01...12
            return _pad(f.n(), 2);
        },
        M : function () { // Shorthand month name; Jan...Dec
            return f.F().slice(0, 3);
        },
        n : function () { // Month; 1...12
            return jsdate.getMonth() + 1;
        },
        t : function () { // Days in month; 28...31
            return (new Date(f.Y(), f.n(), 0)).getDate();
        },

        // Year
        L : function () { // Is leap year?; 0 or 1
            var j = f.Y();
            return j % 4 == 0 & j % 100 != 0 | j % 400 == 0;
        },
        o : function () { // ISO-8601 year
            var n = f.n(), W = f.W(), Y = f.Y();
            return Y + (n === 12 && W < 9 ? 1 : n === 1 && W > 9 ? -1 : 0);
        },
        Y : function () { // Full year; e.g. 1980...2010
            return jsdate.getFullYear();
        },
        y : function () { // Last two digits of year; 00...99
            return (f.Y() + "").slice(-2);
        },

        // Time
        a : function () { // am or pm
            return jsdate.getHours() > 11 ? "pm" : "am";
        },
        A : function () { // AM or PM
            return f.a().toUpperCase();
        },
        B : function () { // Swatch Internet time; 000..999
            var H = jsdate.getUTCHours() * 36e2,
            // Hours
            i = jsdate.getUTCMinutes() * 60,
            // Minutes
            s = jsdate.getUTCSeconds(); // Seconds
            return _pad(Math.floor((H + i + s + 36e2) / 86.4) % 1e3, 3);
        },
        g : function () { // 12-Hours; 1..12
            return f.G() % 12 || 12;
        },
        G : function () { // 24-Hours; 0..23
            return jsdate.getHours();
        },
        h : function () { // 12-Hours w/leading 0; 01..12
            return _pad(f.g(), 2);
        },
        H : function () { // 24-Hours w/leading 0; 00..23
            return _pad(f.G(), 2);
        },
        i : function () { // Minutes w/leading 0; 00..59
            return _pad(jsdate.getMinutes(), 2);
        },
        s : function () { // Seconds w/leading 0; 00..59
            return _pad(jsdate.getSeconds(), 2);
        },
        u : function () { // Microseconds; 000000-999000
            return _pad(jsdate.getMilliseconds() * 1000, 6);
        },

        // Timezone
        e : function () { // Timezone identifier; e.g. Atlantic/Azores, ...
            // The following works, but requires inclusion of the very large
            // timezone_abbreviations_list() function.
            /*
             * return that.date_default_timezone_get();
             */
            throw 'Not supported (see source code of date() for timezone on how to add support)';
        },
        I : function () { // DST observed?; 0 or 1
            // Compares Jan 1 minus Jan 1 UTC to Jul 1 minus Jul 1 UTC.
            // If they are not equal, then DST is observed.
            var a = new Date(f.Y(), 0),
            // Jan 1
            c = Date.UTC(f.Y(), 0),
            // Jan 1 UTC
            b = new Date(f.Y(), 6),
            // Jul 1
            d = Date.UTC(f.Y(), 6); // Jul 1 UTC
            return 0 + ((a - c) !== (b - d));
        },
        O : function () { // Difference to GMT in hour format; e.g. +0200
            var tzo = jsdate.getTimezoneOffset(), a = Math.abs(tzo);
            return (tzo > 0 ? "-" : "+") + _pad(Math.floor(a / 60) * 100 + a % 60, 4);
        },
        P : function () { // Difference to GMT w/colon; e.g. +02:00
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2));
        },
        T : function () { // Timezone abbreviation; e.g. EST, MDT, ...
            // The following works, but requires inclusion of the very
            // large timezone_abbreviations_list() function.
            /*
             * var abbr = '', i = 0, os = 0, default = 0; if (!tal.length) { tal =
             * that.timezone_abbreviations_list(); } if (that.php_js &&
             * that.php_js.default_timezone) { default =
             * that.php_js.default_timezone; for (abbr in tal) { for (i=0; i <
             * tal[abbr].length; i++) { if (tal[abbr][i].timezone_id ===
             * default) { return abbr.toUpperCase(); } } } } for (abbr in tal) {
             * for (i = 0; i < tal[abbr].length; i++) { os =
             * -jsdate.getTimezoneOffset() * 60; if (tal[abbr][i].offset === os) {
             * return abbr.toUpperCase(); } } }
             */
            return 'UTC';
        },
        Z : function () { // Timezone offset in seconds (-43200...50400)
            return -jsdate.getTimezoneOffset() * 60;
        },

        // Full Date/Time
        c : function () { // ISO-8601 date.
            return 'Y-m-d\\TH:i:sP'.replace(formatChr, formatChrCb);
        },
        r : function () { // RFC 2822
            return 'D, d M Y H:i:s O'.replace(formatChr, formatChrCb);
        },
        U : function () { // Seconds since UNIX epoch
            return jsdate / 1000 | 0;
        }
    };
    this.date = function (format, timestamp) {
        that = this;
        jsdate = (timestamp == null ? new Date() : // Not provided
            (timestamp instanceof Date) ? new Date(timestamp) : // JS Date()
                new Date(timestamp * 1000) // UNIX timestamp (auto-convert to
                                            // int)
        );
        return format.replace(formatChr, formatChrCb);
    };
    return this.date(format, timestamp);
}
