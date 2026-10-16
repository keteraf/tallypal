<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PreferencesUpdateRequest;
use App\Models\Currency;
use App\Models\User;
use App\Services\HomeService;
use Illuminate\Http\RedirectResponse;

final class PreferencesController extends Controller
{
    /**
     * Update the authenticated user's preferences. The default currency is the Home/Stats primary
     * currency (O-04), so the cached Home aggregates are dropped.
     */
    public function update(PreferencesUpdateRequest $request, HomeService $homeService): RedirectResponse
    {
        /** @var array{default_currency_uuid: string|null} $data */
        $data = $request->validated();

        /** @var User $user */
        $user = $request->user();

        $user->update([
            'default_currency_id' => $data['default_currency_uuid'] === null
                ? null
                : Currency::query()->where('uuid', $data['default_currency_uuid'])->value(Currency::PRIMARY_KEY),
        ]);

        $userId = (int) $user->id; // @pest-mutate-ignore
        $homeService->forgetCacheForUser($userId);

        return back();
    }
}
