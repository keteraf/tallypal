<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SetupSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-super-admin {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Super Admin role and assign it to a new user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = Role::findOrCreate('Super Admin');

        $user = User::create([
            'name' => 'Super Admin',
            'email' => $this->argument('email'),
            'password' => Hash::make($this->argument('password')),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role);

        $this->info("Super Admin created successfully for {$user->email}");

        return 0;
    }
}
