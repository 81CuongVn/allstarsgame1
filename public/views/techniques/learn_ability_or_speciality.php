<?php
	echo partial('shared/info',
			array(
				'id'		=> 4,
				'title'		=> $translate_key . 'success_learn_title',
				'message'	=> t($translate_key . 'success_learn', array(
					'name'	=> $item->description()->name
				))
			)
		);
?>
<br />