<?php

declare(strict_types=1);

use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Models\Tally;
use App\Models\User;

covers(UpdateTallyRequest::class);

it('authorizes tally owner', function () {
    $owner = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $owner->id]);

    $request = new UpdateTallyRequest();
    $request->setUserResolver(fn () => $owner);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private readonly Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeTrue();
});

it('denies unauthorized user', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create();

    $request = new UpdateTallyRequest();
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private readonly Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeFalse();
});

it('denies guest user', function () {
    $tally = Tally::factory()->create();

    $request = new UpdateTallyRequest();
    $request->setUserResolver(fn () => null);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private readonly Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeFalse();
});
