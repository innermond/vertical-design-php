<?php declare(strict_types=1);
namespace Echoes;

use lib\get_value;

class Body {
	function __construct(private string $value){}
	use get_value;
}
