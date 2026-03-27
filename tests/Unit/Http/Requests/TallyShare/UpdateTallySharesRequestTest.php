<?php

declare(strict_types=1);

use App\Http\Requests\TallyShare\UpdateTallySharesRequest;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

covers(UpdateTallySharesRequest::class);

it('authorizes the owner', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $request = new UpdateTallySharesRequest();
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

    $request = new UpdateTallySharesRequest();
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
    $request = new UpdateTallySharesRequest();
    expect($request->rules())->toBe([
        'shares' => ['required', 'array'],
        'shares.*.user_id' => ['required', 'integer', 'exists:users,id'],
        'shares.*.percentage' => ['required', 'numeric', 'min:0', 'max:100'],
    ]);
});

it('validates that total percentage is 100', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $request = new UpdateTallySharesRequest();
    $request->merge(['shares' => [
        ['user_id' => $user1->id, 'percentage' => 50],
        ['user_id' => $user2->id, 'percentage' => 50],
    ]]);

    $validator = Validator::make($request->all(), $request->rules());
    $request->withValidator($validator);

    expect($validator->passes())->toBeTrue();
});

it('fails if total percentage is not 100', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $request = new UpdateTallySharesRequest();
    $request->merge(['shares' => [
        ['user_id' => $user1->id, 'percentage' => 50],
        ['user_id' => $user2->id, 'percentage' => 40],
    ]]);

    $validator = Validator::make($request->all(), $request->rules());
    $request->withValidator($validator);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('shares'))->toBe('The total percentage must equal 100.');
});

it('denies guest', function () {
    $tally = Tally::factory()->create();
    $request = new UpdateTallySharesRequest();
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
