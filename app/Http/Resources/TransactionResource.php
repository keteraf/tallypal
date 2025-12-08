<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $transaction_id
 * @property-read int $tally_id
 * @property-read int $party_id
 * @property-read int $currency_id
 * @property-read int $category_id
 * @property-read string $type
 * @property-read string $title
 * @property-read string $description
 * @property-read float $amount
 * @property-read string $transaction_date
 * @property-read string $created_at
 * @property-read string $updated_at
 */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->transaction_id,
            'tally_id' => $this->tally_id,
            'party_id' => $this->party_id,
            'currency_id' => $this->currency_id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'transaction_date' => $this->transaction_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
