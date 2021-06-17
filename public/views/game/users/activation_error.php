<?php echo partial(isset($beta) && $beta ? 'shared/info_battle' : 'shared/info', array('id'=> 3, 'title' => $title, 'message' => $message)) ?>
