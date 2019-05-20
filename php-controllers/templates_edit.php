<?php

	Security::init();

	$edit = new EditClass('edit1', io::geti('RefID'));

	$edit->title = 'Add/Edit Desktop Template';

	$edit->setSourceTable('public.desktop_templates', 'sdt_refid');

	$edit->addGroup('General Information');

	$edit->addControl(
		FFInput::factory()
			->caption('Template Title')
			->name('sdt_title')
			->sqlField('sdt_title')
			->req()
	);

	$edit->addGroup('Shortcuts');

	$edit->addControl(
		FFAppsShortcuts::factory()
	);

	$edit->addEnterpriseInformation();
	$edit->addUpdateInformation();

	$edit->printEdit();