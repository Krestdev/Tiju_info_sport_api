<?php

declare(strict_types=1);

namespace App\Middleware\Image;

use App\Db\Repository\AdsService;
use App\Db\Repository\ArticleService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;

class GetImageOwner
{
  public function __construct(private UserService $user, private AdsService $ads, private ArticleService $article) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $body = $request->getParsedBody();

    if (key_exists("user_id", $body)) {
      $user_id = $body["user_id"];
      $data = $this->user->findById((int)$user_id);

      if ($data === null) {
        throw new HttpNotFoundException($request, "Author user not found");
      }

      $request = $request->withAttribute("user", $data);
    } elseif (key_exists("ads_id", $body)) {
      $ads_id = $body["ads_id"];
      $data = $this->ads->findById((int)$ads_id);

      if ($data === null) {
        throw new HttpNotFoundException($request, "Advertisment user not found");
      }

      $request = $request->withAttribute("ads", $data);
    } elseif (key_exists("article_id", $body)) {
      $article_id = $body["article_id"];
      $data = $this->article->findById((int)$article_id);

      if ($data === null) {
        throw new HttpNotFoundException($request, "Article user not found");
      }

      $request = $request->withAttribute("article", $data);
    } else {
      throw new HttpNotFoundException($request, "'user_id','article_id' or 'ads_id' is required ");
    }


    return $handler->handle($request);
  }
}
