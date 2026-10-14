<?php

declare(strict_types=1);

use App\Http\Requests\Tally\UpdateTallyRequest;

covers(UpdateTallyRequest::class);

it('has validation rules', function () {
    $request = new UpdateTallyRequest();
    $rules = $request->rules();
    expect($rules)->toHaveKey('name')
        ->and($rules)->toHaveKey('description')
        ->and($rules)->toHaveKey('color')
        ->and($rules)->toHaveKey('glyph');
});
