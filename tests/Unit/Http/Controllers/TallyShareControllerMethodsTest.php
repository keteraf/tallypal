<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TallyShareController;

covers(TallyShareController::class);

it('has store method', function () {
    $controller = new TallyShareController();
    $controller->store();
    expect(true)->toBeTrue();
});

it('has update method', function () {
    $controller = new TallyShareController();
    $controller->update();
    expect(true)->toBeTrue();
});

it('has destroy method', function () {
    $controller = new TallyShareController();
    $controller->destroy();
    expect(true)->toBeTrue();
});
