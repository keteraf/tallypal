<?php

declare(strict_types=1);

namespace App\Http\Requests\Transactions;

use App\Models\Currency;
use App\Models\Tally;

/**
 * Builds the `amount` rule: numeric, with at most as many decimal places as the request's currency
 * (the submitted one, else the tally's default). Without a resolvable currency only the base rules apply.
 */
trait ValidatesAmountDecimals
{
    protected function amountRule(): string
    {
        $decimals = $this->amountCurrencyDecimals();

        return $decimals === null ? 'required|numeric' : "required|numeric|decimal:0,{$decimals}";
    }

    private function amountCurrencyDecimals(): ?int
    {
        $currencyId = $this->input('currency_id');

        if (is_scalar($currencyId)) {
            $submitted = Currency::query()->whereKey($currencyId)->first();

            if ($submitted instanceof Currency) {
                return $submitted->decimals;
            }
        }

        $tally = $this->route('tally');

        return $tally instanceof Tally ? $tally->defaultCurrency?->decimals : null;
    }
}
