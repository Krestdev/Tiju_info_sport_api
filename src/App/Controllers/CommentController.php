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

  public function show(Request $request, Response $response, string $comment_id): Response
  {
    $comment = $request->getAttribute('comment');
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function create(Request $request, Response $response, string $article_id): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $request->getAttribute('author');
    $article = $request->getAttribute('article');

    $comment = $this->commentService->create($user, $article, $data);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function update(Request $request, Response $response, string $comment_id): Response
  {
    $data = $request->getParsedBody();
    $comment = $this->commentService->update((int)$comment_id, $data);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function responseComment(Request $request, Response $response, string $article_id, string $id): Response
  {
    $parentComment = $request->getAttribute('parentComment');
    $author = $request->getAttribute('author');
    $article = $request->getAttribute('article');
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $comment = $this->commentService->reponseToComment($author, $parentComment, $article, $data);

    $response->getBody()->write(json_encode($comment));
    return $response;
    // $comment = $this->commentService->reponseToComment()
  }

  // like comments
  public function likeComment(Request $request, Response $response, string $comment_id): Response
  {
    $user = $request->getAttribute('user');
    $comment = $request->getAttribute('comment');
    $comment = $this->commentService->likeComment($user, $comment);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function unlikeComment(Request $request, Response $response, string $comment_id): Response
  {
    $user = $request->getAttribute('user');
    $comment = $request->getAttribute('comment');
    $comment = $this->commentService->unlikeComment($user, $comment);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  //signal comments
  public function signalComment(Request $request, Response $response, string $comment_id): Response
  {
    $user = $request->getAttribute('user');
    $comment = $request->getAttribute('comment');
    $comment = $this->commentService->signalComment($user, $comment);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function unsignalComment(Request $request, Response $response, string $comment_id): Response
  {
    $user = $request->getAttribute('user');
    $comment = $request->getAttribute('comment');
    $comment = $this->commentService->unsignalComment($user, $comment);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }

  public function delete(Request $request, Response $response, string $comment_id): Response
  {
    $comment = $this->commentService->delete((int)$comment_id);
    $response->getBody()->write(json_encode($comment));
    return $response;
  }
}
