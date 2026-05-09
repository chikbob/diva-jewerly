<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:pending,paid,failed,cancelled'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,cancelled'],
            'sort' => ['nullable', 'in:newest,oldest,total_asc,total_desc'],
        ];
    }

    public function filters(): array
    {
        return $this->validated();
    }
}
