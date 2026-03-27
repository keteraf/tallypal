<?php

declare(strict_types=1);

use App\Http\Requests\Tally\UpdateTallyRequest;

covers(UpdateTallyRequest::class);

it('has validation rules', function () {
    $request = new UpdateTallyRequest();
    expect($request->rules())->toBe([
        'name' => ['required', 'string', 'max:25'],
        'description' => ['sometimes', 'nullable', 'string'],
    ]);
});
