<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\AdsService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator;

class AdsController
{
  public function __construct(private AdsService $adsService, private Validator $validator)
  {
    $this->validator->mapFieldsRules([
      'title' => ['required', ['lengthMin', 4]],
      'description' => ['required', ['lengthMin', 4]],
      'image' => ['required', ['lengthMin', 4]],
      'url' => ['required', ['lengthMin', 4]],
      'user_id' => ['required', ['lengthMin', 1]],
    ]);
  }

  public function showAll(Request $request, Response $response): Response
  {
    $data = $this->adsService->readAll();
    $response->getBody()->write(json_encode($data));
    return $response;
  }

  public function show(Request $request, Response $response, string $advertisement_id): Response
  {
    $advertisement = $request->getAttribute('advertisement');
    $response->getBody()->write(json_encode($advertisement));
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

    $advertisement = $this->adsService->create($user, $data);
    $response->getBody()->write(json_encode($advertisement));
    return $response;
  }

  public function update(Request $request, Response $response, string $advertisement_id): Response
  {
    $data = $request->getParsedBody();

    $this->validator = $this->validator->withData($data);
    if (! $this->validator->validate()) {
      $response->getBody()->write(json_encode($this->validator->errors()));
      return $response->withStatus(422);
    }

    $advertisement = $this->adsService->update((int)$advertisement_id, $data);
    $response->getBody()->write(json_encode($advertisement));
    return $response;
  }

  public function delete(Request $request, Response $response, string $advertisement_id): Response
  {
    $advertisement = $this->adsService->delete((int)$advertisement_id);
    $response->getBody()->write(json_encode($advertisement));
    return $response;
  }
}
