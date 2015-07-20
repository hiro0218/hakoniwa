<div class="row">
	<div class="col-lg-4 col-md-6 col-sm-8 col-xs-12">
		<h1>島の登録・設定変更</h1>
		<?php $this->discovery($hako->islandNumber);   ?>
		<hr>
		<?php $this->changeIslandInfo($hako->islandList); ?>
		<hr>
		<?php $this->changeOwnerName($hako->islandList);  ?>
	</div>
</div>
		