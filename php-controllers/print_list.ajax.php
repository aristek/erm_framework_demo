<?php

	Security::init();

	CoreUtils::increaseTime();
	CoreUtils::increaseMemory();

	$ids = io::post('ids', true);
	$cas_refids = $ids ? explode(',', $ids) : array();
	$ds = DataStorage::factory(io::post('dskey_sc', true), true);
	
	# read storage, makes validation of data
	$activityID = $ds->safeGet('activity_id');
	$activityMode = $ds->safeGet('activity_mode');
	
	$doc = new RCDocument();
	
	$activity = CurriculumActivityBase::factory($activityMode, $activityID);
	
	if ($activityMode == CurriculumActivityMode::MODE_INSTRUCTOR_SECTION) {
		/** @var InstructorSuiteCourseSection $section */
		$section = $activity->getCourseSection();

		$actName = $activity->getActivityName();

		if (!$actName) {
			$actName = '';
		}

		$doc->addTitle($actName);
		$doc->addLeftHeading('School Building', $section->getSchool()->cb_desc);
		$doc->addRightHeading('Teacher', $section->getInstructorName());
		$doc->addRightHeading('Course', $section->getCourseName());
	}

	/** @var ScoringKeyItem[] $items */
	$items = $activity->getScoringKeyItems();

	for ($a = 0; $a < count($items); $a++) {
		/** @var ScoringKeyItem */
		$item = $items[$a];
		
		# print only requested IDS
		if (empty($cas_refids) || in_array($item->id(), $cas_refids)) {
			$doc->newLine('nobreak');
			$doc->addObject($item->toRCE());
		}
	}
	
	$doc->open();