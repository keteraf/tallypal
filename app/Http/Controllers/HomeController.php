<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\HomeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use function abort_unless;

final class HomeController extends Controller
{
    public function __construct(private readonly HomeService $homeService) {}

    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        abort_unless((bool) $user, 401);

        return Inertia::render('Home', $this->homeService->buildHomePayload($user, CarbonImmutable::now()));
    }
}
