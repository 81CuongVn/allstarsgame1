<?php echo partial('shared/title', array('title' => 'titles.news', 'place' => 'titles.news')) ?>
<div class="titulo-home3">
	<div style="float:left">
		<b><?php echo $new->title ?></b>
	</div>	
	<div style="float:right">
		<span class="laranja"><?=t('news.posted_by', [
			'name'	=> $new->user()->name
		]);?></span><br />
		<span class="azul"><?=t('news.post_date', [
			'date'	=> date("d/m/Y", strtotime($new->created_at)) . " &agrave;s " . date("H:i:s", strtotime($new->created_at))
		]);?></span>
	</div>	
</div>
<div class="conteudo-news">
	<div style="padding: 5px;"><?php echo nl2br($new->description) ?></div>
</div>
<?php if (FW_ENV != 'dev') { ?>
<div align="right" style="margin-top: 10px; margin-right: 5px;">
    <div class="fb-share-button" data-href="<?=make_url('home#read_news/' . $new->id);?>" data-layout="button_count" data-size="large"></div>
    <div class="fb-like" data-href="<?=make_url('home#read_news/' . $new->id);?>" data-width="70" data-layout="button_count" data-action="like" data-size="large" data-share="false"></div>
</div>
<?php } ?>
<?php if ($_SESSION['user_id']) { ?>
    <div class="titulo-home3">
        <b><?php echo t('news.leave_comment') ?></b>
    </div>
    <form id="news-comment-form" action="<?php echo make_url('home#make_comment/' . $new->id) ?>">
        <div style="padding: 3px 7px 0 7px; margin-top: 5px;">
            <textarea name="content" class="form-control" rows="10" cols="115"></textarea>
	        <div align="right" style="margin-top: 5px">
    	        <input type="submit" class="btn btn-primary" value="<?php echo t('news.comment') ?>"/>
        	</div>
		</div>
    </form>
<?php } ?>
<hr />
<div id="comments-container">
	<?php if (!sizeof($comments)): ?>
		<?php echo t('news.no_comments') ?>
	<?php else: ?>
		<?php foreach ($comments as $comment): ?>
			<?php echo partial('news_comment', ['comment' => $comment]) ?>
		<?php endforeach ?>
	<?php endif ?>
</div>