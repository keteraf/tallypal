<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TallyShareController;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

covers(TallyShareController::class);

it('returns tally shares collection', function () {
    $user = User::factory()->create();
    $tally = Tally::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user);

    $request = Request::create('/');

    $controller = new TallyShareController();
    $response = $controller->index($request, $tally);

    expect($response)->toBeInstanceOf(AnonymousResourceCollection::class);
});
