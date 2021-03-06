(function () {
	var	news_page	= 1;
	var	news_container	= $('.noticias-1');
	var	players_page	= 1;
	var	players_container	= $('.noticias-2');
	var	tops_page	= 1;
	var	tops_container	= $('.noticias-3');
	var	ranks_page	= 1;
	var	ranks_container	= $('.noticias-4');
	var	comments_form	= $('#news-comment-form');
		
	if(news_container.length) {
		(function () {
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
		})();
	}
	if(players_container.length) {
		(function () {
			players_container.on('click', '.prev', function () {
				if(players_page == 1) {
					return;
				}
				
				players_page--;
				refresh_news();
			});
	
			players_container.on('click', '.next', function () {
				players_page++;
				refresh_news();
			});
			
			function refresh_news() {
				$.ajax({
					url:		make_url('home#statistic_list/' + players_page),
					success:	function (result) {
						$('.players-list', players_container).html(result);
					}
				});
			}
			
			refresh_news();
		})();
	}
	if(tops_container.length) {
		(function () {
			tops_container.on('click', '.prev', function () {
				if(tops_page == 1) {
					return;
				}
				
				tops_page--;
				refresh_news();
			});
	
			tops_container.on('click', '.next', function () {
				tops_page++;
				refresh_news();
			});
			
			function refresh_news() {
				$.ajax({
					url:		make_url('home#top_list/' + tops_page),
					success:	function (result) {
						$('.tops-list', tops_container).html(result);
					}
				});
			}
			
			refresh_news();
		})();
	}
	$('#sl-ranks').on('change', function () {
		function refresh_news() {
			$.ajax({
				url:		make_url('home#rank_list/1/'+ $('#sl-ranks').val()),
				success:	function (result) {
					$('.ranks-list', ranks_container).html(result);
				}
			});
		}
		refresh_news();
	});	
	if(ranks_container.length) {
		(function () {
			ranks_container.on('click', '.prev', function () {
				if(ranks_page == 1) {
					return;
				}
				
				ranks_page--;
				refresh_news();
			});
	
			ranks_container.on('click', '.next', function () {
				ranks_page++;
				refresh_news();
			});
			
			function refresh_news() {
				$.ajax({
					url:		make_url('home#rank_list/' + ranks_page +'/'+ $('#sl-ranks').val()),
					success:	function (result) {
						$('.ranks-list', ranks_container).html(result);
					}
				});
			}
			
			refresh_news();
		})();
	}

	if(comments_form.length) {
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