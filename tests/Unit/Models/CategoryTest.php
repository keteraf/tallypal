<?php

declare(strict_types=1);

use App\Enums\CategoryType;
use App\Models\Category;

covers(Category::class);

it('returns uuid as the unique id column', function () {
    $category = new Category();
    expect($category->uniqueIds())->toBe(['uuid']);
});

it('returns uuid as the route key name', function () {
    $category = new Category();
    expect($category->getRouteKeyName())->toBe('uuid');
});

it('has correct table name', function () {
    $category = new Category();
    expect($category->getTable())->toBe('categories');
    expect(Category::TABLE_NAME)->toBe('categories');
});

it('has correct primary key', function () {
    $category = new Category();
    expect($category->getKeyName())->toBe('category_id');
    expect(Category::PRIMARY_KEY)->toBe('category_id');
});

it('has fillable attributes', function () {
    $category = new Category();
    expect($category->getFillable())->toBe(['type', 'slug', 'name', 'description', 'icon', 'color']);
});

it('scopes categories to one transaction type', function () {
    $expense = Category::factory()->create();
    $income = Category::factory()->income()->create();

    expect(Category::query()->ofType(CategoryType::INCOME)->pluck('category_id')->all())->toBe([$income->category_id])
        ->and(Category::query()->ofType(CategoryType::EXPENSE)->pluck('category_id')->all())->toContain($expense->category_id)
        ->not->toContain($income->category_id);
});
