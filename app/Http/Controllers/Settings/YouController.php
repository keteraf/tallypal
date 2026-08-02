<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class YouController extends Controller
{
    /**
     * Show the You hub: identity, preferences and data rows linking to the settings pages.
     */
    public function __invoke(): Response
    {
        return Inertia::render('settings/You');
    }
}
