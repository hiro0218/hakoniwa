<div class="row">
	<div class="col-xs-12">
		<div id="RecentlyLog">
			<h2>最近の出来事</h2>
            <?= $this->getData('recent'); ?>
		</div>
        <div id="HistoryLog">
            <h2>歴史</h2>
            <ul class="list-unstyled">
            <?= $this->getData('history'); ?>
            </ul>
        </div>
	</div>
</div>

