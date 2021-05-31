(function () {
    var comparison_targets = [];

    window._equipments = [];
    window.generate_equipment_tooltip = function (base, comparison) {
        var html = '<div class="equipment-popover-content">' +
            '<span class="rarity rarity-' + base.rarity + '">' + I18n.t('rarities.' + base.rarity) +
            (parseInt(base.have_extra) ? ' - <span class="extra">' + I18n.t('equipments.attributes.have_extra') + '</span>' : '') +
            '</span><hr /><div class="break"></div><table>';
        var ignores = ['id', 'player_item_id', 'created_at', 'graduation_sorting', 'rarity', 'have_extra', 'is_new', 'cooldown_reduction_id', 'technique_attack_increase_id', 'technique_mana_reduction_id', 'technique_crit_increase_id', 'technique_zero_mana_id'];
        var percents = ['currency_battle', 'exp_battle', 'currency_quest', 'exp_quest', 'luck_discount', 'consumable_price_discount', 'amplifier_price_discount', 'item_drop_increase', 'defense_technique_extra', 'generic_technique_damage', 'unique_technique_damage', 'technique_attack_increase', 'technique_mana_reduction', 'stamina_regen', 'life_regen', 'mana_regen', 'for_crit', 'for_inc_crit', 'for_abs', 'for_inc_abs', 'for_prec', 'for_inti', 'for_conv', 'technique_crit_increase'];
        var colors = ['currency_battle', 'exp_battle', 'currency_quest', 'exp_quest', 'luck_discount', 'consumable_price_discount', 'amplifier_price_discount', 'item_drop_increase', 'defense_technique_extra', 'generic_technique_damage', 'unique_technique_damage', 'technique_attack_increase', 'technique_mana_reduction', 'stamina_regen', 'life_regen', 'mana_regen', 'technique_crit_increase'];
        var with_ids = ['cooldown_reduction', 'technique_attack_increase', 'technique_mana_reduction', 'technique_crit_increase', 'technique_zero_mana'];
        var text_only_ids = ['lock_enemy_technique', 'technique_zero_mana'];

        for (var i in base) {
            var ignore = false;
            var is_percent = false;
            var is_color = false;
            var is_id = false;
            var text_only = false;
            var with_in = true;
            var assigns = {
                mana: I18n.t('formula.for_mana.' + _current_anime)
            }

            ignores.forEach(function (prop) {
                if (i == prop) {
                    ignore = true;
                }
            });

            if (ignore) {
                continue;
            }

            with_ids.forEach(function (prop) {
                if (i == prop) {
                    is_id = true;
                }
            });

            text_only_ids.forEach(function (prop) {
                if (i == prop) {
                    text_only = true;
                }
            });

            percents.forEach(function (prop) {
                if (i == prop) {
                    is_percent = true;
                }
            });
            colors.forEach(function (prop) {
                if (i == prop) {
                    is_color = true;
                }
            });

            var value = base[i];

            if (i == 'for_mana') {
                var translation = I18n.tb('formula.for_mana.' + _current_anime);
            } else {
                var translation = I18n.tb('at.' + i);
            }

            if (!translation) {
                translation = I18n.tb('formula.' + i);
            }

            if (!translation) {
                if (is_id) {
                    assigns.name = _equipments_names[base[i + '_id']];
                }

                translation = I18n.tb('equipments.attributes.' + i, assigns);
                with_in = false;
            }

            if (!comparison) {
                if (!parseFloat(value)) {
                    continue;
                }

                if (is_percent) {
                    value += '%';
                }
                if (is_color) {
                    color = "laranja";
                } else {
                    color = "plus";
                }

                if (text_only) {
                    html += '<tr><td><span class="' + color + '">' + translation + '</td></tr>';
                } else {
                    html += '<tr><td><span class="' + color + '">+' + value + '</span> ' + (with_in ? I18n.t('global.em') : '') + ' ' + translation + '</td></tr>';
                }
            } else {
                var comparison_value = comparison[i];

                if (!parseFloat(value) && !parseFloat(comparison_value)) {
                    continue;
                }

                if (comparison_value == value) {
                    var show_value = 0;

                    if (is_percent) {
                        show_value += '%';
                    }

                    if (text_only) {
                        html += '<tr><td><span class="same">' + translation + '</td></tr>';
                    } else {
                        html += '<tr><td><span class="same">+' + show_value + '</span> ' + (with_in ? I18n.t('global.em') : '') + ' ' + translation + '</td></tr>';
                    }
                } else {
                    var sign = comparison_value < value ? '+' : '';
                    var status = comparison_value < value ? 'plus' : 'less';

                    var show_value = comparison_value < value ? value - comparison_value : value - comparison_value;
                    show_value = Math.round(show_value * 100) / 100

                    if (is_percent) {
                        show_value += '%';
                    }

                    if (text_only) {
                        html += '<tr><td><span class="' + status + '">' + translation + '</td></tr>';
                    } else {
                        html += '<tr><td><span class="' + status + '">' + sign + show_value + '</span> ' + (with_in ? I18n.t('global.em') : '') + ' ' + translation + '</td></tr>';
                    }
                }
            }
        }

        var graduation_status = base.graduation_sorting > _current_graduation ? 'less' : '';
        var warn_span = base.graduation_sorting > _current_graduation ? '<span class="glyphicon glyphicon-warning-sign"></span> ' : '';

        // html += '<tr><td><span class="graduation ' + graduation_status + '">' + warn_span + I18n.t('equipments.attributes.graduation', {
        // 	name: _graduations[_current_anime][base.graduation_sorting]
        // }) + '</span></td></tr>';

        return html + '</table></div>';
    };

    var equipment_title = function (element) {
        var base = _equipments[parseInt(element.data('id'))],
            name = '',
            maxs = [],
            max_attributes = [],
            groups = {
                1: ['at_for', 'at_int', 'at_res', 'at_agi', 'at_dex', 'at_vit'],
                2: ['for_atk', 'for_def', 'for_crit', 'for_abs', 'for_prec', 'for_inti', 'for_conv', 'for_hit', 'for_init'],
                3: ['currency_battle', 'exp_battle', 'currency_quest', 'exp_quest', 'npc_battle_count', 'item_drop_increase', 'luck_discount', 'consumable_price_discount', 'amplifier_price_discount'],
                4: ['cooldown_reduction', 'technique_attack_increase', 'technique_mana_reduction', 'technique_crit_increase', 'technique_zero_mana', 'lock_enemy_technique']
            };

        for (var group in groups) {
            maxs[group] = 0;
            max_attributes[group] = '';

            for (var attribute in groups[group]) {
                if (parseFloat(base[groups[group][attribute]]) > parseFloat(maxs[group])) {
                    maxs[group] = base[groups[group][attribute]];
                    max_attributes[group] = groups[group][attribute];
                }
            }
        }

        for (var i in maxs) {
            if (maxs[i])
                name += I18n.t('equipments.names.' + max_attributes[i]) + ' ';
        }

        return I18n.t('slots.' + _current_anime) + ' ' + name;
    }
    window.attach_equipment_popver = function (source, comparison, is_chat) {
        source.popover({
            html: true,
            trigger: is_chat ? 'manual' : 'hover',
            title: equipment_title($(source)),
            placement: (is_chat ? 'right' : 'bottom'),
            content: function () {
                var base = _equipments[parseInt($(this).data('id'))];
                return generate_equipment_tooltip(base, comparison);
            }
        }).on("mouseenter", function () {
            var _this = this;

            if (is_chat) {
                $(this).popover("show");
                $(this).siblings(".popover").css({
                    position: 'fixed',
                    left: '270px',
                    top: $(window).height() - 350
                });

                $(document.body).append($(this).siblings(".popover"));
            }
        }).on("mouseleave", function () {
            if (is_chat) {
                $(this).popover("hide");
            }
        });
    }

    var container = $('#position-container');

    $(document).ready(function () {
        $('.slot', container).each(function () {
            var _ = $(this);

            if (parseInt(_.data('id'))) {
                attach_equipment_popver(_);
            }

            comparison_targets[_.data('slot')] = _.data('id');
        });
    });


    // Fazendo um evento com o botão direito do mouse
    var upgrade_equipment = function (element) {
        var win = bootbox.dialog({
            message: '...',
            buttons: [{
                label: 'Fechar',
                className: 'btn btn-sm btn-danger'
            }]
        });

        $('.modal-dialog', win).addClass('pattern-container');
        $('.modal-content', win).addClass('with-pattern');

        $.ajax({
            url: element.data('url'),
            type: 'get',
            data: {
                id: element.data('id'),
                slot: element.data('slot')
            },
            success: function (result) {
                $('.bootbox-body', win).html(result);

                $('.equipment-popover', win).each(function () {
                    var _ = $(this);
                    attach_equipment_popver(_);
                });

                // This one is for the images
                $('.modal-content', win).on('click', '.upgrade', function () {
                    win.modal('hide');
                    lock_screen(true);

                    $.ajax({
                        url: make_url('equipments#list_equipments'),
                        type: 'post',
                        data: {
                            id: $(this).data('id'),
                            method: $(this).data('method')
                        },
                        dataType: 'json',
                        success: function (result) {
                            if (result.success) {
                                location.href = make_url('equipments');
                            } else {
                                lock_screen(false);
                                format_error(result);
                            }
                        }
                    });

                });

            }
        });
    };
    var list_equipments = function (element) {
        $('.badge', this).remove();

        var win = bootbox.dialog({
            message: '...',
            buttons: [{
                label: I18n.t('global.close'),
                className: 'btn btn-sm btn-danger'
            }]
        });

        $('.modal-dialog', win).addClass('pattern-container');
        $('.modal-content', win).addClass('with-pattern');

        $('.bootbox-body', win).on('click', '.item', function () {
            alert('ok');
        });
        $.ajax({
            url: make_url('equipments#show'),
            data: {
                slot: element.data('slot')
            },
            type: 'post',
            success: function (result) {
                $('.bootbox-body', win).html(result);

                $('.equipment-popover', win).each(function () {
                    var _ = $(this);

                    if (comparison_targets[_.data('slot')]) {
                        attach_equipment_popver(_, _equipments[comparison_targets[_.data('slot')]]);
                    } else {
                        attach_equipment_popver(_);
                    }

                    _.on('click', function (e) {
                        var win2 = bootbox.dialog({
                            message: I18n.t('equipments.show.click_text'),
                            buttons: [{
                                label: I18n.t('equipments.show.equip'),
                                className: 'btn btn-sm btn-primary',
                                callback: function () {
                                    lock_screen(true);

                                    $.ajax({
                                        url: make_url('equipments#equip'),
                                        type: 'post',
                                        dataType: 'json',
                                        data: {
                                            slot: _.data('slot'),
                                            equipment: _.data('id')
                                        },
                                        success: function (result) {
                                            if (result.success) {
                                                location.reload();
                                            } else {
                                                lock_screen(false);
                                                format_error(result);
                                            }
                                        }
                                    });

                                    return false;
                                }
                            }, {
                                label: I18n.t('global.sell_by') + ' ' + _.data('price') + ' ' + I18n.t('currencies.' + _current_anime),
                                className: 'btn btn-sm btn-danger',
                                callback: function () {
                                    lock_screen(true);

                                    $.ajax({
                                        url: make_url('equipments#sell'),
                                        type: 'post',
                                        dataType: 'json',
                                        data: {
                                            equipment: _.data('id')
                                        },
                                        success: function (result) {
                                            if (result.success) {
                                                lock_screen(false);
                                                _.parent().remove();
                                                win2.modal('hide');
                                            } else {
                                                lock_screen(false);
                                                format_error(result);
                                            }
                                        }
                                    });

                                    return false;
                                }
                            }, {
                                label: I18n.t('global.destroy_by') + ' ' + _.data('destroy') + ' Fragmentos',
                                className: 'btn btn-sm btn-danger',
                                callback: function () {
                                    lock_screen(true);

                                    $.ajax({
                                        url: make_url('equipments#destroy'),
                                        type: 'post',
                                        dataType: 'json',
                                        data: {
                                            equipment: _.data('id')
                                        },
                                        success: function (result) {
                                            if (result.success) {
                                                lock_screen(false);
                                                _.parent().remove();
                                                win2.modal('hide');
                                            } else {
                                                lock_screen(false);
                                                format_error(result);
                                            }
                                        }
                                    });

                                    return false;
                                }
                            }, {
                                label: I18n.t('global.close'),
                                className: 'btn btn-sm'
                            }]
                        });
                    });
                })
            }
        });
    };

    container.on('click', '.slot', function (e) {
        var element = $(this);

        var buttons = [];
        if (!$(this).hasClass('equipped')) {
            list_equipments(element);
            return;
        } else {
            buttons.push({
                label: I18n.t('equipments.show.unequip'),
                className: 'btn btn-sm btn-danger',
                callback: function () {
                    lock_screen(true);

                    $.ajax({
                        url: make_url('equipments#equip'),
                        type: 'post',
                        dataType: 'json',
                        data: {
                            slot: element.data('slot'),
                            equipment: element.data('id')
                        },
                        success: function (result) {
                            if (result.success) {
                                location.reload();
                            } else {
                                lock_screen(false);
                                format_error(result);
                            }
                        }
                    });

                    return false;
                }
            });

            buttons.push({
                label: I18n.t('equipments.show.upgrade'),
                className: 'btn btn-sm btn-primary',
                callback: function () {
                    upgrade_equipment(element);
                    win.modal('hide');
                }
            });
        }

        buttons.push({
            label: I18n.t('equipments.show.list'),
            className: 'btn btn-sm btn-primary',
            callback: function () {
                list_equipments(element);
                win.modal('hide');
            }
        }, {
            label: I18n.t('global.close'),
            className: 'btn btn-sm'
        });
        var win = bootbox.dialog({
            message: I18n.t('equipments.show.click_text'),
            buttons: buttons
        });
    });
})();