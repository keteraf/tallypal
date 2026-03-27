<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property float|int $default_share_percentage
 */
class TallyUser extends Pivot
{
    public const string TABLE_NAME = 'tally_user';

    /** @pest-mutate-ignore */
    public const string KEYS_PREFIX = self::TABLE_NAME; // @pest-mutate-ignore

    /** @pest-mutate-ignore */
    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id'; // @pest-mutate-ignore

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_share_percentage' => 'float',
        ];
    }
}
