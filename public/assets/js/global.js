(function () {
    $('#form-login form').on('submit', function (e) {
        lock_screen(true);

        $.ajax({
            url:		make_url('users#login'),
            data:		$(this).serialize(),
            dataType:	'json',
            type:		'post',
            success:	function (result) {
                if(!result.success) {
                    lock_screen(false);

                    format_error(result);
                } else {
                    location.href	= result.redirect;
                }
            }
        });

        e.preventDefault();
    });

    $(window).on('scroll', function () {
        var	_	= $(this);

        if(_.scrollTop() > 241) {
            $('#background-topo2 .bg').show();

            $('#background-topo2 .menu').addClass('floatable-menu');
            $('#background-topo2 .info').addClass('floatable-info');
            $('#background-topo2 .cloud').addClass('floatable-cloud');
        } else {
            $('#background-topo2 .bg').hide();

            $('#background-topo2 .menu').removeClass('floatable-menu');
            $('#background-topo2 .info').removeClass('floatable-info');
            $('#background-topo2 .cloud').removeClass('floatable-cloud');
        }
    });

    window.make_url	= function(to) {
        if (!to)
            return _site_url;

        return _site_url + (_rewrite_enabled ? '' : '/index.php') + '/' + to.split('#').join('/');
    };

    window.image_url	= function (to) {
        return _site_url + '/assets/images/' + to;
    };

    window.resource_url	= function (to) {
        return _site_url + '/assets/' + to;
    };

    window.absolute_url	= function (to) {
        return _site_url + '/' + to;
    };

    window.lock_screen	= function (show) {
        if(show) {
            var d	= $(document.createElement('DIV')).addClass('screen-lock');
            var dd	= $(document.createElement('DIV')).addClass('screen-lock-text');

            dd.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> Aguarde...');

            $(document.body).append(d, dd).css('overflow', 'hidden');

            if(!window.has_screen_lock_callback) {
                window.has_screen_lock_callback	= true;

                $(window).on('resize', function () {
                    $('.screen-lock')
                        .css('width', $(window).width())
                        .css('height', $(window).height());
                });
            }
        } else {
            $(document.body).css('overflow', 'auto');
            $('.screen-lock, .screen-lock-text').remove();
        }
    };

    window.format_error	=	function (result) {
        var errors	= [];
        var	win		= bootbox.dialog({message: '...', buttons: [
            {
                'label': 'Fechar'
            }
        ]});

        (result.errors || result.messages).forEach(function (error) {
            errors.push('<li>&bull; ' + error + '</li>');
        });

        $('.bootbox-body', win).html('<h4>Os seguintes erros impediram de salvar os dados atuais:</h4><ul>' + errors.join('') + '</ul>')
        $('.modal-body', win).css('border-top', 'solid 6px #F00');
    };

    window.generate_tooltips = function() {
        $('[data-toggle="tooltip"]').tooltip({html: true});
        $('[data-toggle="popover"]').popover({html: true});
    }

    $('.technique-popover, .requirement-popover, .shop-item-popover').each(function () {
        var placement = $(this).data('placement');
        $(this).popover({
			trigger:	'manual',
			content:	function () {
				return $($(this).data('source')).html();
			},
			html:		true,
            placement:  'auto ' + placement
		}).on("mouseenter", function () {
		    var _this = this;
		    $(this).popover("show");
		    $(this).siblings(".popover").on("mouseleave", function () {
		        $(_this).popover('hide');
		    });
		}).on("mouseleave", function () {
		    var _this = this;
		    setTimeout(function () {
		        if (!$(".popover:hover").length) {
		            $(_this).popover("hide")
		        }
		    }, 100);
		});
    });

    window.exp_bar_width	= function (v, m ,w) {
        var	r = (w / m) * v;

        return (r > w ? w : r);
    };

    window.fill_exp_bar =	function (target, value, max, text) {
        if(!text) {
            text	= value;
        }

        var	target	= $(target);
        var	width	= exp_bar_width(value, max, target.width());

        if(value == 0) {
            width	= 0;
        }

        $('.fill', target).animate({
            width: width
        });

        $('.text', target).html(text);
    };

    $('.mr-debug-window .title').on('click', function () {
        $('.mr-debug-window').toggleClass('mr-debug-window-expanded');
    });

    $('.mr-debug-window .mr-sql-trace').on('click', function () {
        bootbox.alert($('#mr-sql-trace-' + $(this).data('id')).html());
    });

    window.jalert	= function (msg, ok_callback, options) {
        options	= options || {};

        var	win		= bootbox.dialog({message: msg, buttons: [
            {
                'label':	'Fechar',
                callback:	function () {
                    if(ok_callback) {
                        ok_callback.apply(null, []);
                    }
                }
            }
        ]});

        if(options.texturize) {
            $('.modal-dialog', win).addClass('pattern-container');
            $('.modal-content', win).addClass('with-pattern');
        }
    };

    window.jconfirm	= function (msg, ok_callback, cancel_callback, options) {
        options	= options || {};

        var	win		= bootbox.dialog({message: msg, buttons: [
            {
                'label':	'Fechar',
                callback:	function () {
                    if(cancel_callback) {
                        cancel_callback.apply(null, []);
                    }
                }
            }, {
                'label':	'Ok',
                callback:	function () {
                    if(ok_callback) {
                        ok_callback.apply(null, []);
                    }
                }
            }
        ]});

        if(options.texturize) {
            $('.modal-dialog', win).addClass('pattern-container');
            $('.modal-content', win).addClass('with-pattern');
        }
    };

    var ___timers = [];
    window.create_timer	= function(h, m, s, t, f, identifier, change_title) {
        var title	= document.title;
        var _t		= setInterval(function () {
            s--;

            if(s <= 0 && m <= 0 && h <= 0) {
                clearInterval(_t);

                if(!f) {
                    location.reload();
                    return;
                } else {
                    f.apply();
                }
            }

            if(s <= 0) {
                s = 59;
                m--;

                if(m <= 0 && h > 0) {
                    h--;
                    m = 59;
                }
            }

            if(t instanceof Array) {
                for(var ii in t) {
                    $("." + t[ii]).html(
                        (h < 10 ? "0" + h : h) + ":" + (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s)
                    );
                }
            } else {
                var	timer	= (h < 10 ? "0" + h : h) + ":" + (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);

                if(change_title) {
                    document.title	= '[' + timer + '] ' + title;
                }

                $("." + t).html(timer);
            }
        }, 1000);

        if(!identifier) {
            ___timers.push(_t);
        } else {
            ___timers[identifier]	= _t;
        }
    };

    window.clear_timer	= function(id) {
        clearInterval(___timers[id]);
    };

    window.clear_timers	= function() {
        for(var i in ___timers) {
            clearInterval(___timers[i]);
        }

        ___timers = [];
    };

    window.character_exp	= function (exp, max, level) {
        var	width	= parseInt($('.top-progress').width());
        var	size	= (width / max) * exp;

        if(size > width) size	= width;

        $('.top-expbar-container .level .number').html(level);
        $('.top-progress-player .fill').animate({width: size});
        $('.top-progress-player .light').animate({marginLeft: size + 50});
        $('.top-progress-player .text').html(highamount(exp) + ' / ' + highamount(max));
    };

    window.default_bar_change	= function (val, max, target) {
        var	width	= parseInt(target.width());
        var	size	= (width / max) * val;

        if(size > width) size	= width;

        $('.fill', target).animate({width: size});
        $('.text', target).html(highamount(val) + ' / ' + highamount(max));
    };

    window.character_stats	= function (params) {
        if(typeof(params.life) != 'undefined') {
            $('#background-topo2 .life .c').html(highamount(params.life));
        }

        if(typeof(params.max_life) != 'undefined') {
            $('#background-topo2 .life .m').html(highamount(params.max_life));
        }

        if(typeof(params.mana) != 'undefined') {
            $('#background-topo2 .mana .c').html(highamount(params.mana));
        }

        if(typeof(params.max_mana) != 'undefined') {
            $('#background-topo2 .mana .m').html(highamount(params.max_mana));
        }

        if(typeof(params.stamina) != 'undefined') {
            $('#background-topo2 .stamina .c').html(highamount(params.stamina));
        }

        if(typeof(params.max_stamina) != 'undefined') {
            $('#background-topo2 .stamina .m').html(highamount(params.max_stamina));
        }

        if(typeof(params.currency) != 'undefined') {
            $('#background-topo2 .currency').html(highamount(params.currency));
        }
    };

    window.$id = function() {
        return 'id-' + parseInt(Math.random() * 512384).toString(16) + '-' + parseInt(Math.random() * 512384).toString(16) + '-' + parseInt(Math.random() * 512384).toString(16) + '-' + parseInt(Math.random() * 512384).toString(16);
    }

    window.highamount	= function (number, decimals, dec_point, thousands_sep) {
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}
    
    if (!localStorage.alertCookies) {
        $(".box-cookies").removeClass('hide');
    }

    generate_tooltips();

    function acceptCookies() {
        $(".box-cookies").hide();

        localStorage.setItem("alertCookies", "accept");
    };

    var btnCookies = $(".btn-cookies");
    btnCookies.on('click', acceptCookies);
  })();