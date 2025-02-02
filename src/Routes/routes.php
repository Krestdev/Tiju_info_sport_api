<?php

declare(strict_types=1);

use App\Controllers\CommentController;
use App\Controllers\UserController;
use App\Controllers\UserIndex;
use App\Middleware\Comment\GetComment;
use App\Middleware\Comment\GetCommentAuthor;
use App\Middleware\Comment\GetParentComment;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\User\GetUser;


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
  $group->post('/comments/{id:[0-9]+}', [CommentController::class, 'responseComment'])->add(GetParentComment::class)->add(GetCommentAuthor::class);
  $group->get('/comments/{comment_id:[0-9]+}', [CommentController::class, 'show'])->add(GetComment::class);

  // Comment likes
  $group->get('/comments/like/{comment_id:[0-9]+}/{id:[0-9]+}', [CommentController::class, 'likeComment'])->add(GetComment::class)->add(GetUser::class);
  $group->get('/comments/unlike/{comment_id:[0-9]+}/{id:[0-9]+}', [CommentController::class, 'unlikeComment'])->add(GetComment::class)->add(GetUser::class);

  // Comment signals
  $group->get('/comments/signal/{comment_id:[0-9]+}/{id:[0-9]+}', [CommentController::class, 'signalComment'])->add(GetComment::class)->add(GetUser::class);
  $group->get('/comments/unsignal/{comment_id:[0-9]+}/{id:[0-9]+}', [CommentController::class, 'unsignalComment'])->add(GetComment::class)->add(GetUser::class);

  $group->patch('/comments/{comment_id:[0-9]+}', [CommentController::class, 'update'])->add(GetComment::class);
  $group->delete('/comments/{comment_id:[0-9]+}', [CommentController::class, 'delete'])->add(GetComment::class);
});
