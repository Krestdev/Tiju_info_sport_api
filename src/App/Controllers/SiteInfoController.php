<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\SiteInfoService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Db\Repository\UserService;
use Valitron\Validator;

class SiteInfoController
{
  public function __construct(private SiteInfoService $siteInfoService, private UserService $userService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'company' => ['required', ['lengthMin', 2]],
      'logo' => ['required', ['lengthMin', 2]],
      'email' => ['required', ['lengthMin', 2]],
      'phone' => ['required', ['lengthMin', 2]],
      'address' => ['required', ['lengthMin', 2]],
      'facebook' => ['required', ['lengthMin', 2]],
      'instagram' => ['required', ['lengthMin', 2]],
      'x' => ['required', ['lengthMin', 2]],
      'description' => ['required', ['lengthMin', 2]]
    ]);
  }

  public function showAll(Request $request, Response $response): Response
  {
    $articles = $this->siteInfoService->readAll();
    $response->getBody()->write(json_encode($articles));
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

    $article = $this->siteInfoService->create($data);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function update(Request $request, Response $response, string $company_id): Response
  {
    $data = $request->getParsedBody();
    $this->validator = $this->validator->withData($data);
    if (!$this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }
    $article = $this->siteInfoService->update((int)$company_id, $data);
    $response->getBody()->write(json_encode($article));
    return $response;
  }

  public function delete(Request $request, Response $response, string $company_id): Response
  {
    $company = $this->siteInfoService->delete((int)$company_id);
    $response->getBody()->write(json_encode($company));
    return $response;
  }
}
