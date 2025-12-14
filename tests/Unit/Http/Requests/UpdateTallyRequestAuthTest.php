<?php

declare(strict_types=1);

use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Models\Tally;
use App\Models\User;

covers(UpdateTallyRequest::class);

it('denies unauthorized user', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create();

    $request = new UpdateTallyRequest();
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class
    {
        public function parameter($key)
        {
            return Tally::factory()->create();
        }
    });

    expect($request->authorize())->toBeFalse();
});
