<div class="lastModified">
	<p>最終更新時間: <?= date("Y年m月d日 H時", $hako->islandLastTime) ?><br>
	(次のターンまで、あと
	<script>remainTime(<?= $hako->islandLastTime + $init->unitTime ?>);</script>
	</p>
</div>
