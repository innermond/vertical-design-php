<?php declare(strict_types=1);
namespace Echoes;

use lib\get_value;

class ArticleID {
	function __construct(private int $value){}
	use get_value;
}


