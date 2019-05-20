<?php

	Security::init();

	$list = new ListClass();

	$list->title = 'Desktop Templates';

	$list->SQL = "
		SELECT dt.*
		  FROM public.desktop_templates AS dt
		 WHERE vndrefid = VNDREFID
		 ORDER BY sdt_refid DESC
	";

	$list->addColumn('Template Title')
		->sqlField('sdt_title');

	$list->addColumn('Shortcuts')
		->dataCallback(function ($data) {
			$t = new ARDesktopTemplate($data);
			$list = $t->getShortcuts();

			if (empty($list)) {
				return '<i>no shortcuts...</i>';
			}

			return UIDiv::factory(count($list))
				->viewStyle(UIDiv::VIEW_HINT_TEXT)
				->hint(implode('<hr>', $list->sdts_title))
				->toHTML();
		});

	$list->setColumnsGroup('Applied to');

	$list->addColumn('User Roles')
		->dataCallback(function ($data) {
			$SQL = "
				SELECT vr.vrname
				  FROM public.roles_desktops AS vd
				       INNER JOIN public.roles AS vr ON vr.vrrefid = vd.vrrefid
				 WHERE vd.sdt_refid = " . (int)$data['sdt_refid'] . "
			";
			$list = db::execSQL($SQL)->indexCol();

			if (empty($list)) {
				return '0';
			}

			return UIDiv::factory(count($list))
				->viewStyle(UIDiv::VIEW_HINT_TEXT)
				->hint(implode('<hr>', $list))
				->toHTML();
		});

	$list->addColumn('Users')
		->dataCallback(function ($data) {
			$SQL = "
				SELECT COUNT(1)
				  FROM public.users AS u
				 WHERE u.sdt_refid = " . (int)$data['sdt_refid'] . " 
			";
			$cnt = db::execSQL($SQL)->getOne();

			if ($cnt == 0) {
				return '0';
			}

			$SQL = "
				SELECT COALESCE(u.umlastname, '') || ', ' || COALESCE(u.umfirstname, '') 
				  FROM public.users AS u
				 WHERE u.sdt_refid = " . (int)$data['sdt_refid'] . "
				 LIMIT 10 
			";
			$list = db::execSQL($SQL)->indexCol();

			return UIDiv::factory(count($list))
				->viewStyle(UIDiv::VIEW_HINT_TEXT)
				->hint(implode('<hr>', $list) . ($cnt > 10 ? '<hr>... total ' . $cnt : ''))
				->toHTML();
		});

	$list->setColumnsGroup('Update Information');

	$list->addColumn('Last User')
		->sqlField('lastuser');

	$list->addColumn('Last Update')
		->type(ListClassColumn::TYPE_DATETIME)
		->sqlField('lastupdate');

	$list->editURL = 'templates_edit.php';
	$list->addURL = 'templates_edit.php';

	$list->deleteTableName = 'public.desktop_templates';
	$list->deleteKeyField = 'sdt_refid';

	$list->printList();