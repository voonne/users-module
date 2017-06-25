<?php

/**
 * This file is part of the Voonne platform (http://www.voonne.org)
 *
 * Copyright (c) 2016 Jan Lavička (mail@janlavicka.name)
 *
 * For the full copyright and license information, please view the file licence.md that was distributed with this source code.
 */

namespace Voonne\UsersModule\Pages;

use Voonne\Pages\Page;


class DefaultPage extends Page
{

	public function __construct()
	{
		parent::__construct('default', 'voonne-usersModule.default.title');
	}


	public function isAuthorized()
	{
		return $this->getUser()->havePrivilege('admin', 'users', 'view');
	}

}
