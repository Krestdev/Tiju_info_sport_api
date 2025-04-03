<?php

declare(strict_types=1);

use App\Controllers\AdsController;
use App\Controllers\ArticleController;
use App\Controllers\CategoryController;
use App\Controllers\CommentController;
use App\Controllers\ImageController;
use App\Controllers\PackageController;
use App\Controllers\PaymentController;
use App\Controllers\SubscriptionController;
use App\Controllers\UserController;
use App\Controllers\UserIndex;
use App\Middleware\AddJasonResponseHeader;
use App\Middleware\Ads\GetAdsAuthor;
use App\Middleware\Ads\GetAds;
use App\Middleware\Articles\GetArticle;
use App\Middleware\Articles\GetArticleAuthor;
use App\Middleware\Authentication\RequireApiKey;
use App\Middleware\Authentication\StartSession;
use App\Middleware\Category\GetCategory;
use App\Middleware\Category\GetCategoryAuthor as CategoryGetCategoryAuthor;
use App\Middleware\Comment\GetComment;
use App\Middleware\Comment\GetCommentAuthor;
use App\Middleware\Comment\GetParentComment;
use App\Middleware\Comment\IdentifyUser;
use App\Middleware\Image\GetImage;
use App\Middleware\Image\GetImageOwner;
use App\Middleware\Package\GetPackage;
use App\Middleware\Package\GetPackageAuthor;
use App\Middleware\Payments\GetPayment;
use App\Middleware\Payments\PaymentUserAndSub;
use App\Middleware\Subscription\GetSubscription;
use App\Middleware\Subscription\GetSubscriptionAuthor;
use Slim\Routing\RouteCollectorProxy;
use App\Middleware\User\GetUser;

$app->group('/api', function (RouteCollectorProxy $group) {

  // user google auth
  $group->get('/users/google/uri', [UserController::class, 'getGoogleUri']);
  $group->get('/users/google/store', [UserController::class, 'storeGoogleUser']);
  $group->get('/sendmail', [UserController::class, "sendMail"]);

  // user Routes

  $group->get('/users', UserIndex::class);
  $group->post('/users', [UserController::class, 'signup'])->add(StartSession::class);
  $group->get('/profile/{user_id:[0-9]+}', [UserController::class, 'show'])->add(GetUser::class);

  $group->post('/users/signin', [UserController::class, 'signIn'])->add(StartSession::class);
  $group->get('/users/signout', [UserController::class, 'logout'])->add(StartSession::class);
  $group->patch('/users/{user_id:[0-9]+}', [UserController::class, 'edit'])->add(GetUser::class);

  $group->group("", function (RouteCollectorProxy $group) {

    $group->group('/users', function (RouteCollectorProxy $group) {
      $group->get('/{user_id:[0-9]+}', [UserController::class, 'show']);
      $group->delete('/{user_id:[0-9]+}', [UserController::class, 'delete']);
    })->add(GetUser::class);
  })->add(RequireApiKey::class);

  // Comments Routes

  $group->group('/comments', function (RouteCollectorProxy $group) {

    $group->post('/{article_id:[0-9]+}', [CommentController::class, 'create'])->add(GetCommentAuthor::class)->add(GetArticle::class);
    $group->post('/{article_id:[0-9]+}/{parent_id:[0-9]+}', [CommentController::class, 'responseComment'])->add(GetParentComment::class)->add(GetCommentAuthor::class)->add(GetArticle::class);
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
  $group->get('/articles', [ArticleController::class, 'showAll']);

  $group->group('/articles', function (RouteCollectorProxy $group) {
    $group->get('/{article_id:[0-9]+}', [ArticleController::class, 'show']);
    $group->patch('/{article_id:[0-9]+}', [ArticleController::class, 'update']);
    $group->patch('/publish/{article_id:[0-9]+}', [ArticleController::class, 'publish']);
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
  $group->get('/category/{category_id:[0-9]+}', [CategoryController::class, 'show'])->add(GetCategory::class);
  $group->patch('/category/{category_id:[0-9]+}', [CategoryController::class, 'update'])->add(GetCategory::class);
  $group->delete('/category/{category_id:[0-9]+}', [CategoryController::class, 'delete'])->add(GetCategory::class);

  // Advertisment
  $group->get('/advertisement', [AdsController::class, 'showAll']);
  $group->post('/advertisement', [AdsController::class, 'create'])->add(GetAdsAuthor::class);
  $group->get('/advertisement/{advertisement_id:[0-9]+}', [AdsController::class, 'show'])->add(GetAds::class);
  $group->patch('/advertisement/{advertisement_id:[0-9]+}', [AdsController::class, 'update'])->add(GetAds::class);
  $group->delete('/advertisement/{advertisement_id:[0-9]+}', [AdsController::class, 'delete'])->add(GetAds::class);

  // Package
  $group->get('/package', [PackageController::class, 'showAll']);
  $group->post('/package', [PackageController::class, 'create'])->add(GetPackageAuthor::class);
  $group->get('/package/{package_id:[0-9]+}', [PackageController::class, 'show'])->add(GetPackage::class);
  $group->patch('/package/{package_id:[0-9]+}', [PackageController::class, 'update'])->add(GetPackage::class);
  $group->delete('/package/{package_id:[0-9]+}', [PackageController::class, 'delete'])->add(GetPackage::class);

  // subscription
  $group->get('/subscription', [SubscriptionController::class, 'showAll']);
  $group->post('/subscription', [SubscriptionController::class, 'create'])->add(GetSubscriptionAuthor::class);
  $group->get('/subscription/{subscription_id:[0-9]+}', [SubscriptionController::class, 'show'])->add(GetSubscription::class);
  $group->patch('/subscription/{subscription_id:[0-9]+}', [SubscriptionController::class, 'update'])->add(GetSubscription::class);
  $group->delete('/subscription/{subscription_id:[0-9]+}', [SubscriptionController::class, 'delete'])->add(GetSubscription::class);


  $group->post("/pay", [PaymentController::class, "makePayment"])->add(PaymentUserAndSub::class);
  $group->get("/pay/{payment_id:[0-9]+}", [PaymentController::class, "getPaymentById"])->add(GetPayment::class);
  $group->get("/pay/retry/{payment_id:[0-9]+}", [PaymentController::class, "retryPayment"])->add(GetPayment::class);
  $group->get("/pay/check/{payment_id:[0-9]+}", [PaymentController::class, "checkpaymentStatus"])->add(GetPayment::class);
  $group->delete("/pay/delete/{payment_id:[0-9]+}", [PaymentController::class, "deletePayment"])->add(GetPayment::class);
})->add(new AddJasonResponseHeader);

// images
$app->group('/api', function (RouteCollectorProxy $group) {
  $group->post('/image', [ImageController::class, 'save'])->add(GetImageOwner::class);
  $group->get('/image/{image_id:[0-9]+}', [ImageController::class, 'read'])->add(GetImage::class);
  $group->post('/image/{image_id:[0-9]+}', [ImageController::class, 'updateImage'])->add(GetImage::class)->add(GetImageOwner::class);
  $group->delete('/image/{image_id:[0-9]+}', [ImageController::class, 'deleteImage'])->add(GetImage::class);
});
