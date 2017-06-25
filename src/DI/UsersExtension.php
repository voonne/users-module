<?php

/**
 * This file is part of the Voonne platform (http://www.voonne.org)
 *
 * Copyright (c) 2016 Jan Lavička (mail@janlavicka.name)
 *
 * For the full copyright and license information, please view the file licence.md that was distributed with this source code.
 */

namespace Voonne\UsersModule\DI;

use Kdyby\Translation\Translator;
use Nette\DI\CompilerExtension;
use Voonne\UsersModule\Pages\CreatePage;
use Voonne\UsersModule\Pages\DefaultPage;
use Voonne\UsersModule\Pages\UpdatePage;
use Voonne\Voonne\InvalidStateException;


class UsersExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->getDefinition('voonne.permissionManager')
			->addSetup('addResource', ['admin', 'users', 'voonne-usersModule.permissions.name'])
			->addSetup('addPrivilege', ['admin', 'users', 'create', 'voonne-usersModule.permissions.create'])
			->addSetup('addPrivilege', ['admin', 'users', 'view', 'voonne-usersModule.permissions.view'])
			->addSetup('addPrivilege', ['admin', 'users', 'update', 'voonne-usersModule.permissions.update'])
			->addSetup('addPrivilege', ['admin', 'users', 'remove', 'voonne-usersModule.permissions.remove']);

		$builder->getDefinition('voonne.pageManager')
			->addSetup('addGroup', ['users', 'voonne-usersModule.title', 'user'])
			->addSetup('addPage', ['users', '@' . $this->prefix('defaultPage')])
			->addSetup('addPage', ['users', '@' . $this->prefix('createPage')])
			->addSetup('addPage', ['users', '@' . $this->prefix('updatePage')]);

		$builder->addDefinition($this->prefix('defaultPage'))
			->setClass(DefaultPage::class);

		$builder->addDefinition($this->prefix('createPage'))
			->setClass(CreatePage::class);

		$builder->addDefinition($this->prefix('updatePage'))
			->setClass(UpdatePage::class);
	}


	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		$translatorName = $builder->getByType(Translator::class);

		if(empty($translatorName)) {
			throw new InvalidStateException('Kdyby/Translation not found. Please register Kdyby/Translation as an extension.');
		}

		$builder->getDefinition($translatorName)
			->addSetup('addResource', ['neon', realpath(__DIR__ . '/../translations/users.cs.neon'), 'cs', 'voonne-usersModule'])
			->addSetup('addResource', ['neon', realpath(__DIR__ . '/../translations/users.en.neon'), 'en', 'voonne-usersModule']);
	}

}
