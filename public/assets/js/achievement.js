(function () {
    var results = $('#make-list-achievement');

    $('#achievements-list-tabs a').click(function (e) {
        lock_screen(true);

        var _ = $(this);

        e.preventDefault()
        _.tab('show');

        $.ajax({
            url: make_url('achievements#make_list'),
            data: {
                achievement_id: _.data('id')
            },
            type: 'post',
            success: function (result) {
                lock_screen(false);
                results.html(result);
            }
        });
    });

    $('#achievements-list-tabs').find('a:first').trigger('click');
})();