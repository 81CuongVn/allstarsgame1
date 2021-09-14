(function () {
    var results = $('#character-attacks-unique');
    var results_theme = $('#theme-list-ajax');
    var form = $('#f-create-character');
    var creation_current_character = null;

    $('#character-data .themes-uniques').on('click', function () {
        var _ = $(this);
        $.ajax({
            url: make_url('guides#themes_list'),
            data: $(this).serialize(),
            type: 'post',
            data: { id: _.data('id') },
            success: function (result) {
                lock_screen(false);
                results_theme.html(result);

				update_tooltips();
            }
        });
    });

    $('#theme-list-ajax').on('click', '.character-uniques', function () {
        var _ = $(this);

        $('#theme-list-ajax .character-uniques').removeClass('selected');
        _.addClass('selected');

    });

    $('.character-paginator').on('click', '.page', function () {
        var _ = $(this);
        var parent = _.parent();
        var target = $(parent.data('target-container'));

        $('.page', parent).removeClass('active');
        _.addClass('active');

        $('.page-item', target).hide();
        $('.page-item-' + _.data('page'), target).show();
    });

    $('#theme-list-ajax .character-uniques:first').trigger('click');
    $('#select-player-list-container .player:first').trigger('click');

    $('.character-paginator').each(function () {
        $('.page:first', $(this)).trigger('click');
    });

    // Fragmentos
    $('.fragments_change').on('click', function () {

        lock_screen(true);
        var _ = $(this);

        $.ajax({
            url: make_url('characters#fragments_change'),
            data: { mode: _.data('mode') },
            dataType: 'json',
            type: 'post',
            success: function (result) {
                if (result.success) {
                    location.href = make_url('characters#fragments?message=' + result.message);
                } else {
                    lock_screen(false);
                    format_error(result);
                }
            }
        });
    });

    if (form.length) {
        $('#anime-list').on('click', '.anime', function () {
            var _ = $(this);

            $('#anime-list .anime').removeClass('selected');
            _.addClass('selected');

            $('.anime-characters').hide();
            $('#anime-characters-' + _.data('id')).show();

            $('#anime-characters-' + _.data('id') + ' .character:first').trigger('click');
        });

        $('#anime-character-list').on('click', '.character', function () {
            var _ = $(this);
            var character = _characters[_.data('id')];

            if (_.hasClass('locked')) {
                var win = bootbox.dialog({
                    message: '...', buttons: [
                        {
                            label: 'Fechar',
                            class: 'btn btn-sm btn-default'
                        }
                    ]
                });

                $('.modal-dialog', win).addClass('pattern-container');
                $('.modal-content', win).addClass('with-pattern');

                $.ajax({
                    url: make_url('characters#show_lock_info/' + _.data('id')),
                    data: { show_only: 1, character: creation_current_character },
                    success: function (result) {
                        $('.bootbox-body', win).html(result);

						update_tooltips();
                    }
                });

                win.on('click', '.unlock', function () {
                    lock_screen(true);

                    $.ajax({
                        url: make_url('characters#unlock_character/' + _.data('id')),
                        data: { method: $(this).data('method') },
                        type: 'post',
                        success: function (result) {
                            lock_screen(false);

                            if (result.success) {
                                win.modal('hide');
                                _.removeClass('locked');
                                $('span', _).remove();
                                _.trigger('click');
                            } else {
                                format_error(result);
                            }
                        }
                    });
                });

                return;
            }

            $('#anime-character-list .character').removeClass('selected');
            _.addClass('selected');

            $('#character-info .character').html(character.name);
            $('#character-info .anime').html(_animes[character.anime]);

            $('#anime-character-list .barra-secao, #anime-list .barra-secao').removeClass('barra-secao-1 barra-secao-2 barra-secao-3 barra-secao-4 barra-secao-5 barra-secao-6');
            $('#anime-character-list .barra-secao, #anime-list .barra-secao').addClass('barra-secao-' + character.anime);

            var max = 0;
            creation_current_character = _.data('id');

            if (character.at.for_atk > max) { max = character.at.for_atk };
            if (character.at.for_def > max) { max = character.at.for_def };
            if (character.at.for_crit > max) { max = character.at.for_crit };
            if (character.at.for_abs > max) { max = character.at.for_abs };
            if (character.at.for_prec > max) { max = character.at.for_prec };
            if (character.at.for_init > max) { max = character.at.for_init };

            fill_exp_bar('#character-attributes .for_atk .exp-bar', character.at.for_atk, max);
            fill_exp_bar('#character-attributes .for_def .exp-bar', character.at.for_def, max);
            fill_exp_bar('#character-attributes .for_crit .exp-bar', character.at.for_crit, max);
            fill_exp_bar('#character-attributes .for_abs .exp-bar', character.at.for_abs, max);
            fill_exp_bar('#character-attributes .for_prec .exp-bar', character.at.for_prec, max);
            fill_exp_bar('#character-attributes .for_init .exp-bar', character.at.for_init, max);

            $('#character-profile-image').attr('src', character.profile);
            $('#name-character').html(character.name);

            $('[name=character_id]', form).val(_.data('id'));
            $('[name=character_theme_id]', form).val(_.data('theme-id'));
        });

        $('.faccao', form).on('click', function () {
            $('.faccao', form).removeClass('selected');
            $(this).addClass('selected');

            $('[name=faction_id]', form).val($(this).data('faction'));
        });

        $('.character-paginator').on('click', '.page', function () {
            var _ = $(this);
            var parent = _.parent();
            var target = $(parent.data('target-container'));

            $('.page', parent).removeClass('active');
            _.addClass('active');

            $('.page-item', target).hide();
            $('.page-item-' + _.data('page'), target).show();
        });

        $('#anime-list .anime:first').trigger('click');
        $('.faccao:first', form).trigger('click');
        $('.character-paginator').each(function () {
            $('.page:first', $(this)).trigger('click');
        });

        form.on('submit', function (e) {
            lock_screen(true);

            $.ajax({
                url: make_url('characters#create'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        location.href = make_url('characters#select?created');
                    } else {
                        lock_screen(false);
                        format_error(result);
                    }
                }
            });

            e.preventDefault();
        });

        $('#character-data #change-theme').on('click', function () {
            var win = bootbox.dialog({
                message: '...', buttons: [
                    {
                        label: 'Fechar',
                        class: 'btn btn-sm btn-default'
                    }
                ]
            });

            $('.modal-dialog', win).addClass('pattern-container');
            $('.modal-content', win).addClass('with-pattern');

            $.ajax({
                url: make_url('characters#list_themes'),
                data: { show_only: 1, character: creation_current_character },
                success: function (result) {
                    $('.bootbox-body', win).html(result);

                    _apply_themes_cb(win);
                }
            });
        });
    }
    $('#theme-list-ajax').on('click', '.character-uniques', function () {
        var _ = $(this);

        $('#theme-list-ajax .character-uniques').removeClass('selected');
        _.addClass('selected');

        $('#character_theme_id').val(_.data('id'));
        $('#character-profile-image').attr('src', image_url('profile/' + _.data('character-id') + '/' + _.data('theme-code') + '/1.jpg'));

        $.ajax({
            url: make_url('guides#attacks_list'),
            data: $(this).serialize(),
            type: 'post',
            data: { id: _.data('id'), character_id: _.data('character-id') },
            success: function (result) {
                lock_screen(false);
                results.html(result);

				update_tooltips();
            }
        });
    });
    $('#select-player-list-container').on('click', '.player', function () {
        var	_		= $(this);
        var	player	= _players[_.data('id')];

        $('#select-player-list-container .player').removeClass('selected');
        _.addClass('selected');

        $('#current-player-name').html(player.name);
        $('#current-player-info .anime').html(player.anime);
        $('#current-player-info .faction').html(player.faction);
        $('#current-player-info .level').html(player.level);
        $('#current-player-info .currency').html(player.currency);
        $('#current-player-info .amount').html(player.amount);
        $('#current-player-info .graduation').html(player.graduation);
        $('#current-player-attributes .mana-name').html(player.mana_name);

        fill_exp_bar('#current-player-attributes .bar-life .exp-bar', player.life, player.max_life);
        fill_exp_bar('#current-player-attributes .bar-mana .exp-bar', player.mana, player.max_mana);
        fill_exp_bar('#current-player-attributes .bar-stamina .exp-bar', player.stamina, player.max_stamina);
        var expPercent = Math.floor((player.exp / player.level_exp) * 100);
        fill_exp_bar('#current-player-info .bar-exp .exp-bar', player.exp, player.level_exp, expPercent + '%');

        $('#current-player-image').html('<img src="' + player.profile + '" />');
    });

    $('#select-player-list-container .player:first').trigger('click');
    $('#theme-list-ajax .character-uniques:first').trigger('click');


    $('#current-player-info .remove').on('click', function () {
        bootbox.confirm($(this).data('message'), function (result) {
            if (result) {
                lock_screen(true);

                $.ajax({
                    url: make_url('characters#remove'),
                    data: { id: $('#select-player-list-container .selected').data('id') },
                    type: 'post',
                    dataType: 'json',
                    success: function (result) {
                        if (result.success) {
                            location.href = make_url('characters#select?deleted');
                        } else {
                            lock_screen(false);
                            format_error(result);
                        }
                    }
                });
            }
        });
    });

    $('#current-player-info .play').on('click', function () {
        lock_screen(true);

        $.ajax({
            url: make_url('characters#select'),
            data: { id: $('#select-player-list-container .selected').data('id'), map: $('#select-player-list-container .selected').data('map-id') },
            type: 'post',
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    if ($('#select-player-list-container .selected').data('map-id')) {
                        location.href = make_url('maps#preview');
                    } else {
                        location.href = make_url('characters#status');
                    }
                } else {
                    lock_screen(false);
                    format_error(result);
                }
            }
        });
    });
    $('#theme-view-image').on('click', function () {
        var theme_id = $('#character_theme_id').val();
        var _ = $(this);
        var win = bootbox.dialog({
            message: '...', buttons: [
                {
                    label: 'Fechar',
                    class: 'btn btn-sm btn-default'
                }
            ]
        });

        $('.modal-dialog', win).addClass('pattern-container');
        $('.modal-content', win).addClass('with-pattern');

        $.ajax({
            url: _.data('url'),
            type: 'get',
            data: { theme_id: theme_id },
            success: function (result) {
                $('.bootbox-body', win).html(result);

                // This one is for the images

            }
        });
    });
    $('#current-player-change-image, #current-player-change-theme').on('click', function () {
        var _ = $(this);
        var win = bootbox.dialog({
            message: '...', buttons: [
                {
                    label: 'Fechar',
                    class: 'btn btn-sm btn-default'
                }
            ]
        });

        $('.modal-dialog', win).addClass('pattern-container');
        $('.modal-content', win).addClass('with-pattern');

        $.ajax({
            url: _.data('url'),
            success: function (result) {
                $('.bootbox-body', win).html(result);

                // This one is for the images
                $('.modal-content', win).on('click', '.image, .ultimate-image', function () {
                    win.modal('hide');
                    lock_screen(true);

                    $.ajax({
                        url: make_url('characters#list_images'),
                        type: 'post',
                        data: { id: $(this).data('id') },
                        dataType: 'json',
                        success: function (result) {
                            if (result.success) {
                                location.href = make_url('characters#status');
                            } else {
                                lock_screen(false);
                                format_error(result);
                            }
                        }
                    });

                });

                _apply_themes_cb(win);
            }
        });
    });

    function _apply_themes_cb(win) {
        var theme = $(this).parent();

        $('.theme', win).tooltip({ html: true });

        //cardize('#popup-character-themes #theme-list');

        $('.buy-theme, .use-theme', win).on('click', function () {
            lock_screen(true);

            var data = { theme: $(this).data('theme'), mode: $(this).data('mode'), type: 1 };
            var _this = $(this);

            if (_this.hasClass('buy-theme')) {
                data.buy = 1;
            } else {
                data.use = 1;
            }

            $.ajax({
                url: make_url('characters#list_themes'),
                type: 'post',
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        location.href = make_url('characters#status');
                    } else {
                        lock_screen(false);
                        format_error(result);
                    }
                }
            });
        });

        $('.use-theme', win).on('click', function () {
            lock_screen(true);
        });
    }

    $('#character-change-headline').on('change', function () {
        lock_screen(true);

        $.ajax({
            url: make_url('characters#change_headline'),
            type: 'post',
            data: { headline: $(this).val() },
            dataType: 'json',
            success: function (result) {
                lock_screen(false);

                if (!result.success) {
                    format_error(result);
                }
            }
        });
    });
})();
(function () {
    var pets = $('#pets-filter-form');

    if (pets.length) {
        pets.on('click', '.pagination a', function () {
            lock_screen(true);
            $('[name=page]', pets).val($(this).data('page') - 1);

            pets[0].submit();
        });

        pets.on('click', '.filter', function () {
            $('[name=page]', pets).val(0);
            pets[0].submit();
        });
    }
})();
$('#character-attacks-unique').on('click', '.buy-theme', function () {
    lock_screen(true);

    var data = { theme: $(this).data('theme'), mode: $(this).data('mode'), type: 0 };
    var _this = $(this);

    if (_this.hasClass('buy-theme')) {
        data.buy = 1;
    } else {
        data.use = 1;
    }

    $.ajax({
        url: make_url('characters#list_themes'),
        type: 'post',
        data: data,
        dataType: 'json',
        success: function (result) {
            if (result.success) {
                location.href = make_url('guides#character');
            } else {
                lock_screen(false);
                format_error(result);
            }
        }
    });
});
$('#theme-list-ajax').on('click', '.unlock', function () {
    lock_screen(true);
    $.ajax({
        url: make_url('characters#unlock_character/' + $(this).data('id')),
        data: { method: $(this).data('method') },
        type: 'post',
        success: function (result) {
            lock_screen(false);

            if (result.success) {
                location.href = make_url('guides#character');
            } else {
                format_error(result);
            }
        }
    });
});
