<?php

namespace App\Presenters;

use App\Model\PostFacade;
use Nette;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private PostFacade $facade,
	)
	{
	}

	public function renderDefault(): void
	{
		$allPosts = $this->facade->getPublicArticles()->limit(5)->fetchAll();
		$oAR = [];
		$oARC = [];
		foreach ($allPosts as $post) {
			$reviews = $post->related('review');
			$overAllRating = 0;
			foreach ($reviews as $review) {
				$overAllRating += $review->review;
			}
			if (count($reviews) != 0) {
				$overAllRating = $overAllRating / count($reviews);
				$oAR[$post->id] = $overAllRating;
			} else {
				$oAR[$post->id] = 0;
			}

			$oARC[$post->id] = count($reviews);

		}
		$this->template->overAllReview = $oAR;
		$this->template->overAllReviewCount = $oARC;

		$this->template->posts = $allPosts;
	}
}
