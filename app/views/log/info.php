<div id="HistoryLog">
	<h2>お知らせ</h2>
	<div style="overflow:auto; height: <?= $init->divHeight ?>px;">
	<?php
		$log = new Log();
		$log->infoPrint();
	?>
	</div>
</div>


