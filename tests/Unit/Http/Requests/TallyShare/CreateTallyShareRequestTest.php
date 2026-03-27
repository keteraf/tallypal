<?php

declare(strict_types=1);

use App\Http\Requests\TallyShare\CreateTallyShareRequest;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

covers(CreateTallyShareRequest::class);

it('authorizes the owner', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $request = new CreateTallyShareRequest();
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeTrue();
});

it('denies non-owner', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(); // Different owner

    $request = new CreateTallyShareRequest();
    $request->setUserResolver(fn () => $user);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeFalse();
});

it('has validation rules', function () {
    $request = new CreateTallyShareRequest();
    expect($request->rules())->toBe([
        'email' => ['required', 'email', 'exists:users,email'],
    ]);
});

it('denies guest', function () {
    $tally = Tally::factory()->create();
    $request = new CreateTallyShareRequest();
    $request->setUserResolver(fn () => null);
    $request->setRouteResolver(fn () => new class($tally)
    {
        public function __construct(private Tally $tally) {}

        public function parameter(string $key): Tally
        {
            return $this->tally;
        }
    });

    expect($request->authorize())->toBeFalse();
});
