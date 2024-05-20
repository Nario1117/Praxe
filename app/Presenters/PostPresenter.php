<?php

namespace App\Presenters;

use App\Components\ReviewControl;
use App\Components\ReviewControlFactory;
use Nette;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter
{

	/** @var int|null @persistent */
	public int|null $postId;

	public function __construct(
		private Nette\Database\Explorer $database,
		private ReviewControlFactory    $reviewControlFactory,
	)
	{
	}

	public function renderShow(): void
	{
		$post = $this->database->table('posts')->get($this->postId);

		$this->template->post = $post;
		$this->template->comments = $post->related('comments')->order('created_at');

	}

	protected function createComponentCommentForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

		$form->addText('name', 'Your name:')
			->setRequired();

		$form->addEmail('email', 'Email:');

		$form->addTextArea('content', 'Comment:')
			->setRequired();

		$form->addSubmit('send', 'Publish comment');

		$form->onSuccess[] = $this->commentFormSucceeded(...);

		return $form;
	}

	private function commentFormSucceeded(\stdClass $data): void
	{
		$postId = $this->getParameter('postId');

		$this->database->table('comments')->insert([
			'post_id' => $postId,
			'name' => $data->name,
			'email' => $data->email,
			'content' => $data->content,
		]);

		$this->flashMessage('Thank you for your comment', 'success');
		$this->redirect('this');
	}

	protected function createComponentReview(): ReviewControl
	{
		return $this->reviewControlFactory->create()->setPostId($this->postId);
	}
}
