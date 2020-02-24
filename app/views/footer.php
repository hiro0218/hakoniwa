			<hr>

			<script>
				document.addEventListener("DOMContentLoaded", init(), false);
			</script>

			<div class="row">
				<div class="col-xs-12">
					<footer>
						<p>Produced by <a href="https://twitter.com/<?= $init->twitterID ?>" target="_blank"><?= $init->adminName ?></a>
						 (<a href="<?= $init->urlTopPage ?>" target="_blank"><?= $init->urlTopPage ?></a>)
<?php if($init->performance) : ?>
	<small class="pull-right">
	<?php
	list($tmp1, $tmp2) = array_pad( explode(" ", $init->CPU_start), 2, 0);
	list($tmp3, $tmp4) = array_pad( explode(" ", microtime()), 2, 0);
	printf("(CPU: %.6f秒)", $tmp4-$tmp2+$tmp3-$tmp1);
	?>
	</small>
<?php endif; ?>
						</p>

						<ul class="list-inline">
							<li><a href="http://www.bekkoame.ne.jp/~tokuoka/hakoniwa.html" target="_blank">箱庭諸島スクリプト配布元</a> <a href="http://scrlab.g-7.ne.jp">[PHP]</a></li>
							<li><a href="http://hakoniwa.symphonic-net.com" target="_blank">箱庭諸島S.E配布元</a></li>
							<li><a href="http://snufkin.jp.land.to" target="_blank">沙布巾の箱庭</a></li>
							<li><a href="http://www.s90259900.onlinehome.us/" target="_blank">箱庭の箱庭</a></li>
							<li><a href="http://no-one.s53.xrea.com" target="_blank">The Return of Neptune</a></li>
							<li><a href="http://minnano.min-ai.net/ocn/" target="_blank">みんなのあいらんど</a></li>
							<li><a href="https://github.com/hiro0218/hakoniwa/" target="_blank">hakoniwa</a></li>
						</ul>
					</footer>
				</div>
			</div>

		</div><!-- container -->
	</body>
</html>
