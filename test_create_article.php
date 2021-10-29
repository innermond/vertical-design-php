<?php declare(strict_types=1);

use Echoes\Article;
use Echoes\ArticleID;
use Echoes\Title;
use Echoes\Body;

use PHPUnit\Framework\TestCase;

include './create_article.php';

final class test_create_article extends TestCase {
		/**
		 * @dataProvider articlesProvider
		 */
    function testArticleAfterCreationHasIdZero(string $t, string $b): Article {
			$ar = $this->create_article($t, $b);
			$this->assertInstanceOf(
					ArticleID::class,
					$ar->id
			);
			$this->assertSame($ar->id->value(), 0);
			return $ar;
    }

		private function create_article(string $t, string $b): Article {
			$t = new Title($t);
			$b = new Body($b);
			return new Article($t, $b);
		}

		function articlesProvider(): array {
			return [
				['one_a', 'one_b'],
				['one_c', 'one_d'],
				['one_e', 'one_f'],
				['one_g', 'one_h'],
			]; 	
		}

		function testArticleCreation(): Article {
			$a = $this->create_article('a', 'b');
			$this->assertInstanceOf(Article::class, $a);
			return $a;
		}

		/**
		 * @dataProvider articlesProvider
		 */
		function testArticleValidator(string ...$a) {
			$v = new CreateArticleValidator();
			$this->assertTrue(
				$v->validate(
					$this->create_article(...$a)
				)
			);
		}

		/**
		 * @dataProvider articlesFalseProvider
		 */
		function testArticleValidatorReturnsFalse(string ...$a) {
			$v = new CreateArticleValidator();
			$this->assertFalse(
				$v->validate(
					$this->create_article(...$a)
				)
			);
		}

		function articlesFalseProvider(): array {
			return [
				['A_one_a', 'one_b'],
				['A_one_c', 'one_d'],
				['A_one_e', 'one_f'],
				['A_one_g', 'one_h'],
			]; 	
		}

}
