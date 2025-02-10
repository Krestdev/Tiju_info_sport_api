<?php

declare(strict_types=1);

namespace App\Middleware\Payments;

use App\Db\Repository\PaymentService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class GetPayment
{
  public function __construct(private PaymentService $paymentService) {}

  public function __invoke(Request $request, RequestHandler $handler)
  {
    $context = RouteContext::fromRequest($request);
    $route = $context->getRoute();
    $id = $route->getArgument('payment_id');

    $Payment = $this->paymentService->getPaymentById((int)$id);

    if ($Payment === null) {
      throw new HttpNotFoundException($request, "Payment not found");
    }

    $request = $request->withAttribute('payment', $Payment);

    return $handler->handle($request);
  }
}
