<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CatalogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'only_new' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:name_asc,name_desc,price_asc,price_desc,newest'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->validated();

        if (array_key_exists('category_id', $filters) && $filters['category_id'] !== null) {
            $filters['category_id'] = (int) $filters['category_id'];
        }

        if (array_key_exists('min_price', $filters) && $filters['min_price'] !== null) {
            $filters['min_price'] = (float) $filters['min_price'];
        }

        if (array_key_exists('max_price', $filters) && $filters['max_price'] !== null) {
            $filters['max_price'] = (float) $filters['max_price'];
        }

        $filters['only_new'] = $this->boolean('only_new');

        return $filters;
    }
}
