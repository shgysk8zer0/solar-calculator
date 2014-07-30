<!doctype html>
<html>
	<head>
		<meta charset="utf-8"/>
		<title>Solar Calculator</title>
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
	<body>
		<header>
			<h1>
				<a href="http://www.vivintsolar.com/en/">vivint solar</a>
			</h1>
			<img src="images/family-solar-home.jpg" alt="Solar Family"/>
		</header>
		<main>
			<form name="rateCalculator">
				<noscript>JavaScript is required for this to work. Please either enable JavaScript or upgrade your browser.</noscript>
				<label for="in">How much was your last electric bill?</label>
				$<input type="number" id="in" min="0" max="1000" step="0.01" name="paidRate" value="0.00" pattern="\d(\.\d{1,2})?" placeholder="117.42" autofocus/><br />
				$<output for="paidRate" name="calculated" id="out" value="0">0.00</output>
			</form>
		</main>
		<footer>
			&copy; <?=date('Y')?>
			<a href="mailto:rtall@vivintsolar.com" target="_blank" title="Send me an email"><?php include('images/envelope.svg')?></a>
		</footer>
	</body>
</html>