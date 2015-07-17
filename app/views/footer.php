<hr>
		<script>
			// JavaScriptモード関連
		    if (document.addEventListener) {
		        if (typeof(init) == "function") {
		            document.addEventListener("DOMContentLoaded", init(), false);
		        }
		    } else {
		        if (typeof(init) == "function") {
		            window.onload = init;
		        }
		    }
		</script>

		<div class="row">
			<div class="col-xs-12">
				<footer>
					<p>Produced by <a href="https://twitter.com/<?= $init->twitterID ?>"><?= $init->adminName ?></a>
                         (<a href="<?= $init->urlTopPage ?>"><?= $init->urlTopPage ?></a>)
                        </p>
					</footer>
				</div>
			</div>

		</div><!-- container -->
	</body>
</html>
