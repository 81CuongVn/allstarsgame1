<div class="enhancer-tooltip">
	<?php if ($player_item->level == 1): ?>
		<ul>
			<li class="enchancer">
				<span class="description">Nenhum bônus para o nível atual</span>
			</li>
		</ul>
	<?php else: ?>
		<ul>
		<?php foreach ($tooltip as $bonus): ?>
			<li class="enhancer">
				<?php /*
				<span class="description description-base">
					<?php echo $bonus['current']['req'] ?>
				</span>
				<ul class="bonuses">
					<?php if (isset($bonus['last'])): ?>
						<li class="success">
							<span class="description">
								<span class="glyphicon glyphicon-ok"></span>
								<?php echo $bonus['last']['bonus'] ?>
							</span>
						</li>
					<?php endif ?>
					<li class="<?php echo $bonus['current']['ok'] ? 'success' : 'error' ?>">
						<span class="description">
							<?php if ($bonus['current']['ok']): ?>
								<span class="glyphicon glyphicon-ok"></span>
							<?php else: ?>
								<span class="glyphicon glyphicon-remove"></span>
							<?php endif ?>
							<?php echo $bonus['current']['bonus'] ?>
						</span>
					</li>
				</ul>
				*/ ?>
				<span class="description description-base">
					<?php echo $bonus['description'] ?>
					<ul class="bonuses">
						<?php foreach ($bonus['levels'] as $level => $data): ?>
							<li class="<?php echo $data['ok'] ? 'success' : 'error' ?>">
								<span class="description">
									<?php if ($data['ok']): ?>
										<span class="glyphicon glyphicon-ok"></span>
									<?php else: ?>
										<span class="glyphicon glyphicon-remove"></span>
									<?php endif ?>
									<?php echo $data['bonus'] ?>
								</span>
							</li>
						<?php endforeach ?>
					</ul>
				</span>
			</li>
			<li><hr /></li>
		<?php endforeach ?>
		</ul>
	<?php endif ?>
</div>