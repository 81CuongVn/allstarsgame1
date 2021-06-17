<?php foreach($news as $new): ?>
    <li>
        <a href="<?php echo make_url('home#read_news/' . $new->id) ?>">
            <?php echo $new->title ?>
        </a><br />
        <span><?php echo t('global.por')?> <?php echo $new->user()->name ?></span>
    </li>
<?php endforeach ?>