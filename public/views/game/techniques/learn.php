<?php
	echo partial('shared/info',
			array(
				'id'		=> 4,
				'title'		=> 'techniques.learn.success_title',
				'message'	=> t('techniques.learn.success', array(
					'name'	=> $item->description()->name,
					'exp'	=> $exp
				))
			)
		);
?>
<br />