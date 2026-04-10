<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Models\Tally;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

covers(DashboardController::class);

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated non-admin users are redirected to tallies', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/dashboard')->assertRedirect(route('tallies.index'));
});

test('authenticated Super Admin users can visit the dashboard', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);

    $this->get('/dashboard')->assertStatus(200);
});

test('dashboard returns correct Inertia component and props', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('stats.total_users')
            ->has('stats.new_users_30d')
            ->has('stats.total_tallies')
            ->has('stats.shared_tallies')
            ->has('stats.total_transactions')
            ->has('stats.pending_invitations')
            ->has('usersOverTime')
            ->has('talliesOverTime')
            ->has('transactionsOverTime')
        );
});

test('dashboard stats count correctly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    User::factory()->count(3)->create();
    $tally = Tally::factory()->create(['owner_id' => $admin->id]);
    $otherUser = User::factory()->create();
    $tally->users()->attach($otherUser->id, ['default_share_percentage' => 50]);

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('stats.total_users', fn (int $count) => $count >= 5)
            ->where('stats.shared_tallies', fn (int $count) => $count >= 1)
        );
});

test('dashboard time series returns 30 data points', function () {
    $user = User::factory()->create();
    $user->assignRole('Super Admin');
    $this->actingAs($user);

    $this->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->has('usersOverTime', 30)
            ->has('talliesOverTime', 30)
            ->has('transactionsOverTime', 30)
        );
});
