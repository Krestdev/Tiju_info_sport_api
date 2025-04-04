<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Db\Repository\AdsService;
use App\Db\Repository\ArticleService;
use App\Db\Repository\ImageService;
use App\Db\Repository\UserService;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ImageController
{
  public function __construct(private AdsService $adsService, private UserService $userService, private ArticleService $articleService, private ImageService $imageService) {}

  public function validateData(array $files, Response $response)
  {
    if (! array_key_exists("file", $files)) {
      $response->getBody()->write(json_encode("'file' missing from request data"));

      return $response->withStatus(422);
    }

    $file = $files["file"];

    $error = $file->getError();

    if ($error !== UPLOAD_ERR_OK) {
      return $response->withStatus(422);
    }

    if ($file->getSize() > 5000000) {
      return $response->withStatus(422);
    }

    $mediaTypes = ["image/png", "image/jpeg", "image/webp", "image/jpeg"];
    if (! in_array($file->getClientMediaType(), $mediaTypes)) {
      return $response->withStatus(422);
    }

    return $file;
  }

  public function save(Request $request, Response $response): Response
  {
    $files = $request->getUploadedFiles();
    $file = $this->validateData($files, $response);

    $user = $request->getAttribute("user");
    $article = $request->getAttribute("article");
    $advertisment = $request->getAttribute("ads");
    $data = [
      "size" => $file->getSize()
    ];

    try {

      $type = explode("/", $file->getClientMediaType());

      if ($user) {
        $data['location'] = "/uploads/users/" . $user->getId() . "." . end($type);

        $file->moveTo(dirname(__DIR__, 3) . $data["location"]);

        $image = $this->imageService->createUserProfile($user, $data);
      } elseif ($article) {
        print("Hello");
        $imageData = $this->imageService->createBaseImage($data);

        $data["location"] = "/uploads/articles/" . $article->getId() . "_image_" . $imageData->getId() . "." . end($type);

        $file->moveTo(dirname(__DIR__, 3) . $data["location"]);

        $image = $this->imageService->addArticleImage($article, $imageData, $data);
      } elseif ($advertisment) {
        $data["location"] = "/uploads/ads/" . $advertisment->getId() . "." . end($type);

        $file->moveTo(dirname(__DIR__, 3) . $data["location"]);

        $image = $this->imageService->createAdsImage($advertisment, $data);
      } else {
        return $response->withStatus(422, "Some information is lacking");
      }

      $response->getBody()->write(json_encode($image));

      return $response->withStatus(201)->withHeader('Content-Type', 'application/json');;
    } catch (Exception $error) {
      return $response->withStatus(500)->withHeader('Content-Type', 'application/json');;
    }
  }

  public function updateImage(Request $request, Response $response, string $image_id): Response
  {
    $files = $request->getUploadedFiles();
    $file = $this->validateData($files, $response);
    $image = $request->getAttribute("image");

    $user = $request->getAttribute("user");
    $article = $request->getAttribute("article");
    $advertisment = $request->getAttribute("advertisement");

    $data = [
      "size" => $file->getSize()
    ];

    try {
      $type = explode("/", $file->getClientMediaType());

      if ($user) {
        $data['location'] = "/uploads/users/" . $user->getId() . "." . end($type);

        $file->moveTo(dirname(__DIR__, 3) . $data["location"]);
      } elseif ($article) {
        $data["location"] = "/uploads/articles/" . $article->getId() . "_image_" . $image_id . "." . end($type);
        $file->moveTo(dirname(__DIR__, 3) . $data["location"]);
      } elseif ($advertisment) {
        $data["location"] = "/uploads/ads/" . $advertisment->getId() . "." . end($type);
        $file->moveTo(dirname(__DIR__, 3) . $data["location"]);
      }

      $this->imageService->update($image->getId(), $data);

      return $response->withStatus(201);
    } catch (Exception $error) {
      return $response->withStatus(500);
    }
  }

  public function read(Request $request, Response $response, string $image_id): Response
  {
    $image = $request->getAttribute("image");

    $contents = file_get_contents(dirname(__DIR__, 3) . $image->getLocation());
    $response->getBody()->write($contents);

    return $response->withHeader("Content-type", "image/png");
  }

  public function deleteImage(Request $request, Response $response, string $image_id): Response
  {
    $image = $request->getAttribute("image");
    unlink(dirname(__DIR__, 3) . $image->getLocation());
    $this->imageService->delete((int)$image_id);

    return $response;
  }
}
