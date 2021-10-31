<!DOCTYPE html>
<html >
<head>
<title>Site de gestion de sondages - 3 sondages au hasard</title>
<meta name="Description" content="Site de gestion de sondages" />
<link rel="stylesheet" type="text/css" href="style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<?php $login = $model->getLogin(); ?>

<body>

<div>
	<?php if (!empty($surveys)) { ?>
	<ul>
		<?php  foreach($surveys as $survey) { ?>
		<li><?= $survey->getQuestion() ?></li>
		<?php } ?>
	</ul>
	<?php } else { ?>
		<p>Aucun sondage trouvé</p>
	<?php }?>
</div>

</body>
</html>
