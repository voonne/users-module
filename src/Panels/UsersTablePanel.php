<?php

/**
 * This file is part of the Voonne platform (http://www.voonne.org)
 *
 * Copyright (c) 2016 Jan Lavička (mail@janlavicka.name)
 *
 * For the full copyright and license information, please view the file licence.md that was distributed with this source code.
 */

namespace Voonne\UsersModule\Panels;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Voonne\Messages\FlashMessage;
use Voonne\Model\IOException;
use Voonne\Panels\Panels\TablePanel\Adapters\Doctrine2Adapter;
use Voonne\Panels\Panels\TablePanel\TablePanel;
use Voonne\Voonne\Model\Entities\User;
use Voonne\Voonne\Model\Facades\UserFacade;
use Voonne\Voonne\Model\Repositories\UserRepository;


class UsersTablePanel extends TablePanel
{

	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * @var UserFacade
	 */
	private $userFacade;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;


	public function __construct(
		UserRepository $userRepository,
		UserFacade $userFacade,
		EntityManagerInterface $entityManager
	)
	{
		parent::__construct();

		$this->userRepository = $userRepository;
		$this->userFacade = $userFacade;
		$this->entityManager = $entityManager;

		$this->setTitle('voonne-usersModule.usersTable.title');
	}


	public function beforeRender()
	{
		parent::beforeRender();

		$this->addColumn('email', 'voonne-usersModule.usersTable.email');

		$this->addColumn('createdAt', 'voonne-usersModule.usersTable.createdAt')
			->setTemplate('datetime');

		$this->addAction('update', 'voonne-usersModule.usersTable.update', function (User $user) {
			if ($this->getUser()->havePrivilege('admin', 'users', 'update')) {
				return $this->link('users.update', ['id' => $user->getId()]);
			} else {
				return null;
			}
		});

		$this->addAction('remove', 'voonne-usersModule.usersTable.remove', function (User $user) {
			if ($this->getUser()->getUser()->getId() != $user->getId() && $this->getUser()->havePrivilege('admin', 'users', 'remove')) {
				return $this->link('remove!', ['id' => $user->getId()]);
			} else {
				return null;
			}
		});

		$this->addTemplate(__DIR__ . '/column.latte');

		$this->setDefaultSort('createdAt');
		$this->setDefaultOrder('DESC');

		$this->setAdapter(new Doctrine2Adapter($this->entityManager->createQueryBuilder()->select('u')->from(User::class, 'u')));
	}


	public function handleRemove($id)
	{
		try {
			if (!$this->getUser()->havePrivilege('admin', 'users', 'remove')) {
				$this->flashMessage('voonne-common.authentication.unauthorizedAction', FlashMessage::ERROR);
				$this->redirect('this');
			}

			$this->userFacade->save($this->userRepository->find($id));

			$this->flashMessage('voonne-usersModule.usersTable.removed', FlashMessage::SUCCESS);
			$this->redirect('this');
		} catch(IOException $e) {
			$this->flashMessage('voonne-usersModule.usersTable.userNotFound', FlashMessage::ERROR);
			$this->redirect('this');
		}
	}

}
