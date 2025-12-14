<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\TallyShare\CreateTallyShareAction;
use App\Actions\TallyShare\DeleteTallyShareAction;
use App\Actions\TallyShare\UpdateTallySharesAction;
use App\Http\Requests\TallyShare\CreateTallyShareRequest;
use App\Http\Requests\TallyShare\DeleteTallyShareRequest;
use App\Http\Requests\TallyShare\UpdateTallySharesRequest;
use App\Http\Resources\UserResource;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TallyShareController
{
    public function index(Request $request, Tally $tally): AnonymousResourceCollection
    {
        return UserResource::collection($tally->users);
    }

    public function store(CreateTallyShareRequest $request, Tally $tally, CreateTallyShareAction $action): JsonResponse
    {
        $email = $request->string('email')->toString();

        $action->handle($tally, $email);

        return response()->json(['message' => 'Tally shared successfully'], Response::HTTP_CREATED);
    }

    public function destroy(DeleteTallyShareRequest $request, Tally $tally, User $share, DeleteTallyShareAction $action): JsonResponse
    {
        $action->handle($tally, $share);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function update(UpdateTallySharesRequest $request, Tally $tally, UpdateTallySharesAction $action): JsonResponse
    {
        /** @var array<int, array{user_id: int, percentage: float|int}> $shares */
        $shares = $request->validated('shares');

        $action->handle($tally, $shares);

        return response()->json(['message' => 'Shares updated successfully']);
    }
}
