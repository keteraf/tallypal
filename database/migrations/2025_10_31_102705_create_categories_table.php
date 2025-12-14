<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Category::TABLE_NAME, function (Blueprint $table): void {
            $table->id(Category::PRIMARY_KEY);
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('categories')->insert([
            ['name' => 'Bills', 'description' => 'Payments for electricity, water, gas, and other utilities.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Clothing', 'description' => 'Purchases of clothes, shoes, and accessories.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Dining Out', 'description' => 'Spending on meals, snacks, and drinks at restaurants, cafes, and fast food outlets.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Education', 'description' => 'Tuition fees, books, supplies, courses, and any learning-related spending.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Entertainment', 'description' => 'Costs for movies, streaming services, concerts, events, and other recreational activities.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gifts & Donations', 'description' => 'Money spent on gifts, charitable donations, or giving to friends and family.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Groceries', 'description' => 'Expenses for food and household supplies purchased at supermarkets or grocery stores.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Healthcare', 'description' => 'Medical expenses including doctor visits, medications, treatments, and dental care.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Household Supplies', 'description' => 'General home maintenance items, cleaning products, and minor home repairs.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Insurance', 'description' => 'Premiums paid for health, car, home, or life insurance coverage.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Personal Care', 'description' => 'Expenses for personal hygiene, beauty products, haircuts, and wellness services.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rent', 'description' => 'Monthly payments made for housing or apartment living arrangements.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Subscriptions', 'description' => 'Regular payments for magazines, software, gym memberships, and online services.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Transportation', 'description' => 'Costs related to commuting, public transit fares, fuel, parking, and vehicle maintenance.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Travel', 'description' => 'Spending on vacations, flights, hotels, and travel-related activities.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Utilities', 'description' => 'Payments for essential services like electricity, water, gas, and trash collection.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
};
