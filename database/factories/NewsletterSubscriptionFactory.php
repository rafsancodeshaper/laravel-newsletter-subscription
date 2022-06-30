<?php

namespace Riverskies\LaravelNewsletterSubscription\NewsletterSubscription;

use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => $this->faker->email,
        ];
    }
}
