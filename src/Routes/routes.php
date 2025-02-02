<?php

declare(strict_types=1);

use App\Controllers\ArticleController;
use App\Controllers\CategoryController;
use App\Controllers\CommentController;
use App\Controllers\UserController;
use App\Controllers\UserIndex;
use App\Middleware\Articles\GetArticle;
use App\Middleware\Articles\GetArticleAuthor;
use App\Middleware\Category\GetCategory;
use App\Middleware\Category\GetCategoryAuthor as CategoryGetCategoryAuthor;
use App\Middleware\Comment\GetComment;
use App\Middleware\Comment\GetCommentAuthor;
use App\Middleware\Comment\GetParentComment;
use App\Middleware\Comment\IdentifyUser;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\User\GetUser;

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

  $group->post('/articles', [ArticleController::class, 'create'])->add(GetArticleAuthor::class);

  $group->group('/articles', function (RouteCollectorProxy $group) {
    $group->get('/{article_id:[0-9]+}', [ArticleController::class, 'show']);
    $group->patch('/{article_id:[0-9]+}', [ArticleController::class, 'update']);
    $group->delete('/{article_id:[0-9]+}', [ArticleController::class, 'delete']);

    // Article likes
    $group->group('', function (RouteCollectorProxy $group) {
      $group->patch('/like/{article_id:[0-9]+}', [ArticleController::class, 'likeArticle']);
      $group->patch('/unlike/{article_id:[0-9]+}', [ArticleController::class, 'unlikeArticle']);
    })->add(IdentifyUser::class);
  })->add(GetArticle::class);

  // Category Routes

  $group->get('/category', [CategoryController::class, 'showAll']);
  $group->post('/category', [CategoryController::class, 'create'])->add(CategoryGetCategoryAuthor::class);
  $group->get('/category/{category_id:[0-9]+}', [CategoryController::class, 'show'])->add(GetCategory::class); // needs get category MW
  $group->patch('/category/{category_id:[0-9]+}', [CategoryController::class, 'update'])->add(GetCategory::class); // needs get category MW
  $group->delete('/category/{category_id:[0-9]+}', [CategoryController::class, 'delete'])->add(GetCategory::class); // needs get category MW
});
