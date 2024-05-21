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
		$reviews = $post->related('review');
		$overAllRating = 0;
		foreach ($reviews as $review) {
			$overAllRating += $review->review;
		}
		if (count($reviews) != 0) {
			$overAllRating = $overAllRating / count($reviews);
			$oAR = $overAllRating;
		} else {
			$oAR = 0;
		}
		$oARCount = count($reviews);
		$this->template->oAR = $oAR;
		$this->template->oARCount = $oARCount;
		$this->template->render(__DIR__ . "/review-summary.latte");
	}

	public function renderList(ActiveRow $post): void
	{
		$reviews = $post->related('review');
		$this->template->reviews = $reviews;

		$this->template->render(__DIR__ . "/review-list.latte");
	}

	public function renderForm(ActiveRow $post): void
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