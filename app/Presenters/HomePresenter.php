<?php

namespace App\Presenters;

use App\Components\ReviewControl;
use App\Components\ReviewControlFactory;
use App\Model\PostFacade;
use Nette;

final class HomePresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private PostFacade $facade,
		private ReviewControlFactory $reviewControlFactory,
	)
	{
	}

	public function renderDefault(): void
	{
		$allPosts = $this->facade->getPublicArticles()->limit(5)->fetchAll();
		$this->template->posts = $allPosts;
	}

	protected function createComponentReview(): ReviewControl
	{
		return $this->reviewControlFactory->create();
	}
}
