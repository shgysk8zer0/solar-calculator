<?php
	$head = $DB->name_value('head');
?>
<head>
	<meta charset="utf-8"/>
	<title><?=$head->title?></title>
	<meta name="description" content="<?=$head->description?>"/>
	<meta name="keywords" content="<?=$head->keywords?>"/>
	<meta name="robots" content="<?=$head->robots?>"/>
	<meta name="viewport" content="<?=$head->viewport?>"/>
	<base href="<?=URL?>/"/>
	<?php if(localhost() and BROWSER === 'Firefox'):?>
	<link rel="stylesheet" type="text/css" href="stylesheets/normalize.css" media="all"/>
	<link rel="stylesheet" type="text/css" href="stylesheets/style.css" media="all"/>
	<?php else:?>
	<link rel="stylesheet" type="text/css" href="stylesheets/combined.out.css" media="all"/>
	<?php endif?>
	<script type="application/javascript" src="scripts/functions.js" defer></script>
	<!--[if lte IE 8]>
		<script type="text/javascript">
			var html5=new Array('header','hgroup','nav','menu','main','section','article','footer','aside','mark');
			for(var i=0;i<html5.length;i++){document.createElement(html5[i]);}
		</script>
	<![endif]-->
</head>