$(document).ready(function () {
    var totalCoords = 20;
    var mapContainer = $('#dungeon-map-container');
    var postKey = mapContainer.data('key');
    var myX = 0,
        myY = 0;
    var myself = mapContainer.data('player');

    var boxWidth = mapContainer.width() / totalCoords;
    var boxHeight = mapContainer.height() / totalCoords;
    var popoverContent = [];

    var dark_100_1 = $(document.createElement('DIV')).addClass('dark dark-top').css({
        opacity: 1
    });
    var dark_100_2 = $(document.createElement('DIV')).addClass('dark dark-bottom').css({
        opacity: 1
    });
    var dark_100_3 = $(document.createElement('DIV')).addClass('dark dark-left').css({
        opacity: 1
    });
    var dark_100_4 = $(document.createElement('DIV')).addClass('dark dark-right').css({
        opacity: 1
    });

    var dark_80_1 = $(document.createElement('DIV')).addClass('dark dark-top').css({
        opacity: .8
    });
    var dark_80_2 = $(document.createElement('DIV')).addClass('dark dark-bottom').css({
        opacity: .8
    });
    var dark_80_3 = $(document.createElement('DIV')).addClass('dark dark-left').css({
        opacity: .8
    });
    var dark_80_4 = $(document.createElement('DIV')).addClass('dark dark-right').css({
        opacity: .8
    });

    var dark_60_1 = $(document.createElement('DIV')).addClass('dark dark-top').css({
        opacity: .6
    });
    var dark_60_2 = $(document.createElement('DIV')).addClass('dark dark-bottom').css({
        opacity: .6
    });
    var dark_60_3 = $(document.createElement('DIV')).addClass('dark dark-left').css({
        opacity: .6
    });
    var dark_60_4 = $(document.createElement('DIV')).addClass('dark dark-right').css({
        opacity: .6
    });

    var fill1 = $(document.createElement('DIV')).addClass('dark fill-tl');
    var fill2 = $(document.createElement('DIV')).addClass('dark fill-tr');
    var fill3 = $(document.createElement('DIV')).addClass('dark fill-bl');
    var fill4 = $(document.createElement('DIV')).addClass('dark fill-br');

    mapContainer.append(
        dark_100_1, dark_100_2, dark_100_3, dark_100_4,
        dark_80_1, dark_80_2, dark_80_3, dark_80_4,
        dark_60_1, dark_60_2, dark_60_3, dark_60_4,
        fill1, fill2, fill3, fill4
    );

    function parseResponse(response) {
        if (response.messages) {
            alert(response.messages.join("\n"));
        }

        if (response.reload) {
            location.reload();
            return;
        }

        var foundIds = [];
        popoverContent = [];

        response.players.forEach(function (player) {
            var image		= mapContainer.find('[data-player=' + player.id + ']');
            var popoverKey	= player.x + ':' + player.y;
            var iconSrc		= image_url('maps/sprites/' + player.theme + '.png');
            var visible		= player.x >= myX - 2 && player.x <= myX + 2 && player.y >= myY - 2 && player.y <= myY + 2;

            foundIds.push(parseInt(player.id));

            if (!popoverContent[popoverKey]) {
                popoverContent[popoverKey] = '';
            }

            if (parseInt(player.id) == myself) {
                myX = parseInt(player.x);
                myY = parseInt(player.y);
            }

            mapContainer.find('.initial-dark').remove();

            var baseDarkLeft	= boxWidth * (myX - 2);
            var baseDarkTop		= boxHeight * (myY - 2);
            var baseDarkWidth	= boxWidth * 5
            var baseDarkHeight	= boxHeight * 4

            dark_60_1.css({
                height:	boxHeight,
                top:	boxHeight * (myY - 2),
                left:	baseDarkLeft,
                width:	baseDarkWidth
            });
            dark_60_2.css({
                height:	boxHeight,
                bottom:	boxHeight * (totalCoords - myY - 3),
                left:	baseDarkLeft,
                width:	baseDarkWidth
            });
            dark_60_3.css({
                width:	boxWidth,
                top:	baseDarkTop + boxWidth,
                left:	baseDarkLeft,
                height:	baseDarkHeight - boxHeight
            });
            dark_60_4.css({
                width:	boxWidth,
                top:	baseDarkTop + boxWidth,
                left:	baseDarkLeft + baseDarkWidth - boxWidth,
                height:	baseDarkHeight - boxHeight
            });

            dark_80_1.css({
                height:	boxHeight,
                top:	boxHeight * (myY - 3),
                left:	baseDarkLeft,
                width:	baseDarkWidth
            });
            dark_80_2.css({
                height:	boxHeight,
                bottom:	boxHeight * (totalCoords - myY - 4),
                left:	baseDarkLeft,
                width:	baseDarkWidth
            });
            dark_80_3.css({
                width:	boxWidth,
                top:	baseDarkTop - boxWidth,
                left:	baseDarkLeft - boxWidth,
                height:	baseDarkHeight + boxHeight * 3
            });
            dark_80_4.css({
                width:	boxWidth,
                top:	baseDarkTop - boxWidth,
                left:	baseDarkLeft + baseDarkWidth,
                height:	baseDarkHeight + boxHeight * 3
            });

            dark_100_1.css({
                height:	boxHeight * (myY - 3),
                left:	baseDarkLeft,
                width:	baseDarkWidth
            });
            dark_100_2.css({
                height:	boxHeight * (totalCoords - myY - 4),
                bottom:	0,
                left:	baseDarkLeft,
                width:	baseDarkWidth
            });
            dark_100_3.css({
                width:	boxWidth * (myX - 3),
                top:	baseDarkTop - boxWidth,
                left:	0,
                height:	baseDarkHeight + boxHeight * 3
            });
            dark_100_4.css({
                width:	boxWidth * (totalCoords - myX - 4),
                top:	baseDarkTop - boxWidth,
                right:	0,
                height:	baseDarkHeight + boxHeight * 3
            });

            fill1.css({
                width:	baseDarkLeft,
                height:	baseDarkTop - boxHeight
            });
            fill2.css({
                width:	boxWidth * (totalCoords - myX - 3),
                height:	baseDarkTop - boxHeight
            });
            fill3.css({
                width:	baseDarkLeft,
                height:	boxHeight * (totalCoords - myY - 4)
            })
            fill4.css({
                width:	boxWidth * (totalCoords - myX - 3),
                height:	boxHeight * (totalCoords - myY - 4)
            })

            if (!image.length) {
                var image = $(document.createElement('img'));
                image
                    .addClass('icon-player')
                    .attr('data-player', player.id)
                    .attr('data-name', player.name)
                    .attr('src', iconSrc);

                mapContainer.append(image)
            }

            image.css({
                left:	player.x * boxWidth,
                top:	player.y * boxHeight
            });

            if (visible || parseInt(player.id) == myself) {
                popoverContent[popoverKey] += '<div class="row"><div class="col-sm-3"><img src="' + iconSrc + '" /></div><div class="col-sm-9">' + player.name + '</div></div>';

                image.show();
            } else {
                image.hide();
            }
        });

        mapContainer.find('.block').removeClass('sharedchest chest door npc sharednpc');

        response.objects.forEach(function (objekt) {
            var image	= mapContainer.find('[data-object=' + objekt.id + ']');
            var visible	= objekt.x >= myX - 2 && objekt.x <= myX + 2 && objekt.y >= myY - 2 && objekt.y <= myY + 2;

            var popoverKey = objekt.x + ':' + objekt.y;

            if (objekt.kind == 'door') {
                var iconSrc		= image_url('maps/door.png');
                var objectClass	= 'door';
            } else if (objekt.kind == 'chest') {
                var iconSrc		= image_url('maps/chest.png');
                var objectClass	= 'chest';

                objekt.name = '<a href="javascript:;" data-object="' + objekt.id + '" class="take">' + objekt.name + '</a>';
            } else if (objekt.kind == 'sharedchest') {
                var iconSrc		= image_url('maps/sharedchest.png');
                var objectClass	= 'sharedchest';

                objekt.name = '<a href="javascript:;" data-object="' + objekt.id + '" class="take">' + objekt.name + '</a>';
            } else if (objekt.kind == 'npc' || objekt.kind == 'sharednpc') {
                var objectClass	= objekt.kind;
                var iconSrc		= image_url('maps/sprites/' + objekt.theme + '.png');

                objekt.name = '<a href="javascript:;" data-object="' + objekt.id + '" class="attack">' + objekt.name + '</a>';
            }

            if (!popoverContent[popoverKey]) {
                popoverContent[popoverKey] = '';
            }

            if (!image.length) {
                var image = $(document.createElement('img'));

                image
                    .addClass('icon-' + objectClass)
                    .attr('data-object', objekt.id)
                    .attr('data-name', objekt.name)
                    .attr('src', iconSrc)
                    .attr('data-x', objekt.x)
                    .attr('data-y', objekt.y);

                mapContainer.append(image)
            }

            image.css({
                left: objekt.x * boxWidth,
                top: objekt.y * boxHeight
            });

            if (visible) {
                popoverContent[popoverKey] += '<div class="row"><div class="col-sm-3"><img src="' + iconSrc + '" /></div><div class="col-sm-9">' + objekt.name + '</div></div>';

                image.show();
                mapContainer.find('.block[data-x=' + objekt.x + '][data-y=' + objekt.y + ']').addClass(objectClass);
            } else {
                image.hide();
            }
        });

        mapContainer.find('[data-player]')
            .each(function (i, el) {
                $el = $(el);
                if (foundIds.indexOf($el.data('player')) == -1) {
                    $el.remove();
                }
            })
            .end()
            .find('.block')
            .removeClass('self')
            .end()
            .find('.block[data-x=' + myX + '][data-y=' + myY + ']')
            .addClass('self');
    };

    mapContainer.on('mouseover mouseout', '.block', function () {
        $(this).toggleClass('hover');
    }).on('click', '.take', function () {
        var self = $(this);
        lock_screen(true);

        $.ajax({
            url:		make_url('guilds#dungeon_take'),
            type:		'post',
            data:		{
                id:		self.data('object'),
                key:	postKey
            },
            success:	function(result) {
                if (result.success) {
                    lock_screen(false);

                    jalert(result.reward, function () {
                        __loadPositions();
                    });
                } else {
                    lock_screen(false);

                    format_error(result);
                }
            },
			error:		function() {
				window.location.reload();
			}
        });
    }).on('click', '.attack', function () {
        var self = $(this);
        lock_screen(true);

        $.ajax({
            url:		make_url('guilds#dungeon_fight'),
            type:		'post',
            data:		{
                id:		self.data('object'),
                key:	postKey
            },
            success:	function(result) {
                if (result.success) {
                    location.href = make_url('battle_npcs#fight');
                } else {
                    lock_screen(false);

                    format_error(result);
                }
            },
			error:		function() {
				window.location.reload();
			}
        });
    }).on('click', 'img', function () {
        var self		= $(this);
        mapContainer.find('.block[data-x=' + self.data('x') + '][data-y=' + self.data('y') + ']').trigger('click');
    }).on('dblclick', 'img', function () {
        var self		= $(this);
        mapContainer.find('.block[data-x=' + self.data('x') + '][data-y=' + self.data('y') + ']').trigger('dblclick');
    }).on('click', '.block', function () {
        var self		= $(this);
        var popoverKey	= self.data('x') + ':' + self.data('y');

        mapContainer.find('.block').popover('destroy');

        if (popoverContent[popoverKey]) {
            self.popover({
                content:	popoverContent[popoverKey],
                trigger:	'manual',
                title:		'O que tem aqui?',
                html:		true,
                // container:	'body'
            });

            self.popover('show');
        }
    }).on('dblclick', '.block', function () {
        var self	= $(this);
        var cX		= parseInt(self.data('x'));
        var cY		= parseInt(self.data('y'));

        if (cX > myX + 1 || cX < myX - 1 || cY > myY + 1 || cY < myY - 1) {
            jalert('O lugar onde você quer ir é muito longe', false);

            return;
        }

        $.ajax({
            url:		make_url('guilds#dungeon_move'),
            type:		'post',
            data:		{
                key:	postKey,
                x:		self.data('x'),
                y:		self.data('y')
            },
            dataType:	'json',
            success:	function(result) {
                parseResponse(result);
            },
			error:		function() {
				window.location.reload();
			}
        });
    });

    for (var x = 0; x < totalCoords; x++) {
        for (var y = 0; y < totalCoords; y++) {
            var mapBlock = $(document.createElement('div'));

            mapBlock
                .addClass('block')
                .attr('data-x', x)
                .attr('data-y', y)
                .css({
                    width:	boxWidth,
                    height:	boxHeight,
                    top:	y * boxWidth + 'px',
                    left:	x * boxHeight + 'px'
                });

            mapContainer.append(mapBlock);
        }
    }

    function __loadPositions() {
        $.ajax({
            url:		make_url('guilds#dungeon_move'),
            dataType:	'json',
            type:		'post',
            data:		{ key: postKey },
            success:	function(result) {
                parseResponse(result);
            },
			error:		function() {
				window.location.reload();
			}
        });
    }

    __loadPositions();
    setInterval(function () {
        __loadPositions();
    }, 2000);
});
