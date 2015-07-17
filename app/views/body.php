<body>
<div class="container-fluid">
	<header>
		<ul class="list-inline">
			<li><a href="http://www.bekkoame.ne.jp/~tokuoka/hakoniwa.html" target="_blank">箱庭諸島スクリプト配布元</a> <a href="http://scrlab.g-7.ne.jp">[PHP]</a></li>
			<li><a href="http://hakoniwa.symphonic-net.com" target="_blank">箱庭諸島S.E配布元</a></li>
			<li><a href="http://snufkin.jp.land.to" target="_blank">沙布巾の箱庭</a></li>
			<li><a href="http://www.s90259900.onlinehome.us/" target="_blank">箱庭の箱庭</a></li>
			<li><a href="http://no-one.s53.xrea.com" target="_blank">The Return of Neptune</a></li>
			<li><a href="http://minnano.min-ai.net/ocn/" target="_blank">みんなのあいらんど</a></li>
		</ul>

		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<a href="<?= $init->baseDir ?>/hako-main.php" class="navbar-brand"><?= $init->title ?></a>
				</div>
				<ul class="nav navbar-nav">
					<li><a href="<?= $init->baseDir ?>/hako-main.php?mode=conf">島の登録・設定変更</a></li>
					<li><a href="<?= $init->baseDir ?>/hako-ally.php">同盟管理</a></li>
					<li><a href="<?= $init->baseDir ?>/hako-main.php?mode=log">最近の出来事</a></li>
				</ul>
			</div>
		</nav>
	</header>
