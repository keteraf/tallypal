<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float|int $default_share_percentage
 */
class TallyUser extends Model
{
    public const string TABLE_NAME = 'tally_user';

    public const string KEYS_PREFIX = self::TABLE_NAME;

    public const string PRIMARY_KEY = self::KEYS_PREFIX . '_id';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;
}
