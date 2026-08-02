<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\User;

test('guests are redirected to login from the you hub', function () {
    $this->get(route('you'))->assertRedirect(route('login'));
});

test('the you hub renders for an authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('settings/You'));
});

test('the you hub lists the supported currencies for the default currency row', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('you'))
        ->assertInertia(fn ($page) => $page
            ->has('currencies', Currency::query()->count())
            ->has('currencies.0', fn ($currency) => $currency->hasAll(['uuid', 'code', 'symbol'])->etc()));
});
