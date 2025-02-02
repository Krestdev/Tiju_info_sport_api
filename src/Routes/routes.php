<?php

declare(strict_types=1);

use App\Controllers\ArticleController;
use App\Controllers\CommentController;
use App\Controllers\UserController;
use App\Controllers\UserIndex;
use App\Middleware\Articles\GetArticle;
use App\Middleware\Articles\GetArticleAuthor;
use App\Middleware\Comment\GetComment;
use App\Middleware\Comment\GetCommentAuthor;
use App\Middleware\Comment\GetParentComment;
use App\Middleware\Comment\IdentifyUser;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\User\GetUser;
use Doctrine\DBAL\Schema\Identifier;

$app->group('/api', function (RouteCollectorProxy $group) {

  // user Routes

  $group->get('/users', UserIndex::class);
  $group->post('/users', [UserController::class, 'signup']);
  $group->post('/users/signin', [UserController::class, 'signIn']);

  $group->group('/users', function (RouteCollectorProxy $group) {
    $group->get('/{user_id:[0-9]+}', [UserController::class, 'show']);
    $group->patch('/{user_id:[0-9]+}', [UserController::class, 'edit']);
    $group->delete('/{user_id:[0-9]+}', [UserController::class, 'delete']);
  })->add(GetUser::class);

  // Comments Routes

  $group->group('/comments', function (RouteCollectorProxy $group) {

    $group->post('/{article_id:[0-9]+}', [CommentController::class, 'create'])->add(GetCommentAuthor::class)->add(GetArticle::class);
    $group->post('/{article_id:[0-9]+}/{user_id:[0-9]+}', [CommentController::class, 'responseComment'])->add(GetParentComment::class)->add(GetCommentAuthor::class)->add(GetArticle::class);
    $group->get('/{comment_id:[0-9]+}', [CommentController::class, 'show'])->add(GetComment::class);

    $group->group('', function (RouteCollectorProxy $group) {
      // Comment likes
      $group->patch('/like/{comment_id:[0-9]+}', [CommentController::class, 'likeComment']);
      $group->patch('/unlike/{comment_id:[0-9]+}', [CommentController::class, 'unlikeComment']);

      // Comment signals
      $group->patch('/signal/{comment_id:[0-9]+}', [CommentController::class, 'signalComment']);
      $group->patch('/unsignal/{comment_id:[0-9]+}', [CommentController::class, 'unsignalComment']);
    })->add(GetComment::class)->add(IdentifyUser::class);

    $group->patch('/{comment_id:[0-9]+}', [CommentController::class, 'update'])->add(GetComment::class);
    $group->delete('/{comment_id:[0-9]+}', [CommentController::class, 'delete'])->add(GetComment::class);
  });


  // Article Routes

  $group->get('/articles/{article_id:[0-9]+}', [ArticleController::class, 'show'])->add(GetArticle::class);
  $group->post('/articles', [ArticleController::class, 'create'])->add(GetArticleAuthor::class);
  $group->patch('/articles/{article_id:[0-9]+}', [ArticleController::class, 'update'])->add(GetArticle::class);
  $group->delete('/articles/{article_id:[0-9]+}', [ArticleController::class, 'delete'])->add(GetArticle::class);

  // Article likes
  $group->patch('/articles/like/{article_id:[0-9]+}', [ArticleController::class, 'likeArticle'])->add(GetArticle::class)->add(IdentifyUser::class);
  $group->patch('/articles/unlike/{article_id:[0-9]+}', [ArticleController::class, 'unlikeArticle'])->add(GetArticle::class)->add(IdentifyUser::class);
});
