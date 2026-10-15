<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Reference data every environment needs: the currencies and the categories. Runs on every deploy
 * (`deploy/CHECKLIST.md`), so it is idempotent: rows are matched by their stable key (currency
 * `code`, category `slug`) and updated in place, keeping their ids. It never deletes a row.
 */
class ReferenceDataSeeder extends Seeder
{
    /**
     * @var list<array{code: string, name: string, symbol: string, decimals: int, description: string}>
     */
    public const array CURRENCIES = [
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimals' => 2, 'description' => 'The official currency of the Eurozone, used by most European Union countries.'],
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2, 'description' => 'The official currency of the United States of America. Code: USD.'],
        ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£', 'decimals' => 2, 'description' => 'The official currency of the United Kingdom.'],
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimals' => 0, 'description' => 'The official currency of Japan.'],
        ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'Fr.', 'decimals' => 2, 'description' => 'The official currency of Switzerland and Liechtenstein.'],
        ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimals' => 2, 'description' => 'The official currency of Canada.'],
        ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$', 'decimals' => 2, 'description' => 'The official currency of Australia.'],
        ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥', 'decimals' => 2, 'description' => 'The official currency of the People\'s Republic of China.'],
        ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹', 'decimals' => 2, 'description' => 'The official currency of India.'],
        ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'decimals' => 2, 'description' => 'The official currency of Brazil.'],
    ];

    /**
     * Source: `Tallypal Categories.html`. `name` is the English label (the UI translates by slug).
     *
     * @var list<array{slug: string, type: CategoryType, name: string, color: string, description: string}>
     */
    public const array CATEGORIES = [
        ['slug' => 'bills', 'type' => CategoryType::EXPENSE, 'name' => 'Bills', 'color' => 'c4', 'description' => 'Payments for electricity, water, gas, and other utilities.'],
        ['slug' => 'clothing', 'type' => CategoryType::EXPENSE, 'name' => 'Clothing', 'color' => 'c6', 'description' => 'Purchases of clothes, shoes, and accessories.'],
        ['slug' => 'dining_out', 'type' => CategoryType::EXPENSE, 'name' => 'Dining Out', 'color' => 'c6', 'description' => 'Spending on meals, snacks, and drinks at restaurants, cafes, and fast food outlets.'],
        ['slug' => 'education', 'type' => CategoryType::EXPENSE, 'name' => 'Education', 'color' => 'c1', 'description' => 'Tuition fees, books, supplies, courses, and any learning-related spending.'],
        ['slug' => 'entertainment', 'type' => CategoryType::EXPENSE, 'name' => 'Entertainment', 'color' => 'c6', 'description' => 'Costs for movies, streaming services, concerts, events, and other recreational activities.'],
        ['slug' => 'gifts_donations', 'type' => CategoryType::EXPENSE, 'name' => 'Gifts & Donations', 'color' => 'c5', 'description' => 'Money spent on gifts, charitable donations, or giving to friends and family.'],
        ['slug' => 'groceries', 'type' => CategoryType::EXPENSE, 'name' => 'Groceries', 'color' => 'c4', 'description' => 'Expenses for food and household supplies purchased at supermarkets or grocery stores.'],
        ['slug' => 'healthcare', 'type' => CategoryType::EXPENSE, 'name' => 'Healthcare', 'color' => 'c3', 'description' => 'Medical expenses including doctor visits, medications, treatments, and dental care.'],
        ['slug' => 'household_supplies', 'type' => CategoryType::EXPENSE, 'name' => 'Household Supplies', 'color' => 'c4', 'description' => 'General home maintenance items, cleaning products, and minor home repairs.'],
        ['slug' => 'insurance', 'type' => CategoryType::EXPENSE, 'name' => 'Insurance', 'color' => 'c1', 'description' => 'Premiums paid for health, car, home, or life insurance coverage.'],
        ['slug' => 'personal_care', 'type' => CategoryType::EXPENSE, 'name' => 'Personal Care', 'color' => 'c6', 'description' => 'Expenses for personal hygiene, beauty products, haircuts, and wellness services.'],
        ['slug' => 'rent', 'type' => CategoryType::EXPENSE, 'name' => 'Rent', 'color' => 'c1', 'description' => 'Monthly payments made for housing or apartment living arrangements.'],
        ['slug' => 'subscriptions', 'type' => CategoryType::EXPENSE, 'name' => 'Subscriptions', 'color' => 'c2', 'description' => 'Regular payments for magazines, software, gym memberships, and online services.'],
        ['slug' => 'transportation', 'type' => CategoryType::EXPENSE, 'name' => 'Transportation', 'color' => 'c2', 'description' => 'Costs related to commuting, public transit fares, fuel, parking, and vehicle maintenance.'],
        ['slug' => 'travel', 'type' => CategoryType::EXPENSE, 'name' => 'Travel', 'color' => 'c2', 'description' => 'Spending on vacations, flights, hotels, and travel-related activities.'],
        ['slug' => 'utilities', 'type' => CategoryType::EXPENSE, 'name' => 'Utilities', 'color' => 'c5', 'description' => 'Payments for essential services like electricity, water, gas, and trash collection.'],
        ['slug' => 'salary', 'type' => CategoryType::INCOME, 'name' => 'Salary', 'color' => 'c3', 'description' => 'Regular pay from an employer.'],
        ['slug' => 'side_work', 'type' => CategoryType::INCOME, 'name' => 'Side work', 'color' => 'c2', 'description' => 'Freelance jobs, invoices and other work outside the main job.'],
        ['slug' => 'bonus', 'type' => CategoryType::INCOME, 'name' => 'Bonus', 'color' => 'c4', 'description' => 'Bonuses, commissions and extra monthly pay on top of salary.'],
        ['slug' => 'investments', 'type' => CategoryType::INCOME, 'name' => 'Investments', 'color' => 'c1', 'description' => 'Dividends, interest and capital gains.'],
        ['slug' => 'rental_income', 'type' => CategoryType::INCOME, 'name' => 'Rental income', 'color' => 'c1', 'description' => 'Rent received from tenants.'],
        ['slug' => 'refunds_returns', 'type' => CategoryType::INCOME, 'name' => 'Refunds & returns', 'color' => 'c2', 'description' => 'Money back from a shop or service: returns, cancellations, cashback. Never a member paying you back.'],
        ['slug' => 'gifts_received', 'type' => CategoryType::INCOME, 'name' => 'Gifts', 'color' => 'c6', 'description' => 'Presents received, with nothing owed behind them.'],
        ['slug' => 'sold_items', 'type' => CategoryType::INCOME, 'name' => 'Sold items', 'color' => 'c4', 'description' => 'Selling your own things.'],
        ['slug' => 'pension_benefits', 'type' => CategoryType::INCOME, 'name' => 'Pension & benefits', 'color' => 'c1', 'description' => 'Pension, unemployment and other allowances.'],
        ['slug' => 'winnings', 'type' => CategoryType::INCOME, 'name' => 'Winnings', 'color' => 'c5', 'description' => 'Lottery, betting and prize money.'],
        ['slug' => 'other_income', 'type' => CategoryType::INCOME, 'name' => 'Other income', 'color' => 'c3', 'description' => 'Any other money coming in.'],
    ];

    public function run(): void
    {
        foreach (self::CURRENCIES as $currency) {
            Currency::query()->updateOrCreate(['code' => $currency['code']], $currency);
        }

        foreach (self::CATEGORIES as $category) {
            Category::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
