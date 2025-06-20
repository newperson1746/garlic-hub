<?php
/*
 garlic-hub: Digital Signage Management Platform

 Copyright (C) 2024 Nikolaos Sagiadinos <garlic@saghiadinos.de>
 This file is part of the garlic-hub source code

 This program is free software: you can redistribute it and/or  modify
 it under the terms of the GNU Affero General Public License, version 3,
 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


namespace App\Modules\Users\Controller;

use App\Framework\Core\Session;
use App\Framework\Core\Translate\Translator;
use App\Framework\Exceptions\UserException;
use App\Framework\Utils\Html\FieldInterface;
use App\Framework\Utils\Html\FieldType;
use App\Framework\Utils\Html\FormBuilder;
use App\Modules\Users\Services\UsersService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\InvalidArgumentException;

class EditPasswordController
{
	private readonly FormBuilder $formBuilder;
	private readonly UsersService $userService;
	private Translator $translator;

	public function __construct(FormBuilder $formBuilder, UsersService $userService)
	{
		$this->formBuilder = $formBuilder;
		$this->userService = $userService;
	}

	/**
	 * @throws \Doctrine\DBAL\Exception
	 */
	public function editPassword(Request $request, Response $response): Response
	{
		$flash = $request->getAttribute('flash');
		try
		{
			$this->postActions($request);
			$flash->addMessage('success', 'User data changed');
		}
		catch(UserException $e)
		{
			$flash->addMessage('error', $e->getMessage());
		}

		return $response->withHeader('Location', '/users/edit')->withStatus(302);
	}

	/**
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	public function showForm(Request $request, Response $response): Response
	{
		$this->translator = $request->getAttribute('translator');

		$elements  = $this->formBuilder->prepareForm($this->createForm());

		$data = [
				'main_layout' => [
					'LANG_PAGE_TITLE' => $this->translator->translate('options', 'users'),
					'additional_css' => ['/css/user/options.css']
				],
				'this_layout' => [
					'template' => 'generic/edit', // Template-name
					'data' => [
						'LANG_PAGE_HEADER' =>  $this->translator->translate('options', 'users'),
						'FORM_ACTION' => '/users/edit/password',
						'element_hidden' => $elements['hidden'],
						'form_element' => $elements['visible'],
						'form_button' => [
							[
								'ELEMENT_BUTTON_TYPE' => 'submit',
								'ELEMENT_BUTTON_NAME' => 'submit',
								'LANG_ELEMENT_BUTTON' => $this->translator->translate('save', 'main')
							]
					]
				]
			]
		];
		$response->getBody()->write(serialize($data));

		return $response->withHeader('Content-Type', 'text/html');
	}

	/**
	 * @throws UserException
	 * @throws \Doctrine\DBAL\Exception
	 */
	private function postActions(Request $request): void
	{
		/** @var Session $session */
		$session  = $request->getAttribute('session');
		/** @var array<string,string> $postData */
		$postData = $request->getParsedBody();

		$token = $session->get('csrf_token');
		if (!is_string($token))
			throw new UserException('CSRF Token not in session');

		if ($postData['csrf_token'] !== $token)
			throw new UserException('CSRF Token mismatch');

		if (strlen($postData['edit_password']) < 8)
			throw new UserException('Password too small');

		if ($postData['edit_password'] !== $postData['repeat_password'])
			throw new UserException('Password not same');

		/** @var  array{UID: int} $user */
		$user = $session->get('user');

		if ($this->userService->updatePassword($user['UID'], $postData['edit_password']) !== 1)
			throw new UserException('User data could not be changed');

	}

	/**
	 * @return array<string,FieldInterface>
	 * @throws Exception
	 * @throws InvalidArgumentException
	 */
	private function createForm(): array
	{
		$form = [];
		$rules = ['required' => true, 'minlength' => 8];

		$form['edit_password'] = $this->formBuilder->createField([
			'type' => FieldType::PASSWORD,
			'id' => 'edit_password',
			'name' => 'edit_password',
			'translated_name' => $this->translator->translate('edit_password', 'users'),
			'value' => '',
			'rules' => $rules,
			'default_value' => ''
		]);
		$form['repeat_password'] = $this->formBuilder->createField([
			'type' => FieldType::PASSWORD,
			'id' => 'repeat_password',
			'name' => 'repeat_password',
			'translated_name' => $this->translator->translate('repeat_password', 'users'),
			'rules' => $rules,
			'default_value' => ''
		]);

		$form['csrf_token'] = $this->formBuilder->createField([
			'type' => FieldType::CSRF,
			'id' => 'csrf_token',
			'name' => 'csrf_token',
		]);

		return $form;
	}


}