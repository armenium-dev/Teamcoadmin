<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGarment extends FormRequest
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
        $rules = [
            // Общие правила для create и update
        ];

        if ($this->isMethod('post')) {
            // Правила только для создания
            $rules['mainImage'] = 'required|file|mimes:png,jpg,jpeg,gif,webp|max:10240';
            $rules['sizeImage'] = 'required|file|mimes:png,jpg,jpeg,gif,webp|max:10240';
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            // Правила только для обновления
            $rules['mainImage'] = 'sometimes|file|mimes:png,jpg,jpeg,gif,webp|max:10240';
            $rules['sizeImage'] = 'sometimes|file|mimes:png,jpg,jpeg,gif,webp|max:10240';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'mainImage.required' => 'Main Image is required',
            'mainImage.image' => 'The Main Image must be a valid image file',
            'mainImage.mimes' => 'The Main Image must be a file of type: png, jpg, jpeg, gif or webp',
            'mainImage.max' => 'The Main Image may not be greater than 10MB',
            'sizeImage.required' => 'Size Image is required',
            'sizeImage.image' => 'The Size Image must be a valid image file',
            'sizeImage.mimes' => 'The Size Image must be a file of type: png, jpg, jpeg, gif or webp',
            'sizeImage.max' => 'The Size Image may not be greater than 10MB',
        ];
    }
}
