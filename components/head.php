<?php
	$head = $DB->name_value('head');
?>
<head>
	<meta charset="utf-8"/>
	<title><?=$head->title?></title>
	<meta name="description" content="<?=$head->description?>"/>
	<meta name="keywords" content="<?=$head->keywords?>"/>
	<meta name="robots" content="<?=$head->robots?>"/>
	<meta name="author" content="<?=$head->author?>"/>
	<meta name="viewport" content="<?=$head->viewport?>"/>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico"/>
	<link rel="icon" type="image/svg" sizes="any" href="favicon.svgz?t=<?=time()?>"/>
<link rel="alternate icon" type="image/png" sizes="16x16" href="favicon.png"/>
	<?php if(localhost() and BROWSER === 'Firefox'):?>
	<link rel="stylesheet" type="text/css" href="stylesheets/normalize.css" media="all"/>
	<link rel="stylesheet" type="text/css" href="stylesheets/style.css" media="all"/>
	<?php else:?>
	<link rel="stylesheet" type="text/css" href="stylesheets/combined.out.css" media="all"/>
	<?php endif?>
	<script type="text/javascript" src="scripts/functions.js" async defer></script>
	<!--[if lte IE 8]>
		<script type="text/javascript">
			var html5=new Array('header','hgroup','nav','menu','main','section','article','footer','aside','mark', 'picture', 'output');
			for(var i=0;i<html5.length;i++){document.createElement(html5[i]);}
		</script>
	<![endif]-->
</head>
