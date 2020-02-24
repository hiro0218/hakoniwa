<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?= $init->title ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="<?= $init->cssDir ?>/<?= $init->css ?>">
	<link rel="shortcut icon" href="<?= $init->imgDir ?>/monster1.gif">
	<script type="text/javascript" src="<?= $init->jsDir ?>/<?= $init->js ?>"></script>
</head>

<body>
	<div class="container">

<?= $header; ?>

<?= $content; ?>

<?= $footer; ?>

	</div>
</body>
</html>
