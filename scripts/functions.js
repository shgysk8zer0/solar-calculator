if(document.createElement('picture').toString() === '[object HTMLUnknownElement]') {
	var pictures = document.getElementsByTagName('picture'), sources, image, i, n;
	for(i = 0; i < pictures.length; i++) {
		sources = pictures[i].getElementsByTagName('source');
		image = pictures[i].getElementsByTagName('img')[0];
		if('matchMedia' in window) {
			for(n = 0; n < sources.length; n++) {
				if(matchMedia(sources[n].getAttribute('media')).matches) {
					image.src = sources[n].getAttribute('srcset');
					break;
				}
			}
		}
		else {
			image.src = sources[0].getAttribute('src');
		}
	}
}
document.documentElement.className = '';
if('oninput' in document) {
	document.forms.rateCalculator.in.oninput = calc;
}
else {
	document.forms.rateCalculator.in.onchange = calc;
}
document.forms.rateCalculator.onsubmit = function() {
	return false;
}
function calc() {
	var paid = parseFloat(document.forms.rateCalculator.in.value),
		calculated = 0;
	if(!isNaN(paid)) {
		calculated = (paid * getRate(paid)).toFixed(2).toString();
		document.getElementById('out').value = calculated;
		document.getElementById('out').textContent = calculated;
	}
}
function getRate(paid) {
	var rate = 0.85;
	if(paid <= 20) {
		rate = 0.75;
	}
	else if(paid <= 40) {
		rate = 0.72;
	}
	else if(paid <= 80) {
		rate = 0.67;
	}
	else if(paid <= 100) {
		rate = 0.65;
	}
	else {
		rate = 0.63;
	}
	return rate;
}
calc();