<hr>
		<script>
			document.addEventListener("DOMContentLoaded", init(), false);
		</script>

		<div class="row">
			<div class="col-xs-12">
				<footer>
					<p>Produced by <a href="https://twitter.com/<?= $init->twitterID ?>"><?= $init->adminName ?></a>
						 (<a href="<?= $init->urlTopPage ?>"><?= $init->urlTopPage ?></a>)
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
					</footer>
				</div>
			</div>

		</div><!-- container -->
	</body>
</html>
