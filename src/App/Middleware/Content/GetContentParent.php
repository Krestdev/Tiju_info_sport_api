<?php

declare(strict_types=1);

namespace App\Middleware\Content;

use App\Db\Repository\CommentService;
use App\Db\Repository\FooterSectionService;
use App\Db\Repository\UserService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetContentParent
{
  public function __construct(private FooterSectionService $footerSectionService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {

    $footer_id = $request->getParsedBody()['footer_id'] ?? null;
    if (!isset($footer_id)) {
      throw new HttpNotFoundException($request, 'Parent Footer required');
    }

    $footerSection = $this->footerSectionService->findById((int)$footer_id);

    if ($footerSection === null) {
      throw new HttpNotFoundException($request, "Parent Footer not found");
    }

    $request = $request->withAttribute('footerSection', $footerSection);

    return $handler->handle($request);
  }
}
