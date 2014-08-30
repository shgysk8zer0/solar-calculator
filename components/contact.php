<?php
	$contact = $DB->name_value('contact');
?>
<address itemtype="http://schema.org/Person" itemscope>
	<div>
		<span itemprop="name">
			<b itemprop="givenName"><?=ucwords($contact->first_name)?></b>
			<b itemprop="familyName"><?=ucwords($contact->last_name)?></b><br />
		</span>
		<i itemprop="jobTitle"><?=ucwords($contact->job_title)?></i>
		- <b itemprop="worksFor">
			<a itemprop="url" title="<?=ucwords($contact->company_name)?> Homepage" href="<?=ucwords($contact->company_url)?>"><?=ucwords($contact->company)?></a>
		</b>
	</div><br />
	<div>
		<a href="tel:<?=$contact->office_phone?>" target="_blank" title="Call the office" itemprop="telephone">Office: <?=$contact->office_phone?> <?php include('images/icons/telephone.svg')?></a><br />
		<a href="tel:<?=$contact->cell_phone?>" target="_blank" title="Call my cell phone" itemprop="telephone">Cell: <?=$contact->cell_phone?> <?php include('images/icons/mobile_icon.svg')?></a><br />
		<a href="mailto:<?=$contact->email?>" target="_blank" title="Send me an email" itemprop="email"><?=$contact->email?> <?php include('images/icons/envelope.svg')?></a><br /><br />
	</div>
	<div itemprop="workLocation" itemscope itemtype="http://schema.org/PostalAddress">
		<span itemprop="streetAddress"><?=ucwords($contact->street_address)?></span><br />
		<span itemprop="addressLocality"><?=ucwords($contact->city)?></span>,
		<span itemprop="addressRegion"><?=strtoupper($contact->state)?></span>
		<span itemprop="postalCode"><?=$contact->zip?></span><br />
	</div>
	<br /><br />
</address>
