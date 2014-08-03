'use strict';
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

var tierRates = [
		parseFloat(parseFloat(document.forms.rateCalculator.tier1.value).toFixed(2)),
		parseFloat(parseFloat(document.forms.rateCalculator.tier2.value).toFixed(2)),
		parseFloat(parseFloat(document.forms.rateCalculator.tier3.value).toFixed(2)),
		parseFloat(parseFloat(document.forms.rateCalculator.tier4.value).toFixed(2))
	],
	tierKw = [
		parseInt(document.forms.rateCalculator.tier1_kw.value),
		parseInt(document.forms.rateCalculator.tier2_kw.value),
		parseInt(document.forms.rateCalculator.tier3_kw.value),
		parseInt(document.forms.rateCalculator.tier4_kw.value)
	],
	tierStartCost = getTierStart(),
	solarRate = parseFloat(document.forms.rateCalculator.solarRate.value);

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
	var paid = parseFloat(parseFloat(document.forms.rateCalculator.in.value).toFixed(2)),
		calculated = 0;

	if(!isNaN(paid)) {
		calculated = (getKilowatts(paid) * solarRate).toFixed(2);
		document.getElementById('out').value = calculated;
		document.getElementById('out').textContent = calculated;
	}
}

function getTierStart() {
	var starts = [0];
	for(var i = 1; i < tierRates.length; i++) {
		starts.push(starts[i - 1] + (tierRates[i - 1] * (tierKw[i] - tierKw[i - 1])));
	}
	return starts;
}

function getKilowatts(paid) {
	var tier  = getTier(paid);
	return tierKw[tier] + ((paid - tierStartCost[tier]) / tierRates[tier]);
}

function getTier(paid) {
	if(paid <= tierStartCost[1]) {
		return 0;
	}
	else if(paid <= tierStartCost[2]) {
		return 1;
	}
	else if(paid <= tierStartCost[3]) {
		return 2;
	}
	else {
		return 3;
	}
}

calc();