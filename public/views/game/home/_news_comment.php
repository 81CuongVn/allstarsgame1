<div class="titulo-home3">
	<div style="float:left">
		<b><?php echo t('news.comment_by', ['name' => $comment->user()->name]) ?></b>
	</div>	
	<div style="float:right">
		<span style="top: 25px"><?php echo t('news.post_date', [
			'date' => date("d/m/Y", strtotime($comment->created_at)) . " &agrave;s " . date("H:i:s", strtotime($comment->created_at))
		]) ?></span>
	</div>
</div>
<div class="conteudo-news">
	<div style="padding: 5px;"><?php echo nl2br($comment->content) ?></div>
</div>
<br />