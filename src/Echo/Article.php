<?php declare(strict_types=1);
namespace Echoes;

class Article {
	public ArticleID $id;

	function __construct(
		public Title $title,
		public Body $body,
	){
		$this->id = new ArticleID(0);
	}
}
