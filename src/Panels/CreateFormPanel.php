<?php

/**
 * This file is part of the Voonne platform (http://www.voonne.org)
 *
 * Copyright (c) 2016 Jan Lavička (mail@janlavicka.name)
 *
 * For the full copyright and license information, please view the file licence.md that was distributed with this source code.
 */

namespace Voonne\UsersModule\Panels;

use Voonne\Forms\Container;
use Voonne\Forms\Form;
use Voonne\Messages\FlashMessage;
use Voonne\Panels\Panels\FormPanel\FormPanel;
use Voonne\Voonne\DuplicateEntryException;
use Voonne\Voonne\Model\Entities\Role;
use Voonne\Voonne\Model\Entities\User;
use Voonne\Voonne\Model\Facades\UserFacade;
use Voonne\Voonne\Model\Repositories\RoleRepository;


class CreateFormPanel extends FormPanel
{

	/**
	 * @var RoleRepository
	 */
	private $roleRepository;

	/**
	 * @var UserFacade
	 */
	private $userFacade;


	public function __construct(RoleRepository $roleRepository, UserFacade $userFacade)
	{
		parent::__construct();

		$this->roleRepository = $roleRepository;
		$this->userFacade = $userFacade;

		$this->setTitle('voonne-usersModule.createForm.title');
	}

	public function setupForm(Container $container)
	{
		$container->addText('email', 'voonne-usersModule.createForm.email')
			->setRequired('voonne-form.rules.required');

		$container->addText('firstName', 'voonne-usersModule.createForm.firstName');

		$container->addText('lastName', 'voonne-usersModule.createForm.lastName');

		$container->addPassword('password', 'voonne-usersModule.createForm.password')
			->setRequired('voonne-form.rules.required')
			->addRule(Form::PATTERN, 'voonne-usersModule.createForm.passwordFormat', '^.*(?=.{8,})(?=.*[a-z])(?=.*[A-Z])(?=.*[\d\W]).*$')
			->setDescription('voonne-usersModule.createForm.passwordHint');

		$container->addCheckboxList('roles', 'voonne-usersModule.createForm.roles', $this->getRoles());

		$container->addSubmit('submit', 'voonne-usersModule.createForm.submit');

		$container->onSuccess[] = [$this, 'success'];
	}


	public function success(Container $container, $values)
	{
		try {
			$user = new User($values->email, $values->password, $values->firstName, $values->lastName);

			foreach ($values->roles as $role) {
				$user->addRole($this->roleRepository->find($role));
			}

			$this->userFacade->save($user);

			$this->flashMessage('voonne-usersModule.createForm.created', FlashMessage::SUCCESS);
			$this->redirect('users.update', ['id' => $user->getId()]);
		} catch (DuplicateEntryException $e) {
			$container->getForm()->addError('voonne-usersModule.createForm.duplicateEntry');
		}
	}


	private function getRoles()
	{
		$roles = [];

		foreach ($this->roleRepository->findAll() as $role) {
			/** @var Role $role */
			$roles[$role->getId()] = $role->getName();
		}

		return $roles;
	}

}
