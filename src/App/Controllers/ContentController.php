<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Db\Repository\ContentService;
use Slim\Exception\HttpNotFoundException;
use Valitron\Validator;

class ContentController
{
  public function __construct(private ContentService $contentService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'title' => ['required', ['lengthMin', 4]],
      'url' => ['required', ['lengthMin', 4]],
      'footer_id' => ['required'],
    ]);
  }

  public function showAll(Request $request, Response $response): Response
  {
    $data = $this->contentService->readAll();
    $response->getBody()->write(json_encode($data));
    return $response;
  }

  public function create(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $validator = $this->validator->withData($data);
    if (!$validator->validate()) {
      $response->getBody()->write(json_encode($validator->errors()));
      return $response->withStatus(422);
    }

    $footerSection = $request->getAttribute('footerSection');

    $content = $this->contentService->create($footerSection, $data);

    $response->getBody()->write(json_encode($content));

    return $response;
  }

  public function update(Request $request, Response $response, string $content_id): Response
  {
    $data = $request->getParsedBody();

    $validator = $this->validator->withData($data);
    if (!$validator->validate()) {
      $response->getBody()->write(json_encode($validator->errors()));
      return $response->withStatus(422);
    }

    $content = $this->contentService->update((int)$content_id, $data);

    if ($content) {
      $response->getBody()->write(json_encode($content));
      return $response;
    }

    throw new HttpNotFoundException($request, 'Content not found');
  }

  public function delete(Request $request, Response $response, string $content_id): Response
  {
    $content = $this->contentService->delete((int)$content_id);

    if ($content) {
      $response->getBody()->write(json_encode($content));
      return $response;
    }

    throw new HttpNotFoundException($request, 'Content not found');
  }

  public function show(Request $request, Response $response, string $content_id): Response
  {
    $content = $this->contentService->findById((int)$content_id);
    if (!$content) {
      throw new HttpNotFoundException($request, 'Content not found');
    }
    $response->getBody()->write(json_encode($content));
    return $response;
  }
}
