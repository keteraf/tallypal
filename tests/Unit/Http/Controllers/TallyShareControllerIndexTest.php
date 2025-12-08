<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TallyShareController;
use App\Models\Tally;
use Illuminate\Http\Request;

covers(TallyShareController::class);

it('returns tally shares collection', function () {
    $tally = Tally::factory()->create();
    $request = Request::create('/');

    $controller = new TallyShareController();
    $response = $controller->index($request, $tally);

    expect($response)->toBeInstanceOf(Illuminate\Http\Resources\Json\AnonymousResourceCollection::class);
});
