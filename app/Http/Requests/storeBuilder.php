<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeBuilder extends FormRequest
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
            'uploadSVG'       => 'image|mimes:svg|max:20480',
        ];
    }
    public function messages()
    {
        return [
            'uploadSVG.required'       => 'An Image is required',
            'uploadSVG.image' => 'File Must be an image',
            'uploadSVG.mimes'         => 'The Image must be an extension SVG',
        ];
    }
}
