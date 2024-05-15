<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;

final class EditPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private Nette\Database\Explorer $database,
	)
	{
	}

	protected function createComponentPostForm(): Form
	{
		$form = new Form;
		$form->addText('title', 'Title:')
			->setRequired();
		$form->addTextArea('content', 'Content:')
			->setRequired();
		$form->addUpload("PostImg", "PostImg:")
		->addRule($form::Image, "Post img must be an image");

		$form->addSubmit('send', 'Save and publish');
		$form->onSuccess[] = $this->postFormSucceeded(...);

		return $form;
	}

	private function postFormSucceeded(array $data): void
	{
		/** @var Nette\Http\FileUpload $postImg */
		$postImg = $data["PostImg"];
		$imgDir = "/www/img/";
		$imgPathInfo =  pathinfo($postImg->getUntrustedName());
		$imgFilename =  $imgPathInfo["filename"];
		$imgExtention =  $imgPathInfo["extension"];

		\Tracy\Debugger::barDump($postImg->getUntrustedName());
		\Tracy\Debugger::barDump(pathinfo($postImg->getUntrustedName()));

		$file = Nette\Utils\Strings::webalize($imgFilename) . "." . $imgExtention;
		move_uploaded_file($postImg->getTemporaryFile(), __DIR__ . "/../../www/img/" . $file);
		$postId = $this->getParameter('postId');
		$data["img"] = "/nette-blog/www/img/" . $file;
		unset($data["PostImg"]);

		if ($postId) {
			$post = $this->database
				->table('posts')
				->get($postId);
			$post->update($data);

		} else {
			$post = $this->database
				->table('posts')
				->insert($data);
		}

		$this->flashMessage('Post was published', 'success');
		$this->redirect('Post:show', $post->id);
	}

	public function renderEdit(int $postId): void
	{
		$post = $this->database
			->table('posts')
			->get($postId);

		if (!$post) {
			$this->error('Post not found');
		}

		$this->getComponent('postForm')
			->setDefaults($post->toArray());
	}

	public function startup(): void
	{
		parent::startup();

		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in');
		}
	}

}