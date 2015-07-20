<div class="lastModified">
	<p>
	最終更新時間: <?= date("Y年 m月 d日 H時", $hako->islandLastTime) ?><br>
	<small>( 次のターンまで、あと <?= remainTime($hako->islandLastTime + $init->unitTime) ?>)</small>
	</p>
</div>
<?php
	function remainTime($nextTime) {
		$sec = $nextTime - $_SERVER['REQUEST_TIME'];
		
		//$time['day'] = floor($sec / 86400);
		//$sec %= 86400;
		$time['hour'] = floor($sec / 3600);
		$sec %= 3600;
		$time['min'] = floor($sec / 60);
		$time['sec'] = $sec % 60;
	   
	   echo $time['hour']."時間 ".$time['min']."分 ".$time['sec']."秒 ";
   }
