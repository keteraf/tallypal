<?php

declare(strict_types=1);

namespace App\Http\Requests\Tally;

use App\Models\Currency;

/**
 * Swaps the public `default_currency_uuid` for the internal `default_currency_id` in the validated data.
 * The key stays absent when the request did not send it, so an update keeps the stored value.
 */
trait ResolvesDefaultCurrency
{
    /**
     * @param  array<int, string>|int|string|null  $key
     * @param  mixed  $default
     * @return ($key is null ? array<string, mixed> : mixed)
     */
    public function validated($key = null, $default = null)
    {
        /** @var array<string, mixed> $validated */
        $validated = parent::validated();

        if (array_key_exists('default_currency_uuid', $validated)) {
            $uuid = $validated['default_currency_uuid'];
            unset($validated['default_currency_uuid']);

            $validated['default_currency_id'] = $uuid === null
                ? null
                : Currency::query()->where('uuid', $uuid)->value(Currency::PRIMARY_KEY);
        }

        return data_get($validated, $key, $default);
    }
}
