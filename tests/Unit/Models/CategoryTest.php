<?php

declare(strict_types=1);

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
    expect($category->getFillable())->toBe(['name', 'icon', 'color']);
});
