<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\PackageService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;

class PackageController
{
  public function __construct(private PackageService $packageService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'title' => ['required', ['lengthMin', 4]],
      'price' => ['required', 'integer'],
      'user_id' => ['required', ['lengthMin', 1]],
      'period' => ['required', 'numeric', ['min', 1]],
    ]);
  }

  public function showAll(Request $request, Response $response): Response
  {
    $data = $this->packageService->readAll();
    $response->getBody()->write(json_encode($data));
    return $response;
  }

  public function show(Request $request, Response $response, string $package_id): Response
  {
    $package = $request->getAttribute('package');
    $response->getBody()->write(json_encode($package));
    return $response;
  }

  public function create(Request $request, Response $response): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (! $this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $user = $request->getAttribute('author');

    $package = $this->packageService->create($user, $data);
    $response->getBody()->write(json_encode($package));
    return $response;
  }

  public function update(Request $request, Response $response, string $package_id): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (! $this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $package = $this->packageService->update((int)$package_id, $data);
    $response->getBody()->write(json_encode($package));
    return $response;
  }

  public function delete(Request $request, Response $response, string $package_id): Response
  {
    $package = $this->packageService->delete((int)$package_id);
    $response->getBody()->write(json_encode($package));
    return $response;
  }
}
