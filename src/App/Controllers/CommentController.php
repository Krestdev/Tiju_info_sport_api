<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Db\Repository\CommentService;
use App\Db\Repository\UserService;
use App\Db\Schema\CommentSchema;
use App\Db\Schema\UserSchema;
use Doctrine\ORM\EntityManager;
use Slim\Exception\HttpNotFoundException;
use Valitron\Validator;

class CommentController
{
  public function __construct(private CommentService $commentService, private UserService $userService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'user_id' => ['required', ['lengthMin', 1]],
      'message' => ['required', ['lengthMin', 1]]
    ]);
  }

  public function show(Request $request, Response $response, string $id): Response
  {
    $comment = $request->getAttribute('comment');
    $response->getBody()->write(json_encode($comment));
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

    $user = $this->userService->findById((int)$data['user_id']);

    $comment = $this->commentService->create($user, $data);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function update(Request $request, Response $response, string $id): Response
  {
    $data = $request->getParsedBody();
    $comment = $this->commentService->update((int)$id, $data);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function delete(Request $request, Response $response, string $id): Response
  {
    $comment = $this->commentService->delete((int)$id);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }
}
