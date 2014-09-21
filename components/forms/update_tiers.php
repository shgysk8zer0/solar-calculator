<form name="<?=filename(__FILE__)?>" action="." method="post">
	<table>
		<caption>Tier Rates</caption>
		<thead>
			<tr>
				<th>Tier</th>
				<th>Rate</th>
				<th>Starting Kilowatts</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Tier</th>
				<th>Rate</th>
				<th>Starting Kilowatts</th>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach($DB->fetch_array("
			SELECT
				`tier`,
				`rate`,
				`kw`
			FROM `tiers`
			ORDER BY `tier`
		") as $tier):?>
			<tr>
				<td><?=$tier->tier?></td>
				<td><input type="number" min="0" step="0.01" name="rate[<?=$tier->tier?>]" value="<?=$tier->rate?>" required/></td>
				<td><input type="number" min="0" step="1" name="kw[<?=$tier->tier?>]" value="<?=$tier->kw?>" required/></td>
			</tr>
		<?php endforeach?>
		</tbody>
	</table>
	<button type="submit">Submit</button>
	<button type="reset">Reset</button>
</form>
