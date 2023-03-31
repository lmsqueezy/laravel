<?php

namespace LaravelLemonSqueezy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @see https://docs.lemonsqueezy.com/api/webhooks#signing-requests
 */
class VerifyWebhookSignature
{
    /**
     * Handle the incoming request.
     *
     * @throws AccessDeniedHttpException
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->isInvalidSignature($request->getContent(), $request->header('x-signature'))) {
            throw new AccessDeniedHttpException('Invalid webhook signature.');
        }

        return $next($request);
    }

    /**
     * Validate the API signature.
     */
    protected function isInvalidSignature(string $payload, string $signature): bool
    {
        $hash = hash_hmac('sha256', $payload, config('lemon-squeezy.signing_secret'));

        return $hash !== $signature;
    }
}
