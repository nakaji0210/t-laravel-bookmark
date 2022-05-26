<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @package App\Http\Requests
 * @property-read string comment
 * @property-read int categoryId
 */
class UpdateBookmarkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment' => 'required|string|min:10|max:1000',
            'category' => 'required|integer|exists:bookmark_categories,id',
        ];
    }
}