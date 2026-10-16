<?php

declare(strict_types=1);

use App\Http\Requests\Tally\StoreTallyRequest;
use App\Models\Currency;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Validation\Rule;

covers(StoreTallyRequest::class);

it('has exactly the documented validation rules', function () {
    expect((new StoreTallyRequest())->rules())->toEqual([
        'name' => ['required', 'string', 'max:50'],
        'description' => ['nullable', 'string', 'max:1000'],
        'color' => ['nullable', 'string', Rule::in(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'])],
        'glyph' => ['nullable', 'string', Rule::in(Tally::GLYPHS)],
        'default_currency_uuid' => ['nullable', 'uuid', 'exists:' . Currency::TABLE_NAME . ',uuid'],
    ]);
});

it('authorizes only signed-in users', function () {
    expect((new StoreTallyRequest())->authorize())->toBeFalse();

    $this->actingAs(User::factory()->create());

    expect((new StoreTallyRequest())->authorize())->toBeTrue();
});
