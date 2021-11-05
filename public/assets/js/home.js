(function () {
	var	news_page			= 1;
	var	news_container		= $('.noticias-1');
	var	players_page		= 1;
	var	players_container	= $('.noticias-2');
	var	tops_page			= 1;
	var	tops_container		= $('.noticias-3');
	var	ranks_page			= 1;
	var	ranks_container		= $('.noticias-4');
	var	leagues_container	= $('.noticias-5');
	var	comments_form		= $('#news-comment-form');

	if (news_container.length) {
		news_container.on('click', '.prev', function () {
			if(news_page == 1) {
				return;
			}

			news_page--;
			refresh_news();
		});

		news_container.on('click', '.next', function () {
			news_page++;
			refresh_news();
		});

		function refresh_news() {
			$.ajax({
				url:		make_url('home#news_list/' + news_page),
				success:	function (result) {
					$('.list', news_container).html(result);
				}
			});
		}

		refresh_news();
	}

	if (players_container.length) {
		players_container.on('click', '.prev', function () {
			if(players_page == 1) {
				return;
			}

			players_page--;
			refresh_statistics();
		});

		players_container.on('click', '.next', function () {
			players_page++;
			refresh_statistics();
		});

		function refresh_statistics() {
			$.ajax({
				url:		make_url('home#statistic_list/' + players_page),
				success:	function (result) {
					$('.players-list', players_container).html(result);
				}
			});
		}

		refresh_statistics();
	}

	if (tops_container.length) {
		tops_container.on('click', '.prev', function () {
			if(tops_page == 1) {
				return;
			}

			tops_page--;
			refresh_top_list();
		});

		tops_container.on('click', '.next', function () {
			tops_page++;
			refresh_top_list();
		});

		function refresh_top_list() {
			$.ajax({
				url:		make_url('home#top_list/' + tops_page),
				success:	function (result) {
					$('.tops-list', tops_container).html(result);
				}
			});
		}

		refresh_top_list();
	}

	$('#sl-ranks').on('change', function () {
		$.ajax({
			url:		make_url('home#rank_list/1/'+ $('#sl-ranks').val()),
			success:	function (result) {
				$('.ranks-list', ranks_container).html(result);
			}
		});
	});

	if (leagues_container.length) {
		$('#sl-leagues').on('change', function () {
			refresh_leagues();
		});

		function refresh_leagues() {
			$.ajax({
				url:		make_url('home#league_list/'+ $('#sl-leagues').val()),
				success:	function(result) {
					$('.leagues-list', leagues_container).html(result);
				}
			});
		}
		refresh_leagues();
	}

	if (ranks_container.length) {
		ranks_container.on('click', '.prev', function () {
			if(ranks_page == 1) {
				return;
			}

			ranks_page--;
			refresh_ranks();
		});

		ranks_container.on('click', '.next', function () {
			ranks_page++;
			refresh_ranks();
		});

		function refresh_ranks() {
			$.ajax({
				url:		make_url('home#rank_list/' + ranks_page +'/'+ $('#sl-ranks').val()),
				success:	function (result) {
					$('.ranks-list', ranks_container).html(result);
				}
			});
		}

		refresh_ranks();
	}

	if (comments_form.length) {
		comments_form.on('submit', function (e) {
			lock_screen(true);
			e.preventDefault();

			$.ajax({
				url:		$(this).attr('action'),
				data:		$(this).serialize(),
				type:		'post',
				success:	function (result) {
					lock_screen(false);

					$('textarea', comments_form).val('');

					$('#comments-container').html(result);
				}
			});
		});
	}
})();
