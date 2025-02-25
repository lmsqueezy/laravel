<?php

namespace LemonSqueezy\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LemonSqueezy\Laravel\Events\WebhookHandled;
use LemonSqueezy\Laravel\Events\WebhookReceived;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\Http\Middleware\VerifyWebhookSignature;
use LemonSqueezy\Laravel\Webhooks\Enums\Topic;
use LemonSqueezy\Laravel\Webhooks\Handlers;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal Not supported by any backwards compatibility promise. Please use events to react to webhooks.
 */
final class WebhookController extends Controller
{
    public function __construct()
    {
        if (config('lemon-squeezy.signing_secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * Handle a Lemon Squeezy webhook call.
     *
     * @param Request $request
     * @return Response
     * @throws InvalidCustomPayload
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->all();

        if (!isset($payload['meta']['event_name']) || $request->hasHeader('X-Event-Name')) {
            return new JsonResponse(
                data: [
                    'message' => 'Webhook received but no event name was found.',
                ],
                status: Response::HTTP_BAD_REQUEST,
            );
        }

        WebhookReceived::dispatch($payload);


        $handler = match ($request->header('X-Event-Name')) {
            Topic::OrderCreated->value => new Handlers\HandleOrderCreated(),
            Topic::OrderRefunded->value => new Handlers\HandleOrderRefunded(),
            Topic::SubscriptionCreated->value => new Handlers\HandleSubscriptionCreated(),
            Topic::SubscriptionUpdated->value => new Handlers\HandleSubscriptionUpdated(),
            Topic::SubscriptionCancelled->value => new Handlers\HandleSubscriptionCancelled(),
            Topic::SubscriptionExpired->value => new Handlers\HandleSubscriptionExpired(),
            Topic::SubscriptionPaused->value => new Handlers\HandleSubscriptionPaused(),
            Topic::SubscriptionResumed->value => new Handlers\HandleSubscriptionResumed(),
            Topic::SubscriptionUnpaused->value => new Handlers\HandleSubscriptionUnpaused(),
            Topic::SubscriptionPaymentSuccess->value => new Handlers\HandleSubscriptionPaymentSuccess(),
            Topic::SubscriptionPaymentFailed->value => new Handlers\HandleSubscriptionPaymentFailed(),
            Topic::SubscriptionPaymentRecovered->value => new Handlers\HandleSubscriptionPaymentRecovered(),
            Topic::LicenseKeyCreated->value => new Handlers\HandleLicenseKeyCreated(),
            Topic::LicenseKeyUpdated->value => new Handlers\HandleLicenseKeyUpdated(),
        };

        $handler->handle($payload);
        WebhookHandled::dispatch($payload);

        return new JsonResponse(
            data: [
                'message' => 'Webhook was handled.',
            ],
            status: Response::HTTP_OK,
        );
    }
}
