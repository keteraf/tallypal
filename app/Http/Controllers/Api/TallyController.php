<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Tally\DestroyTallyAction;
use App\Actions\Tally\StoreTallyAction;
use App\Actions\Tally\UpdateTallyAction;
use App\DTOs\TallyDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tally\DestroyTallyRequest;
use App\Http\Requests\Tally\StoreTallyRequest;
use App\Http\Requests\Tally\UpdateTallyRequest;
use App\Http\Resources\TallyResource;
use App\Models\Tally;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TallyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $userId = auth()->id();

        $tallies = Tally::query()
            ->with(['owner', 'users'])
            ->where(function ($query) use ($userId) {
                $query->where('owner_id', $userId)
                    ->orWhereHas('users', fn ($query) => $query->where('user_id', $userId));
            })
            ->get();

        return TallyResource::collection($tallies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws Throwable
     */
    public function store(StoreTallyRequest $request, StoreTallyAction $createTallyAction): TallyResource
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var array{owner_id: int, name: string, description?: string|null} $data */
        $data = ['owner_id' => $user->getKey(), ...$request->validated()];
        $tallyDTO = TallyDTO::fromArray($data);

        $tally = $createTallyAction->handle($tallyDTO);

        return new TallyResource($tally->load(['owner', 'users']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Tally $tally): TallyResource
    {
        $this->authorize('view', $tally);

        return new TallyResource($tally->load(['owner', 'users']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTallyRequest $request, Tally $tally, UpdateTallyAction $updateTallyAction): TallyResource
    {
        /** @var array{owner_id: int, name: string, description?: string|null} $data */
        $data = ['owner_id' => $tally->owner_id, ...$request->validated()];
        $tallyDTO = TallyDTO::fromArray($data);
        $tally = $updateTallyAction->handle($tally, $tallyDTO);

        return new TallyResource($tally->load(['owner', 'users']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DestroyTallyRequest $request, Tally $tally, DestroyTallyAction $deleteTallyAction): JsonResponse
    {
        $deleteTallyAction->handle($tally);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
