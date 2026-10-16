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

test('authenticated non-admin users are redirected to home dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->get('/dashboard')->assertRedirect(route('home.dashboard'));
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

test('the time series covers the last 30 days with each day count', function () {
    $this->travelTo('2026-06-30 12:00:00');
    $admin = User::factory()->create(['created_at' => now()->subDays(40)]);
    $admin->assignRole('Super Admin');
    User::factory()->create(['created_at' => '2026-06-30 08:00:00']);
    User::factory()->count(2)->create(['created_at' => '2026-06-01 08:00:00']);
    User::factory()->create(['created_at' => '2026-05-31 08:00:00']);

    $this->actingAs($admin)->get('/dashboard')
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('usersOverTime.0', ['date' => '2026-06-01', 'count' => 2])
            ->where('usersOverTime.29', ['date' => '2026-06-30', 'count' => 1])
            ->where('usersOverTime.1', ['date' => '2026-06-02', 'count' => 0])
            ->where('stats.new_users_30d', 3)
        );
});
