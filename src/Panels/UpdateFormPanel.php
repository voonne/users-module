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
use Voonne\Messages\FlashMessage;
use Voonne\Panels\Panels\FormPanel\FormPanel;
use Voonne\Voonne\DuplicateEntryException;
use Voonne\Voonne\Model\Entities\Role;
use Voonne\Voonne\Model\Entities\User;
use Voonne\Voonne\Model\Facades\UserFacade;
use Voonne\Voonne\Model\Repositories\RoleRepository;
use Voonne\Voonne\Model\Repositories\UserRepository;


class UpdateFormPanel extends FormPanel
{

	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * @var RoleRepository
	 */
	private $roleRepository;

	/**
	 * @var UserFacade
	 */
	private $userFacade;

	/**
	 * @var User
	 */
	private $user;


	public function __construct(UserRepository $userRepository, RoleRepository $roleRepository, UserFacade $userFacade)
	{
		parent::__construct();

		$this->userRepository = $userRepository;
		$this->roleRepository = $roleRepository;
		$this->userFacade = $userFacade;

		$this->setTitle('voonne-usersModule.updateForm.title');
	}


	public function beforeRender()
	{
		parent::beforeRender();

		$this->user = $this->userRepository->find($this->getPresenter()->getParameter('id'));
	}


	public function setupForm(Container $container)
	{
		$container->addText('email', 'voonne-usersModule.updateForm.email')
			->setDefaultValue($this->user->getEmail())
			->setRequired('voonne-form.rules.required');

		$container->addText('firstName', 'voonne-usersModule.updateForm.firstName')
			->setDefaultValue($this->user->getFirstName());

		$container->addText('lastName', 'voonne-usersModule.updateForm.lastName')
			->setDefaultValue($this->user->getLastName());

		$container->addCheckboxList('roles', 'voonne-usersModule.updateForm.roles', $this->getRoles())
			->setDefaultValue($this->getDefaultRoles());

		$container->addSubmit('submit', 'voonne-usersModule.updateForm.submit');

		$container->onSuccess[] = [$this, 'success'];
	}


	public function success(Container $container, $values)
	{
		try {
			$this->user->update($values->email, $values->firstName, $values->lastName);

			foreach ($this->user->getRoles() as $role) {
				$this->user->removeRole($role);
			}

			foreach ($values->roles as $role) {
				$this->user->addRole($this->roleRepository->find($role));
			}

			$this->userFacade->save($this->user);

			$this->flashMessage('voonne-usersModule.updateForm.updated', FlashMessage::SUCCESS);
			$this->redirect('users.default');
		} catch (DuplicateEntryException $e) {
			$container->getForm()->addError('voonne-usersModule.updateForm.duplicateEntry');
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


	private function getDefaultRoles()
	{
		$roles = [];

		foreach ($this->user->getRoles() as $role) {
			/** @var Role $role */
			$roles[] = $role->getId();
		}

		return $roles;
	}

}
