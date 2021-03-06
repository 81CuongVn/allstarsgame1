<?php
function buff_properties($player_instance = null) {
    if(!$player_instance) {
        $player_instance	= Player::get_instance();
    }

    $properties	= array();
    $formulas	= array(
        'for_life',
        'for_mana',
        'for_stamina',
        'for_atk',
        'for_def',
        'for_hit',
        'for_init',
        'for_crit',
        'for_inc_crit',
        'for_abs',
        'for_inc_abs',
        'for_prec'
    );

    $images		= array(
        'for_hit'		=> 'for_prec',
        'for_inc_abs'	=> 'for_abs',
        'for_inc_crit'	=> 'for_crit'
    );

    $formatter	= function ($item, $prop) {
        $formula	= $item->formula();
        $percent	= array(
            'for_crit',
            'for_inc_crit',
            'for_abs',
            'for_inc_abs',
            'for_life',
            'for_mana',
            'for_stamina'
        );

        $field			= $prop->field;

        /*
        if($item->id == 32) {
            print_r($item->$field);
            print_r($formula->level);
        }
        */

        if($field == 'for_hit') {
            $field_level	= 'for_hit_chance_inc';
        } else {
            $field_level	= $prop->field;
        }

        if(isset($formula->level->$field_level) && $formula->level->$field_level) {
            $val		= $item->$field + $formula->level->$field_level;
            $enhancer	= ' (<span class="enhancer-value">'. $item->$field . ' + ' . $formula->level->$field_level . '</span>)';
        } else {
            $enhancer	= '';
            $val		= $item->{$prop->field};
        }

        if(in_array($prop->field, $percent)) {
            return $val . '%' . $enhancer;
        } else {
            return $val . $enhancer;
        }
    };

    foreach($formulas as $formula) {
        $object				= new stdClass();
        $object->name		= t('formula.' . $formula . ($formula == 'for_mana' ? '.' . $player_instance->character()->anime_id : ''));
        $object->image		= 'icons/' . (isset($images[$formula]) ? $images[$formula] : $formula) . '.png';
        $object->field		= $formula;
        $object->formatter	= $formatter;

        $properties[]	= $object;
    }

    return $properties;
}