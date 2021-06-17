<?php if (!sizeof($comments)): ?>
	<?php echo t('news.no_comments') ?>
<?php else: ?>
	<?php foreach ($comments as $comment): ?>
		<?php echo partial('news_comment', ['comment' => $comment]) ?>
	<?php endforeach ?>
<?php endif ?>
