<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Currency;
use App\Services\HomeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(private readonly HomeService $homeService) {}

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user() ? [
                    ...$request->user()->only(['id', 'name', 'email', 'email_verified_at']),
                    'roles' => $request->user()->getRoleNames(),
                    'default_currency' => $request->user()->defaultCurrency?->only(['uuid', 'code', 'symbol']),
                ] : null,
            ],
            ...($user === null ? [] : [
                'currencies' => Inertia::once(fn () => Currency::query()->get(['currency_id', 'uuid', 'name', 'code', 'symbol'])),
                'sidebar_tallies' => Inertia::once(fn (): array => $this->homeService->sidebarTallies($user, CarbonImmutable::now()))
                    ->as('sidebar_tallies:' . $this->homeService->sidebarVersion($user)),
                'primary_currency' => Inertia::once(fn (): array => $this->homeService->primaryCurrency($user))
                    ->as('primary_currency:' . $this->homeService->sidebarVersion($user)),
            ]),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'seo' => [
                'site_name' => config('app.name'),
                'default_description' => __('seo.default_description'),
                'default_og_image' => asset('og/og-card.svg'),
                'locale' => 'it_IT',
                'url' => config('app.url'),
            ],
        ];
    }
}
