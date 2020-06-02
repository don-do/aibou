<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhoto extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() // 権限に関する判定。使わないのでtrue
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
            // 項目名photo。必須・ファイルの種類
            'photo' => 'required|file|mimes:jpg,jpeg,png,gif'
        ];
    }
}
