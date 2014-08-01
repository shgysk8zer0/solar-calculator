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
		calculated = (paid * paid.getRate()).toFixed(2);
		document.getElementById('out').value = calculated;
		document.getElementById('out').textContent = calculated;
	}
}
Number.prototype.getRate = function() {
	var rate = 0.85;
	if(this.between(0 , 20)) {
		rate = 0.75;
	}
	else if(this.between(20, 40)) {
		rate = 0.72;
	}
	else {
		rate = 0.63;
	}
	return rate;
}
Number.prototype.between = function(min, max) {
	return (this >= min && this <= max);
}
calc();