<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\TallyShareResource;
use App\Models\Tally;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TallyShareController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var Tally $tally */
        $tally = $request->tally;

        return TallyShareResource::collection($tally->with('users')->get());
    }

    public function store() {}

    public function update() {}

    public function destroy() {}
}
