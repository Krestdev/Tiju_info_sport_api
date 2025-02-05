<?php

declare(strict_types=1);

namespace App\Middleware\Image;

use App\Db\Repository\ImageService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetImage
{
  public function __construct(private ImageService $imageService) {}

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();

    $id = $route->getArgument("image_id");
    $image = $this->imageService->findById((int)$id);

    if ($image === null) {
      throw new HttpNotFoundException($request, "Image not found");
    }

    $request = $request->withAttribute("image", $image);
    return $handler->handle($request);
  }
}
