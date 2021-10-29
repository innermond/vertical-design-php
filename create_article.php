<?php declare(strict_types=1);
// top down direction means from abstact to concret a la clean architecture style
// till descend along abstraction axis we go from custom created types/classes to primitives
// __construct is about boottime; methods are about runtime
// __construct is used from above; methods are used from bellow 
// keeping all vertical till we are forced by development to go horizontal
// we need tests so we need interfaces

require './vendor/autoload.php';

use Echoes\Article;
use Echoes\ArticleID;
use Echoes\Title;
use Echoes\Body;

class CreateArticle {
	function __construct (
		private CreateArticleStore $store, 
		private ArticleValidator $validator, 
	){}

	function execute(CreateArticleInput $input): CreateArtileOutput {
		try {
			$title = $input->title;
			$body = $input->body;
			$article = new Article(new Title($title), new Body($body));
			$is_valid = $this->validator->validate($article);
			if (!$is_valid) throw new \Exception('validation failed');
			$this->store->create($article);
			return new CreateArtileOutput($article);
		} catch (\Exception $e) {
			throw new CreateArticleException('cannot create article', previous: $e);
		}
	}
}

class CreateArticleInput {
	function __construct(public string $title, public string $body){}
}

class CreateArtileOutput {
	function __construct(private Article $article){}
	
	function toArray(): array {
		return [
			'id' => $this->article->id->value(),
			'title' => $this->article->title->value(),
			'body' => $this->article->body->value(),
		];
	}
}

class CreateArticleException extends Exception {}

interface ArticleValidator {
	function validate(Article $article): bool;
}

class CreateArticleValidator implements ArticleValidator {
	function __construct() {}

	function validate(Article $article): bool {
		if (str_starts_with($article->title->value(), 'A')) return false;	
		if (str_starts_with($article->body->value(), 'Z')) return false;	
		return true;
	}
}

interface CreateArticleStore {
	function create(Article $article): ArticleID;
}

// implementations
class CreateArticleSqliteAdapter implements CreateArticleStore {
	function __construct(private \PDO $pdo){}
	
	function create(Article $article): ArticleID {
		$sql = 'INSERT INTO echoes(title, body) VALUES(:title, :body)';
		$stmt = $this->pdo->prepare($sql);
		$stmt->bindValue(':title', $article->title->value());
		$stmt->bindValue(':body', $article->body->value());
		$stmt->execute();

		$id = (int)$this->pdo->lastInsertId();
		$article->id = new ArticleID($id);
		return $article->id;
	}
} 

class JsonPresenter {
	function __construct(private CreateArtileOutput $article){}

	function out(): string {
		return json_encode($this->article->toArray());
	}
}

class CreateArticleOperation {
	
	function __construct(private ?\PDO $dbh = null) {
		if (!$this->dbh) $this->db();
	}

	private function db() {
		// storage
		$dbh = null;
		try {
			$opt = [
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				\PDO::ATTR_EMULATE_PREPARES => false,
				\PDO::ATTR_PERSISTENT => false, 
			];
			$dbh = new \PDO(dsn: 'sqlite:./echo.sq3', options: $opt);
		} catch(\PDOException $e) {
			throw new \PDOException($e->getMessage(), (int)$e->getCode());
		}
		$this->dbh = $dbh;
	}

	static function run(...$aa) {
		$me = new self();
		$me->app();
		return $me(...$aa);
	}
	
	// app functions that knows all how to join all the parts
	function __invoke(string $title, string $body): void {
		$create_article = new CreateArticle(
			new CreateArticleSqliteAdapter($this->dbh), 
			new CreateArticleValidator(),
		);
		$out = $create_article->execute(new CreateArticleInput($title, $body));
		$p = new JsonPresenter($out);
		echo $p->out();
	}
}

/*function create_table() {
	global $dbh;
	$dbh->exec('CREATE TABLE IF NOT EXISTS echoes (
								id   INTEGER PRIMARY KEY,
								title TEXT NOT NULL,
								body TEXT NOT NULL)');
}
create_table();*/

