<?php

/**
 * This file is part of the Voonne platform (http://www.voonne.org)
 *
 * Copyright (c) 2016 Jan Lavička (mail@janlavicka.name)
 *
 * For the full copyright and license information, please view the file licence.md that was distributed with this source code.
 */

namespace Voonne\UsersModule\Pages;

use Voonne\Messages\FlashMessage;
use Voonne\Pages\Page;
use Voonne\Voonne\Model\Repositories\UserRepository;


class UpdatePage extends Page
{

	/**
	 * @var UserRepository
	 */
	private $userRepository;


	public function __construct(UserRepository $userRepository)
	{
		parent::__construct('update', 'voonne-usersModule.update.title');

		$this->userRepository = $userRepository;

		$this->hideFromMenu();
	}


	public function startup()
	{
		parent::startup();

		if ($this->userRepository->countBy(['id' => $this->getPresenter()->getParameter('id')]) == 0) {
			$this->flashMessage('voonne-usersModule.update.userNotFound', FlashMessage::ERROR);
			$this->redirect('users.default');
		}
	}


	public function isAuthorized()
	{
		return $this->getUser()->havePrivilege('admin', 'users', 'update');
	}

}
