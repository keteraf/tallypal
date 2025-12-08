<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TallyShareController;

covers(TallyShareController::class);

it('has store method', function () {
    $controller = new TallyShareController();
    expect(method_exists($controller, 'store'))->toBeTrue();
});

it('has update method', function () {
    $controller = new TallyShareController();
    expect(method_exists($controller, 'update'))->toBeTrue();
});

it('has destroy method', function () {
    $controller = new TallyShareController();
    expect(method_exists($controller, 'destroy'))->toBeTrue();
});
