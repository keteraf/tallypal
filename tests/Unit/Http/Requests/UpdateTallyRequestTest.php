<?php

declare(strict_types=1);

use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Models\Currency;
use App\Models\Tally;
use Illuminate\Validation\Rule;

covers(UpdateTallyRequest::class);

it('has exactly the documented validation rules, accepting the stored glyph', function () {
    $tally = Tally::factory()->make(['glyph' => 'legacy-glyph']);
    $request = new UpdateTallyRequest();
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private readonly Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->rules())->toEqual([
        'name' => ['required', 'string', 'max:50'],
        'description' => ['sometimes', 'nullable', 'string'],
        'color' => ['nullable', 'string', Rule::in(['c1', 'c2', 'c3', 'c4', 'c5', 'c6'])],
        'glyph' => ['nullable', 'string', Rule::in([...Tally::GLYPHS, 'legacy-glyph'])],
        'default_currency_uuid' => ['nullable', 'uuid', 'exists:' . Currency::TABLE_NAME . ',uuid'],
    ]);
});

it('builds its rules when the route has no tally', function () {
    $request = new UpdateTallyRequest();
    $request->setRouteResolver(fn () => new class
    {
        public function parameter(string $key): null
        {
            return null;
        }
    });

    expect($request->rules()['glyph'])->toEqual(['nullable', 'string', Rule::in([...Tally::GLYPHS, null])]);
});
