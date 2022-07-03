<?php

namespace Riverskies\LaravelNewsletterSubscription\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Riverskies\LaravelNewsletterSubscription\Jobs\SendNewsletterSubscriptionConfirmation;
use Riverskies\LaravelNewsletterSubscription\NewsletterSubscription;

class NewsletterSubscriptionController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $existingSubscription = NewsletterSubscription::withTrashed()->whereEmail($validated['email'])->first();

        if ($existingSubscription) {
            if ($existingSubscription->trashed()) {
                $existingSubscription->restore();
                SendNewsletterSubscriptionConfirmation::dispatch($existingSubscription);
            }
        } else {
            $subscription = NewsletterSubscription::create(['email' => $validated['email']]);
            SendNewsletterSubscriptionConfirmation::dispatch($subscription);
        }

        if (!$request->expectsJson()) {
            return redirect(url('/'))
                ->with([
                    config('newsletter_subscription.session_message_key') => trans('riverskies::newsletter_subscription.subscribe', ['email' => $validated['email']]),
                    'data' => ['email' => $validated['email']],
                ]);
        }

        return $this->responseWithSuccess(
            trans('riverskies::newsletter_subscription.subscribe', ['email' => $validated['email']]),
            ['email' => $validated['email']]
        );
    }

    /**
     * @param $hash
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $hash)
    {
        $subscription = app('subscription-code-generator')->decode($hash);
        if ($subscription) {
            $subscription->delete();

            if (!$request->expectsJson()) {
                return redirect(url('/'))
                    ->with([
                        config('newsletter_subscription.session_message_key') => trans('riverskies::newsletter_subscription.unsubscribe', ['email' => $subscription->email]),
                        'data' => ['email' => $subscription->email],
                    ]);
            }

            return $this->responseWithSuccess(
                trans('riverskies::newsletter_subscription.unsubscribe', ['email' => $subscription->email]),
                ['email' => $subscription->email]
            );
        }

        if (!$request->expectsJson()) {
            return redirect(url('/'))
                ->with([
                    config('newsletter_subscription.session_message_key') => trans('riverskies::newsletter_subscription.already_unsubscribed'),
                ]);
        }

        return $this->responseWithError(
            trans('riverskies::newsletter_subscription.already_unsubscribed'),
        );
    }

    protected function responseWithSuccess($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'success' => true,
            config('newsletter_subscription.session_message_key') => $message,
            'data' => $data,
        ], $code);
    }

    protected function responseWithError($message = '', $data = [], $code = 400)
    {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
