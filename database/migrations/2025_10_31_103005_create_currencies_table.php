<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id('currency_id');
            $table->string('name');
            $table->string('code');
            $table->string('symbol');
            $table->integer('decimals');
            $table->string('description');
            $table->timestamps();
        });

        $now = now();

        DB::table('currencies')->insert([
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'decimals' => 2,
                'description' => 'The official currency of the Eurozone, used by most European Union countries.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'decimals' => 2,
                'description' => 'The official currency of the United States of America. Code: USD.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'British Pound',
                'code' => 'GBP',
                'symbol' => '£',
                'decimals' => 2,
                'description' => 'The official currency of the United Kingdom.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Japanese Yen',
                'code' => 'JPY',
                'symbol' => '¥',
                'decimals' => 0,
                'description' => 'The official currency of Japan.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Swiss Franc',
                'code' => 'CHF',
                'symbol' => 'Fr.',
                'decimals' => 2,
                'description' => 'The official currency of Switzerland and Liechtenstein.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Canadian Dollar',
                'code' => 'CAD',
                'symbol' => 'C$',
                'decimals' => 2,
                'description' => 'The official currency of Canada.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Australian Dollar',
                'code' => 'AUD',
                'symbol' => 'A$',
                'decimals' => 2,
                'description' => 'The official currency of Australia.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Chinese Yuan',
                'code' => 'CNY',
                'symbol' => '¥',
                'decimals' => 2,
                'description' => 'The official currency of the People\'s Republic of China.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Indian Rupee',
                'code' => 'INR',
                'symbol' => '₹',
                'decimals' => 2,
                'description' => 'The official currency of India.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Brazilian Real',
                'code' => 'BRL',
                'symbol' => 'R$',
                'decimals' => 2,
                'description' => 'The official currency of Brazil.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
};
