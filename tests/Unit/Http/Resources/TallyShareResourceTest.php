<?php

declare(strict_types=1);

use App\Http\Resources\TallyShareResource;
use App\Models\Tally;
use Illuminate\Http\Request;

covers(TallyShareResource::class);

it('transforms tally share to array', function () {
    $tally = Tally::factory()->create();
    $resource = new TallyShareResource($tally);
    $request = Request::create('/');

    $array = $resource->toArray($request);

    expect($array)->toHaveKey('tally_id')
        ->and($array)->toHaveKey('name')
        ->and($array)->toHaveKey('description')
        ->and($array)->toHaveKey('owner')
        ->and($array)->toHaveKey('users')
        ->and($array)->toHaveKey('created_at')
        ->and($array)->toHaveKey('updated_at')
        ->and($array['tally_id'])->toBe($tally->tally_id);
});
