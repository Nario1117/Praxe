<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private Nette\Database\Explorer $database,
	)
	{
	}

	public function renderShow(int $postId): void
	{
		$post = $this->database->table('posts')->get($postId);
		$reviews = $post->related('review')->order('created_at');

		$this->template->post = $post;
		$this->template->comments = $post->related('comments')->order('created_at');
		$this->template->reviews = $reviews;

		$overAllRating = 0;
		foreach ($reviews as $review) {
			$overAllRating += $review->review;
		}
		if (count($reviews) != 0){
			$overAllRating = $overAllRating / count($reviews);
		}

		$this->template->overAllReview = $overAllRating;
		$this->template->overAllReviewCount = count($reviews);
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


	protected function createComponentReviewForm(): Form
	{
		$form = new Form; // means Nette\Application\UI\Form

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

	private function reviewFormSucceeded(\stdClass $data): void
	{
		$postId = $this->getParameter('postId');

		$this->database->table('review')->insert([
			'post_id' => $postId,
			'name' => $data->name,
			'content' => $data->content,
			'review' => $data->review,

		]);

		$this->flashMessage('Thank you for your review', 'success');
		$this->redirect('this');
	}
}
