<?php

namespace App\Components;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;

class ReviewControl extends Control
{

	protected int|null $postId;

	public function __construct(
		private Explorer $database,
	)
	{
	}

	public function setPostId(?int $postId): static
	{
		$this->postId = $postId;
		return $this;
	}

	public function renderSummary(ActiveRow $post): void
	{
		$id = $post->id;
		$promena = $this->database->query('SELECT SUM(review), COUNT(review) FROM `review` WHERE `post_id` = ' . $id)->fetchAll();
		$result = $promena[0];
		$this->template->oAR = 0;
		if ($result[1] != 0) {
			$this->template->oAR = $result[0] / $result[1];

		}
		$this->template->oARCount = $result[1];

		$this->template->render(__DIR__ . "/review-summary.latte");
	}

	public function renderList(ActiveRow $post): void
	{
		$reviews = $post->related('review');
		$this->template->reviews = $reviews;

		$this->template->render(__DIR__ . "/review-list.latte");
	}

	public function renderForm(): void
	{
		$this->template->render(__DIR__ . "/review-form.latte");
	}
	protected function createComponentReviewForm(): Form
	{
		$form = new Form(); // means Nette\Application\UI\Form

		$form->addText('name', 'Your name:')
			->setRequired();
		$form->addText('review', 'rating:')
			->setRequired()
			->addRule($form::Range, 'You must add rating from 1 to 10', [1, 10]);


		$form->addTextArea('content', 'Content:')
			->setRequired();

		$form->addSubmit('send', 'Publish review');

		$form->onSuccess[] = $this->reviewFormSucceeded(...);

		return $form;
	}
	private function reviewFormSucceeded(\stdClass $data): void
	{
		$this->database->table('review')->insert([
			'post_id' => $this->postId,
			'name' => $data->name,
			'content' => $data->content,
			'review' => $data->review,

		]);

		$this->flashMessage('Thank you for your review', 'success');
		$this->redirect('this');
	}

	public function handleClick(string $id): void
	{
		if (!$this->getPresenter()->getUser()->isLoggedIn()) {
			$this->getPresenter()->redirect('Sign:in');
		}

			$ult = $this->database->query('DELETE FROM review WHERE id = '.$id);

	}
}