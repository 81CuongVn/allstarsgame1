(function () {
    var queue_alert			= false;
    var timer_iv			= null;

	var timer				= 5;
	var timeout				= 5;

	var audio				= $(document.createElement('AUDIO')).attr('src', resource_url('media/found.mp3')).attr('type', 'audio/mpeg');
    var room_search_friend	= $('#room-search-friend');

    // Filtro da página de ligas
    $('#leagues').on('change', function () {
        var _ = $(this);
        $.ajax({
            url:		make_url('battles_pvp#ranked'),
            data:		$(this).serialize(),
            type:		'post',
            data:		{
				leagues: $(this).val()
			},
            success:	function (result) {
                $('#league-filter-form').trigger('submit');
            }
        });
    });

    // Recebe a recompensa da Season
    $('#reward-league').on('click', '.reward', function () {
        lock_screen(true);
        var _ = $(this);
        $.ajax({
            url:		make_url('battle_pvps#reward'),
            data:		{
				id: _.data('league')
			},
            dataType:	'json',
            type:		'post',
            success:	function (result) {
                if (result.success) {
                    location.href = make_url('battle_pvps#ranked');
                } else {
                    lock_screen(false);
                    format_error(result);
                }
            }
        });
    });

    $('#battle-pvp-enter-queue').on('click', function () {
        lock_screen(true);

        $.ajax({
            url:		make_url('battle_pvps#enter_queue'),
            dataType:	'json',
            success:	function (result) {
                lock_screen(false);

                _check_pvp_queue = result.success;

                if (!result.success) {
                    format_error(result);
                } else {
                    location.href = make_url('battle_pvps');
                }
            }
        });
    });

    // Aceita o Duelo
    $('#room-search-results').on('click', '.enter-pvp-training-battle', function () {
        lock_screen(true);
        var _ = $(this);
        $.ajax({
            url:		make_url('battle_pvps#accept'),
            data:		{
                id: _.data('id')
            },
            dataType:	'json',
            type:		'post',
            success:	function (result) {
                if (result.success) {
                    location.href = make_url('battle_pvps#fight');
                } else {
                    lock_screen(false);
                    format_error(result);
                }
            }
        });
    });

    // Deleta uma Sala de Treinamento
    $('#waiting').on('click', '.decline', function () {
        lock_screen(true);
        var _ = $(this);
        $.ajax({
            url:		make_url('battle_pvps#decline'),
            dataType:	'json',
            type:		'post',
            success:	function (result) {
                if (result.success) {
                    location.href = make_url('characters#status');
                } else {
                    lock_screen(false);
                    format_error(result);
                }
            }
        });
    });

    // Cria uma Sala de treinamento
    if (room_search_friend.length) {
        room_search_friend.on('submit', function (e) {
            e.preventDefault();
            lock_screen(true);
            $.ajax({
                url:		make_url('battle_pvps#room_create'),
                data:		$(this).serialize(),
                dataType:	'json',
                type:		'post',
                success:	function (result) {
                    if (result.success) {
                        location.href = make_url('battle_pvps#waiting');
                    } else {
                        lock_screen(false);
                        format_error(result);
                    }
                }
            });
        });

        // room_search_friend.trigger('submit');
    }

    $(document).on('click', '#tooltip-queue-data .btn-primary', function () {
        lock_screen(true);

        $.ajax({
            url: make_url('battle_pvps#enter_queue'),
            dataType: 'json',
            success: function () {
                lock_screen(false);
                _check_pvp_queue = true;

                location.reload();
            }
        });
    });

    $(document).on('click', '#tooltip-queue-data .btn-danger', function () {
        lock_screen(true);

        $.ajax({
            url: make_url('battle_pvps#exit_queue'),
            dataType: 'json',
            success: function () {
                lock_screen(false);
                _check_pvp_queue = false;

                location.reload();
            }
        });
    });

    $('#1x-queue-data').on('click', function () {
        lock_screen(true);

        $.ajax({
            url: make_url('battle_pvps#exit_queue'),
            dataType: 'json',
            success: function () {
                lock_screen(false);
                _check_pvp_queue = false;

                location.href = make_url('battle_pvps');
            }
        });
    });

	setInterval(function () {
        if (_check_pvp_queue) {
            $.ajax({
                url:		make_url('battle_pvps#check_queue'),
                dataType:	'json',
                success:	function (result) {
                    if (result.redirect) {
                        location.href	= result.redirect;
                    }

                    if (result.found && !queue_alert) {
                        audio[0].play();
                        timer			= result.seconds;
						var $width		= (timer * 100 / timeout);
						var progress	= `<div class="timer progress progress-striped active">
							<div class="progress-bar" style="width: ${$width}%"></div>
						</div>`
                        queue_alert		= bootbox.dialog({
                            message: `
								<h4>${I18n.t('battles.pvp.queue_found')}</h4><br /><br />
								${progress}
							`
                        });

                        timer_iv		= setInterval(function () {
							$width	= (timer-- * 100 / timeout);
                            $('.progress-bar', queue_alert).css({
                                width: $width + '%'
                            });

                            if (timer <= 0) {
                                queue_alert.modal('hide');
                                queue_alert	= false;

                                clearInterval(timer_iv);

                                window.location.reload();
                            }
                        }, 1000);
                    }

                    if (!result.found && queue_alert) {
                        queue_alert.modal('hide');
                        queue_alert	= false;

                        clearInterval(timer_iv);
                    }
                }
            });
        }
    }, 2000);
})();
