<div id="HistoryLog">
	<h2>歴史</h2>
	<ul class="list-unstyled">
		<?php
			$log = new Log();
			$log->historyPrint();
	?>
	</ul>
</div>
