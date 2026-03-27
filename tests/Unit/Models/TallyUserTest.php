<?php

declare(strict_types=1);

use App\Models\TallyUser;

covers(TallyUser::class);

it('has expected table and key constants', function (): void {
    $pivot = new TallyUser();

    expect(TallyUser::TABLE_NAME)->toBe('tally_user')
        ->and(TallyUser::KEYS_PREFIX)->toBe('tally_user')
        ->and(TallyUser::PRIMARY_KEY)->toBe('tally_user_id')
        ->and($pivot->getTable())->toBe('tally_user')
        ->and($pivot->getKeyName())->toBe('tally_user_id');
});

it('casts default_share_percentage to float', function (): void {
    $pivot = new TallyUser();
    $pivot->setRawAttributes(['default_share_percentage' => '33.50'], true);

    expect($pivot->default_share_percentage)->toBe(33.5);
    expect($pivot->getCasts())->toMatchArray(['default_share_percentage' => 'float']);
});
