<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * An existing category must have the same type as the transaction it is attached to (C-D9).
 * An unknown id is left to the `exists` rule.
 */
final class CategoryMatchesType implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    private array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $category = Category::query()->whereKey($value)->first();

        if ($category === null) {
            return;
        }

        if ($category->type->value !== ($this->data['type'] ?? null)) {
            $fail('validation.category_type_mismatch')->translate();
        }
    }
}
