<?php

namespace Models;

class Allow extends \DB\SQL\Mapper
{
	function __construct(\DB\SQL $db)
	{
		parent::__construct($db, 'suki_c_allow');
	}

	function getAll()
	{
		$perms = [];
		$data = $db->exec('SELECT name, groups from suki_c_allow',NULL,86400);

		foreach ($data as $key => $p)
			$perms[$p['name']] = array_map('intval', explode(',', $p['groups']));

		return $perms;
	}

	function can($names = [])
	{
		$f3 = \Base::instance();

		// Nope, you can\'t do nothing you lazy bastard!
		if (empty($names))
			return false;

		// Work with arrays.
		$names = (array) $names;

		$user = $f3->get('currentUser');
		$groups =  array_unique(array_merge([$user->groupID], array_map('intval', explode(',', $user->groups))));
		$allowed = false;

		// My place, my rules...
		if(in_array(1, $groups)
			return true;

		// Get the permissions.
		$perms = $this->getAll();

		// Let us abuse array_intersect!
		foreach ($names as $name)
		{
			$check = array_intersect($perms[$name], $groups);

			if (!empty($check))
			{
				$allowed = true;
				break;
			}
		}

		return $allowed;
	}
}
