<?php

declare(strict_types=1);

use App\Controllers\CommentController;
use App\Controllers\UserController;
use App\Controllers\UserIndex;
use App\Middleware\GetComment;
use App\Middleware\GetCommentAuthor;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\GetUser;


$app->group('/api', function (RouteCollectorProxy $group) {

  // user Routes

  $group->get('/users', UserIndex::class);
  $group->post('/users', [UserController::class, 'signup']);
  $group->post('/users/signin', [UserController::class, 'signIn']);

  $group->group('/users', function (RouteCollectorProxy $group) {
    $group->get('/{id:[0-9]+}', [UserController::class, 'show']);
    $group->patch('/{id:[0-9]+}', [UserController::class, 'edit']);
    $group->delete('/{id:[0-9]+}', [UserController::class, 'delete']);
  })->add(GetUser::class);

  // Comments Routes

  $group->post('/comments', [CommentController::class, 'create'])->add(GetCommentAuthor::class);
  $group->get('/comments/{id:[0-9]+}', [CommentController::class, 'show'])->add(GetComment::class);
  $group->patch('/comments/{id:[0-9]+}', [CommentController::class, 'update'])->add(GetComment::class);
  $group->delete('/comments/{id:[0-9]+}', [CommentController::class, 'delete'])->add(GetComment::class);
});
