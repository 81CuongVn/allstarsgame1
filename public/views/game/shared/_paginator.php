<div class="text-center">
<?php
if ($pages > 1) {
	$links  = false;
	$rechts = false;

	echo '<ul class="pagination">';
	if ($current == 1) {
		echo '<li class="disabled"><span>
			<i class="fa fa-arrow-left fa-fw" style="line-height: 15px;"></i>
		</span></li>';
	} else {
		$back = $current - 1;
		echo '<li><a href="javascript:void(0);" data-page="' . $back . '">
			<i class="fa fa-arrow-left fa-fw" style="line-height: 15px;"></i>
		</a></li>';
	}

	for ($i = 1; $i <= $pages; $i++){ 
		if ((2 >= $i) && ($current == $i)) {
			echo '<li class="active"><span>' . $i . '</span></li>';
		} elseif ((2 >= $i) && ($current != $i)) {
			echo '<li><a href="javascript:void(0);" data-page="' . $i . '">' . $i . '</a></li>';
		} elseif (($pages-2 < $i) && ($current == $i)) {
			echo '<li class="active"><span>' . $i . '</span></li>';
		} elseif (($pages-2 < $i) && ($current != $i)) {
			echo '<li><a href="javascript:void(0);" data-page="' . $i . '">' . $i . '</a></li>';
		} else {
			$max = $current + 3;
			$min = $current - 3;
			if ($current == $i) {
				echo '<li class="active"><span>' . $i . '</span></li>';
			} elseif (($min < $i) && ($max > $i)) {
				echo '<li><a href="javascript:void(0);" data-page="' . $i . '">' . $i . '</a></li>';
			} else {
				if ($i < $current) {
					if (!$links) {
						echo '<li class="disabled"><span>...</span></li>';
						$links = true;
					}
				} else {
					if (!$rechts) {
						echo '<li class="disabled"><span>...</span></li>';
						$rechts = true;
					}
				}
			}
		}
	}
	if ($pages == $current) {
		echo '<li class="disabled"><span>
			<i class="fa fa-arrow-right fa-fw" style="line-height: 15px;"></i>
		</span></li>';
	} else {
		$next = $current + 1;
		echo '<li><a href="javascript:void(0);" data-page="' . $next . '">
			<i class="fa fa-arrow-right fa-fw" style="line-height: 15px;"></i>
		</a></li>';
	}
	echo "</ul>";
}
?>
</div>