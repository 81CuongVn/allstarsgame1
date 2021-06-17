<?php
if ($pages > 1) {
	$links  = false;
	$rechts = false;

	echo '<ul class="pagination ' . (isset($addClass) ? $addClass : '') . '">';
	if ($current == 1) {
		echo '<li class="page-item disabled"><span class="page-link">
			<i class="fa fa-arrow-left fa-fw" style="line-height: 15px;"></i>
		</span></li>';
	} else {
		$back = $current - 1;
		echo '<li class="page-item"><a href="?page=' . $back . '" class="page-link">
			<i class="fa fa-arrow-left fa-fw" style="line-height: 15px;"></i>
		</a></li>';
	}

	for ($i = 1; $i <= $pages; $i++){
		if ((2 >= $i) && ($current == $i)) {
			echo '<li class="page-item active"><span class="page-link">' . highamount($i) . '</span></li>';
		} elseif ((2 >= $i) && ($current != $i)) {
			echo '<li class="page-item"><a href="?page=' . $i . '" class="page-link">' . highamount($i) . '</a></li>';
		} elseif (($pages-2 < $i) && ($current == $i)) {
			echo '<li class="page-item active"><span class="page-link">' . highamount($i) . '</span></li>';
		} elseif (($pages-2 < $i) && ($current != $i)) {
			echo '<li class="page-item"><a href="?page=' . $i . '" class="page-link">' . highamount($i) . '</a></li>';
		} else {
			$max = $current + 3;
			$min = $current - 3;
			if ($current == $i) {
				echo '<li class="page-item active"><span class="page-link">' . highamount($i) . '</span></li>';
			} elseif (($min < $i) && ($max > $i)) {
				echo '<li class="page-item"><a href="?page=' . $i . '" class="page-link">' . highamount($i) . '</a></li>';
			} else {
				if ($i < $current) {
					if (!$links) {
						echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
						$links = true;
					}
				} else {
					if (!$rechts) {
						echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
						$rechts = true;
					}
				}
			}
		}
	}
	if ($pages == $current) {
		echo '<li class="page-item disabled"><span class="page-link">
			<i class="fa fa-arrow-right fa-fw" style="line-height: 15px;"></i>
		</span></li>';
	} else {
		$next = $current + 1;
		echo '<li class="page-item"><a href="?page=' . $next . '" class="page-link">
			<i class="fa fa-arrow-right fa-fw" style="line-height: 15px;"></i>
		</a></li>';
	}
	echo "</ul>";
}
