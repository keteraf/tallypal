<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Currency;
use App\Models\User;
use App\Services\HomeService;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia;

covers(HandleInertiaRequests::class);

test('ziggy prop is not shared on public routes', function () {
    $this->get('/')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('ziggy')
        );
});

test('ziggy prop is not shared on authenticated routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/home')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('ziggy')
        );
});

it('shares the app name, the SEO defaults and no user to a guest', function (): void {
    $this->get('/')->assertInertia(fn (AssertableInertia $page) => $page
        ->has('errors')
        ->where('name', config('app.name'))
        ->where('auth.user', null)
        ->where('sidebarOpen', true)
        ->where('seo', [
            'site_name' => config('app.name'),
            'default_description' => __('seo.default_description'),
            'default_og_image' => asset('og/og-card.svg'),
            'locale' => 'it_IT',
            'url' => config('app.url'),
        ])
        ->missing('currencies')
        ->missing('primary_currency')
        ->missing('sidebar_tallies')
    );
});

it('shares the identity, roles and default currency of the signed-in user', function (): void {
    $currency = Currency::factory()->create();
    $user = User::factory()->create(['default_currency_id' => $currency->getKey()]);
    $user->assignRole('Super Admin');

    $this->actingAs($user)->get('/home')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.id', $user->id)
        ->where('auth.user.name', $user->name)
        ->where('auth.user.email', $user->email)
        ->where('auth.user.email_verified_at', $user->email_verified_at?->toJSON())
        ->where('auth.user.roles', ['Super Admin'])
        ->where('auth.user.default_currency', ['uuid' => $currency->uuid, 'code' => $currency->code, 'symbol' => $currency->symbol])
        ->has('currencies')
        ->has('primary_currency')
    );
});

it('shares no default currency when the user has none', function (): void {
    $user = User::factory()->create(['default_currency_id' => null]);

    $this->actingAs($user)->get('/home')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('auth.user.default_currency', null)
        ->where('auth.user.roles', [])
    );
});

it('shares every currency with exactly its identity columns', function (): void {
    $currency = Currency::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)->get('/home')->assertInertia(fn (AssertableInertia $page) => $page
        ->where('currencies', fn ($currencies): bool => collect($currencies)->firstWhere('uuid', $currency->uuid) === [
            'currency_id' => $currency->getKey(),
            'uuid' => $currency->uuid,
            'name' => $currency->name,
            'code' => $currency->code,
            'symbol' => $currency->symbol,
        ])
    );
});

it('keys the once props by the sidebar version so they refresh when it changes', function (): void {
    $user = User::factory()->create();
    $version = app(HomeService::class)->sidebarVersion($user);

    $response = $this->actingAs($user)
        ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(Request::create('/'))])
        ->get('/home');

    $onceProps = $response->json('onceProps');
    expect($onceProps)->toHaveKeys(['sidebar_tallies:' . $version, 'primary_currency:' . $version])
        ->and($onceProps['sidebar_tallies:' . $version]['prop'])->toBe('sidebar_tallies')
        ->and($onceProps['primary_currency:' . $version]['prop'])->toBe('primary_currency');
});

it('keeps the sidebar open unless its cookie says otherwise', function (?string $cookie, bool $open): void {
    $user = User::factory()->create();
    $request = $cookie === null ? $this->actingAs($user) : $this->actingAs($user)->withUnencryptedCookie('sidebar_state', $cookie);

    $request->get('/home')->assertInertia(fn (AssertableInertia $page) => $page->where('sidebarOpen', $open));
})->with([[null, true], ['true', true], ['false', false]]);

it('uses the asset URL hash as the asset version when one is configured', function (): void {
    config(['app.asset_url' => 'https://cdn.example.test']);

    expect(app(HandleInertiaRequests::class)->version(Request::create('/')))->toBe(hash('xxh128', 'https://cdn.example.test'));
});
