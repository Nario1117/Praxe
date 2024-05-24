<?php

namespace App\Presenters;

use App\Components\ReviewControl;
use App\Components\ReviewControlFactory;
use Nette;
use Nette\Application\UI\Form;

final class TagsPresenter extends Nette\Application\UI\Presenter
{

	protected $tagId;

	public function __construct(
		private Nette\Database\Explorer $database,
	)
	{
	}

	public function createComponentAddTags(string $name): Form
	{
		$form = new Form;

		if ($this->tagId) {
			$tag = $this->database->table("tags")->get($this->tagId);
			\Tracy\Debugger::barDump($tag["name"]);
			$tagName = $tag["name"];

			$form->addText("tagName", "Tag name")
				->setDefaultValue($tagName)
				->setRequired();
		} else{
			$form->addText("tagName", "Tag name")->setRequired();

		}
		$form->addSubmit("send", "Save tag");
		$form->onSuccess[] = $this->addTagsSucceeded(...);

		return $form;
	}

	private function addTagsSucceeded(array $data)
	{


		if ($this->tagId) {
			try {
				$this->database->query('UPDATE tags SET', [
					'name' => $data["tagName"],
				], 'WHERE id = ?', $this->tagId);
			} catch (Nette\Database\UniqueConstraintViolationException $e) {
				$this->flashMessage("Tento tag již existuje");
			}
			$this->redirect("this");
		} else {
			\Tracy\Debugger::barDump("creatinggg");

			try {
				$this->database->table('tags')->insert([
					'name' => $data["tagName"]]);
			} catch (Nette\Database\UniqueConstraintViolationException $e) {
				$this->flashMessage("Tento tag již existuje");
			}
			$this->redirect("this");
		}
	}

	public function renderTags()
	{
		$this->template->tags = $this->database->table("tags");

	}

	public function handleClick(string $id): void
	{
		if (!$this->getPresenter()->getUser()->isLoggedIn()) {
			$this->getPresenter()->redirect('Sign:in');
		}

		$ult = $this->database->query('DELETE FROM tags WHERE id = ' . $id);

	}

	public function actionEdit(int $id): void
	{
		if (!$this->getPresenter()->getUser()->isLoggedIn()) {
			$this->getPresenter()->redirect('Sign:in');
		}

		$this->tagId = $id;


		$this->template->tag = $this->database->table("tags")->get($id);
		$this->template->id = $id;
	}
}
