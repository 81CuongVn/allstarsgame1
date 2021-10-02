(function () {
    var __chat              = $('#chat');
    var __chat_register     = __chat.data('register');
    var __chat_universal    = __chat.data('universal') || false;

    var has_type		= false;
    var	channel			= 'world';
    var	real_channel	= 'world';
    var	pvt_dest		= 0;
    var	last_pvt_index	= 0;
    var	trigger_pvt		= false;
    var pvt_data		= null;
    var last_msg		= null;
    var blocked			= [];
    var pm_total		= 0;
    var chat_max_length	= 120;

    window.chat_embeds	= [];
    window.chat_decoded	= [];

    function resizeSelector() {
        var	tw	= $('.selector-trigger', __chat).outerWidth() + 15;
        $('input[name=message]', __chat).css({
            paddingLeft: tw
        });
    }

    function diffInSecs(d1, d2) {
        var diff		= d2 - d1,
            sign		= diff < 0 ? -1 : 1,
            milliseconds,
            seconds,
            minutes,
            hours,
            days;

        diff	/= sign;
        diff	= (diff-(milliseconds=diff%1000))/1000;
        diff	= (diff-(seconds=diff%60))/60;
        diff	= (diff-(minutes=diff%60))/60;
        days	= (diff-(hours=diff%24))/24;

        return seconds;
    }
    function timeSince(date, nowDate = Date.now(), rft = new Intl.RelativeTimeFormat(undefined, { numeric: "auto" })) {
        const SECOND	= 1000;
        const MINUTE	= 60	* SECOND;
        const HOUR		= 60	* MINUTE;
        const DAY		= 24	* HOUR;
        const WEEK		= 7		* DAY;
        const MONTH		= 30	* DAY;
        const YEAR		= 365	* DAY;
        const intervals	= [
            { ge: YEAR,		divisor: YEAR,		unit: 'year' },
            { ge: MONTH,	divisor: MONTH,		unit: 'month' },
            { ge: WEEK,		divisor: WEEK,		unit: 'week' },
            { ge: DAY,		divisor: DAY,		unit: 'day' },
            { ge: HOUR,		divisor: HOUR,		unit: 'hour' },
            { ge: MINUTE,	divisor: MINUTE,	unit: 'minute' },
            { ge: SECOND,	divisor: SECOND,	unit: 'seconds' },
            { ge: 0,		divisor: 1,			text: 'agora' }
        ];
        const now		= typeof nowDate === 'object' ? nowDate.getTime() : new Date(nowDate).getTime();
        const diff		= now - (typeof date === 'object' ? date : new Date(date)).getTime();
        const diffAbs	= Math.abs(diff);
        for (const interval of intervals) {
            if (diffAbs >= interval.ge) {
                const x			= Math.round(Math.abs(diff) / interval.divisor);
                const isFuture	= diff < 0;
                return interval.unit ? rft.format(isFuture ? x : -x, interval.unit) : interval.text;
            }
        }
    }

    // Init Socket.IO
    var __chat_socket	= io.connect(_chat_server);

    // On connect error
    __chat_socket.on('error', function () {
        $('.messages', __chat).html(`<li style="padding: 10px">
            Ocorreu um problema ao conectar ao chat.<br /><br />
            Você pode ter algum firewall (isso inclui programas anti-hack de jogos on-line) ou anti-vírus bloqueando o chat.<br /><br />
            Se sua rede está conectada através de um proxy, o proxy pode estar bloqueando as conexões ou não suporta conexões via websocket
        </li>`);
    });

    // On connect
    __chat_socket.on('connect', function () {
        console.log('Chat service connected!');

        __chat_socket.emit('register', {
            data: __chat_register
        });

        $('.messages', __chat).empty();
    });

    __chat_socket.on('blocked-broadcast', function (data) {
        blocked	= data;
    });
    __chat_socket.on('pvt-broadcast', function (data) {
        var	container	= $('.chat-pvt .r');

        pvt_data	= data;

        if (!container.length) {
            if (!data.length) {
                return;
            }

            var	l	= $(document.createElement('DIV')).addClass('l');
            var	r	= $(document.createElement('DIV')).addClass('r');
            var	c	= $(document.createElement('DIV')).addClass('chat-pvt');

            c.append(l, r);
            container	= r;

            $(document.body).append(c);

            c.on('click', function () {
                function dispatch_pvt_read(id) {
                    __chat_socket.emit('pvt-was-read', {index: id});
                }

                if (this.shown) {
                    $('.reply-box').remove();
                    this.shown	= false;

                    return;
                }

                this.shown			= true;
                var	_this			= this;

                var	msg_container	= $(document.createElement('DIV')).addClass('reply-box');
                var	msg_reply		= $(document.createElement('A')).html('Responder').addClass('reply');
                var	msg_next		= $(document.createElement('A')).html(pvt_data.length == 1 ? 'Fechar' : 'Próxima').addClass(pvt_data.length == 1 ? 'close-pv' : 'next');
                var	msg_from		= $(document.createElement('SPAN')).html(pvt_data[0].from).addClass('from');
                var	msg_text		= $(document.createElement('SPAN')).html(pvt_data[0].message).addClass('text');

                msg_reply.on('click', function () {
                    $('.selector-trigger', __chat)
                        .html(pvt_data[0].from)[0].shown = false;

                    channel		= 'private';
                    pvt_dest	= pvt_data[0].id;

                    $('input[name=message]', __chat).focus();
                    resizeSelector();
                });

                if (pvt_data.length == 1) {
                    msg_next.on('click', function () {
                        dispatch_pvt_read(last_pvt_index);

                        msg_container.remove();
                        c.remove();
                    });
                } else {
                    msg_next.on('click', function () {
                        msg_reply.remove();
                        msg_next.remove();
                        msg_from.remove();
                        msg_text.remove();

                        // No próximo broadcast ele recarrega =)
                        trigger_pvt	= true;
                        _this.shown	= false;

                        msg_container.append('<div class="wait">Aguarde...</div>');

                        dispatch_pvt_read(last_pvt_index);
                        pm_total--;
                    });
                }


                msg_container.append(msg_from, msg_text, msg_reply, msg_next);
                $(document.body).append(msg_container);
            });
        }

        container.html(data.length);

        if (data.length > pm_total) {
            pm_total	= data.length;
            $(document.body).append('<audio autoplay><source src="' + resource_url('media/pm.mp3') + '" type="audio/mp3" /></audio>');
        }

        if (!data.length && container.length) {
            pm_total	= 0;

            container.remove();
        }

        last_pvt_index	= data[0].index;

        if (trigger_pvt) {
            $('.reply-box').remove();

            if (data.length) {
                container.trigger('click');
            }

            trigger_pvt	= false;
        }

        if (!parseInt($.cookie('chat_show'))) {
            $('.chat-pvt').css({bottom: 30});
        } else {
            $('.chat-pvt').css({bottom: 340});
        }
    });

    __chat_socket.on('broadcast', function (data) {
        if (data.decodes) {
            data.decodes.forEach(function (decoded) {
                chat_decoded[decoded[0]]	= decoded[1];
            });
        }

        if (data.channel == 'system' || data.channel == 'warn') {
            var $message	= `<li class="chat-message chat-${data.channel}">
                <div style="text-transform: uppercase;">Aviso do sistema</div>
                <p style="font-weight: normal;">${data.message}</p>
            </li>`;
            $('.messages', __chat).append($message)

            return;
        }

        // GLobal user block -->
            var is_blocked	= false;

            blocked.forEach(function (id) {
                if (parseInt(data.user_id) == parseInt(id)) {
                    is_blocked	= true;
                }
            });

            if (is_blocked) {
                return;
            }
        // <--

        var $date	= timeSince(new Date(data.when));

        var $from		= data.id == _current_player ? 'me' : 'friend';
        var $color		= (data.color ? 'color: ' + data.color + '!important' : '');
        var $message	= `<li class="message-item ${$from} chat-${data.channel} ${(data.gm ? 'chat-gm' : '')}" ${(!(data.channel == real_channel) ? 'style="display: none;"' : '')}>
            <img src="${data.avatar}" alt="${data.from}" />
            <div class="content">
                <div class="message">
                    <div class="bubble">
                        <span style="${$color}" class="chat-user" data-id="${data.id}" data-from="${data.from}">
                            ${data.icon}${data.from}
                        </span>
                        <p>${data.message}</p>
                    </div>
                    <span class="chat-when" data-when="${data.when}">
                        ${$date}
                    </span>
                </div>
            </div>
        </li>`;
        $('.messages', __chat).append($message);

        $('.messages .chat-user', __chat).each(function() {
            if (this.with_callback) {
                return;
            }

            this.with_callback	= true;
            var	_				= $(this);

            _.on('click', function() {
                var $from = $(this).data('from');

                if (channel == 'block') {
                    $('input[name=message]', __chat).val($from).focus();

                    return;
                }

                $('.selector-trigger', __chat)
                    .html($from)[0].shown = false;

                $('.selector ul', __chat).animate({opacity: 0}, function () {
                    $(this).hide()
                });

                channel		= 'private';
                pvt_dest	= _.data('id');

                $('input[name=message]', __chat).focus();
                resizeSelector();
            });
        });

        if (has_type) {
            $('.messages', __chat).scrollTop(1000000);

            has_type	= false;
        }
        if ($('.auto-scroll:checked', __chat).length) {
            $('.messages', __chat).scrollTop(1000000);
        }
    });

    $(document).ready(function(e) {
        $('input[name=message]', __chat).on('keyup', function(e) {
            var	message	= $(this);

            if (e.keyCode == 13 && this.value) {
                if (!__chat_universal) {
                    var now	= new Date();
                    if (last_msg && diffInSecs(last_msg, now) < 5) {
                        return;
                    }
                }

                var broadcast_data	= {
                    message:	this.value,
                    channel:	channel,
                    dest:		pvt_dest,
                    embeds: 	chat_embeds
                };

                __chat_socket.emit('message', broadcast_data);

                this.value	= '';
                chat_embeds	= [];
                has_type	= true;

                if (channel == 'private') {
                    $('.selector ul li', __chat).each(function () {
                        if ($(this).data('channel') == real_channel) {
                            $(this).trigger('click');
                        }
                    });
                } else {
                    if (!__chat_universal) {
                        message.attr('disabled', 'disabled');
                        last_msg	= now;
                        var _iv1	= setInterval(function () {
                            var	now	= new Date();

                            if (last_msg && diffInSecs(last_msg, now) < 55) {
                                message.attr('placeholder', 'Aguarde ' + (5 - diffInSecs(last_msg, now)) + ' segundo(s)');
                            } else {
                                message.removeAttr('disabled').attr('placeholder', '');
                                clearInterval(_iv1);
                            }
                        }, 1000);
                    }
                }
            }

            if (e.keyCode == 32) {
                $('.selector ul li', __chat).each(function () {
                    var _	= $(this);

                    if (message.val().match(new RegExp('\^/' + _.data('cmd')))) {
                        _.trigger('click');

                        message.val('');
                    }
                });

                if (message.val().match(/^@[^\s]+/)) {
                    var	dest	= message.val().replace(/[@<>]/img, '');

                    $('.selector-trigger', __chat).html(dest)[0].shown = false;

                    message.val('');

                    channel		= 'private';
                    pvt_dest	= dest;

                    resizeSelector();
                }

                if (message.val().match('^/block')) {
                    $('.selector-trigger', __chat).html('Bloquear')[0].shown = false;

                    channel	= 'block';

                    message.val('');
                    resizeSelector();
                }
            }
        }).on('focus', function () {
            $('#as', __chat).stop().animate({opacity: 0});
        }).on('blur', function () {
            $('#as', __chat).stop().animate({opacity: 1});
        }).on('pvt-switch', function (e, dest) {
            $('.selector-trigger', __chat).html(dest)[0].shown = false;

            channel		= 'private';
            pvt_dest	= dest;

            resizeSelector();
        });

        $('.selector ul li', __chat).on('click', function () {
            $('.selector-trigger', __chat).html(this.innerHTML)[0].shown = false;
            $('.selector ul', __chat).animate({opacity: 0}, function () {
                $(this).hide()
            });

            channel			= $(this).data('channel');
            real_channel	= channel;

            if (!__chat_universal) {
                $('#message', __chat).attr('maxlength', chat_max_length);
            }

            $('.messages li', __chat).hide();

            $.cookie('chat_channel', channel);

            $('.messages .chat-' + channel, __chat).show();
            $('.messages .chat-warn', __chat).show();
            $('.messages .chat-system', __chat).show();

            $('input[name=message]', __chat).focus();
            resizeSelector();
        });

        $('.selector-trigger', __chat).on('click', function () {
            if (!this.shown) {
                $('.selector ul', __chat).show().animate({opacity: 1});

                this.shown	= true;
            } else {
                $('.selector ul', __chat).animate({opacity: 0}, function () { $(this).hide() });

                this.shown	= false;
            }
        });

        if ($.cookie('chat_channel')) {
            var	current		= $.cookie('chat_channel');
            var was_found	= false;

            $('.selector ul li', __chat).each(function () {
                var _		= $(this);

                if (_.data('channel') == current) {
                    _.trigger('click');

                    was_found	= true;
                }
            });

            if (!was_found) {
                $('.selector ul li', __chat).each(function () {
                    var _		= $(this);

                    if (_.data('channel') == 'world') {
                        _.trigger('click');
                    }
                });
            }
        } else {
            $('.selector ul li', __chat).each(function () {
                var _		= $(this);

                if (_.data('channel') == 'world') {
                    _.trigger('click');
                }
            });
        }
    });

    var __att_when	= setInterval(function () {
        var chatTimers = $('.message-item > .content > .message > .chat-when');
        chatTimers.each(function (key, value) {
            var $elem	= $(value);
            var $when	= $elem.data('when');
            if ($when !== 'undefined') {
                var $date	= timeSince(new Date($when));
                $elem.html($date);
            }
        });
    }, 2500);

    var	__pvt_iv	= setInterval(function() {
        __chat_socket.emit('pvt-query');
    }, 2000)

    var	__block_iv	= setInterval(function() {
        __chat_socket.emit('blocked-query');
    }, 2000)

    $('.title', __chat).on('click', function () {
        if (parseInt($.cookie('chat_show'))) {
            __chat.animate({height: 35});
            $('.chat-pvt').animate({bottom: 30});

            $.cookie('chat_show', 0);
        } else {
            __chat.animate({height: 350});
            $('.chat-pvt').animate({bottom: 340});

            $.cookie('chat_show', 1);
        }

        resizeSelector();
    });

    $('.auto-scroll', __chat).on('click', function () {
        if (this.checked) {
            $.cookie('chat_as', 1);
        } else {
            $.cookie('chat_as', 0);
        }
    });

    if (!parseInt($.cookie('chat_show'))) {
        __chat.css('height', 35);
    }

    if (parseInt($.cookie('chat_as'))) {
        $('.auto-scroll', __chat)[0].checked = true;
    }
})();
