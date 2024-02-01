<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="js/jquery-3.7.0.min.js"></script>
    <title>Salamandra</title>
</head>
    <body>
        <?php
			include_once($_SERVER['DOCUMENT_ROOT'].'/libs/helpers.php');
			if(Server_init(false)){
				echo '<h1>Service started but this method is to be implemented</h1>';
			} else {
				echo '<h1>Service dont start correctly</h1>';
			}
		?>
    </body>
</html>
