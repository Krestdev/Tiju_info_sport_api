<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\ContentService;
use App\Db\Repository\FooterSectionService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpNotFoundException;
use Valitron\Validator;

class FooterController
{
  public function __construct(private FooterSectionService $footerSectionService, private ContentService $contentService,  private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'title' => ['required', ['lengthMin', 1]],
      'message' => ['required', ['lengthMin', 1]]
    ]);
  }

  public function create(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $validatorLogin = new Validator($data);

    $validatorLogin->mapFieldsRules([
      'title' => ['required', ['lengthMin', 8]],
    ]);

    $validatorLogin = $validatorLogin->withData($data);
    if (!$validatorLogin->validate()) {
      $response->getBody()->write(json_encode($validatorLogin->errors()));
      return $response->withStatus(422);
    }

    $footerSection = $this->footerSectionService->create($data['title']);
    $response->getBody()->write(json_encode($footerSection));
    return $response;
  }

  public function update(Request $request, Response $response, string $footer_id): Response
  {
    $data = $request->getParsedBody();

    $validatorLogin = new Validator($data);

    $validatorLogin->mapFieldsRules([
      'title' => ['required', ['lengthMin', 8]],
    ]);

    $validatorLogin = $validatorLogin->withData($data);
    if (!$validatorLogin->validate()) {
      $response->getBody()->write(json_encode($validatorLogin->errors()));
      return $response->withStatus(422);
    }

    $footerSection = $this->footerSectionService->update((int)$footer_id, $data['title']);
    if ($footerSection) {
      $response->getBody()->write(json_encode($footerSection));
      return $response;
    }
    throw new HttpNotFoundException($request, 'Section not found');
  }

  public function delete(Request $request, Response $response, string $footer_id): Response
  {
    $footerSection = $this->footerSectionService->delete((int)$footer_id);
    if ($footerSection) {
      $response->getBody()->write(json_encode($footerSection));
      return $response;
    }
    throw new HttpNotFoundException($request, 'Section not found');
  }
  public function show(Request $request, Response $response, string $footer_id): Response
  {
    $footerSection = $this->footerSectionService->findById((int)$footer_id);
    if ($footerSection) {
      $response->getBody()->write(json_encode($footerSection));
      return $response;
    }
    throw new HttpNotFoundException($request, 'Section not found');
  }

  public function showAll(Request $request, Response $response): Response
  {
    try {
      $footerSections = $this->footerSectionService->readAll();
      $response->getBody()->write(json_encode($footerSections));
      return $response;
    } catch (Exception $e) {
      throw new HttpNotFoundException($request, $e->getMessage());
    }
  }
}
