<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\ArticleService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Db\Repository\UserService;
use Valitron\Validator;

class ArticleController
{
  public function __construct(private ArticleService $articleService, private UserService $userService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'title' => ['required', ['lengthMin', 2]],
      'summary' => ['required', ['lengthMin', 2]],
      'description' => ['required', ['lengthMin', 2]],
      'type' => ['required', ['lengthMin', 2]],
      'user_id' => ['required', ['lengthMin', 1]],
    ]);
  }

  public function showAll(Request $request, Response $response, array $args): Response
  {
    $articles = $this->articleService->readAll();
    $response->getBody()->write(json_encode($articles));
    return $response;
  }

  public function show(Request $request, Response $response, string $article_id): Response
  {
    $article = $request->getAttribute('article');
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function create(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $request->getAttribute('author');
    $category = $request->getAttribute('category');

    $article = $this->articleService->create($user, $category, $data);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function update(Request $request, Response $response, string $article_id): Response
  {
    $data = $request->getParsedBody();
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }
    $article = $this->articleService->update((int)$article_id, $data);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function delete(Request $request, Response $response, string $article_id): Response
  {
    $article = $this->articleService->delete((int)$article_id);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  // article likes

  public function likeArticle(Request $request, Response $response, string $article_id): Response
  {
    $user = $request->getAttribute('user');
    $article = $request->getAttribute('article');
    $article = $this->articleService->likeArticle($user, $article);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function unlikeArticle(Request $request, Response $response, string $article_id): Response
  {
    $user = $request->getAttribute('user');
    $article = $request->getAttribute('article');
    $article = $this->articleService->unlikeArticle($user, $article);
    $response->getBody()->write(json_encode($article));
    return $response;
  }
}
