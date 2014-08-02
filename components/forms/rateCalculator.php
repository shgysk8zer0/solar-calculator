<?php
	$tiers = $DB->fetch_array("
		SELECT `tier`, `rate`
		FROM `tiers`
		ORDER BY `tier`
		LIMIT 5
	");
?>
<form name="<?=filename(__FILE__)?>">
	<label for="in">How much was your last electric bill?</label>
	$<input type="number" id="in" min="0" max="1000" step="0.01" name="paidRate" pattern="\d(\.\d{1,2})?" placeholder="117.42" autofocus/><br />
	Wouldn't you rather pay this? $<output for="paidRate" name="calculated" id="out">0.00</output>* <br />
	<small>
		<b>*</b>
		<em>Rates are estimated.</em>
	</small>
	<?php foreach($tiers as $tier):?>
	<input type="hidden" name="tier<?=$tier->tier?>" value="<?=$tier->rate?>"/>
	<?php endforeach?>
</form>