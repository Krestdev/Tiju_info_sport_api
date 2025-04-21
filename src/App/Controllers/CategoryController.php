<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\CategoryService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Db\Repository\UserService;
use Valitron\Validator;

class CategoryController
{
  public function __construct(private CategoryService $categoryService, private UserService $userService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'title' => ['required', ['lengthMin', 2]],
      'slug' => ['required', ['lengthMin', 2]],
      // 'image' => ['required', ['lengthMin', 2]],
      'description' => ['required', ['lengthMin', 2]],
      'user_id' => ['required', ['lengthMin', 1]],
      'color' => [['lengthMin', 2]]
    ]);
  }

  public function showAll(Request $request, Response $response): Response
  {
    $user = $this->categoryService->readAll();
    $response->getBody()->write(json_encode($user));
    return $response;
  }

  public function show(Request $request, Response $response, string $category_id): Response
  {
    $article = $request->getAttribute('category');
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

    $article = $this->categoryService->create($user, $data);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function createChild(Request $request, Response $response, string $parent_id): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $request->getAttribute('author');
    $parentCategory = $request->getAttribute('parentCategory');

    $category = $this->categoryService->createChild($user, $parentCategory, $data);
    $response->getBody()->write(json_encode($category));
    return $response;
  }

  public function update(Request $request, Response $response, string $category_id): Response
  {
    $data = $request->getParsedBody();
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }
    $article = $this->categoryService->update((int)$category_id, $data);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function delete(Request $request, Response $response, string $category_id): Response
  {
    $article = $this->categoryService->delete((int)$category_id);
    $response->getBody()->write(json_encode($article));
    return $response;
  }
}
