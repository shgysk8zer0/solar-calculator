<?php
	$head = $DB->name_value('head');
?>
<head>
	<meta charset="utf-8"/>
	<title><?=$head->title?></title>
	<link rel="stylesheet" type="text/css" href="./stylesheets/style.css" media="all"/>
	<!--<link rel="stylesheet" type="text/css" href="./stylesheets/normalize.css" media="all"/>-->
	<script type="application/javascript" src="./scripts/functions.js" defer></script>
	<!--[if lte IE 8]>
		<script type="text/javascript">
			var html5=new Array('header','hgroup','nav','menu','main','section','article','footer','aside','mark');
			for(var i=0;i<html5.length;i++){document.createElement(html5[i]);}
		</script>
	<![endif]-->
</head>