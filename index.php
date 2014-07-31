<?php
	require('./functions.php');
	config();
	define_ua();
?>
<!doctype html>
<!--[if lt IE 7]>      <html class="lt-ie9 lt-ie8 lt-ie7 no-js"> <![endif]-->
<!--[if IE 7]>         <html class="lt-ie9 lt-ie8 no-js"> <![endif]-->
<!--[if IE 8]>         <html class="lt-ie9 no-js"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" > <!--<![endif]-->
	<?php load('head')?>
	<body lang="en">
		<?php load('header', 'main', 'footer')?>
	</body>
</html>
