<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {        
        if (auth()->user() && (int)request()->user_id == (int)auth()->user()->id) 
            return true;
        
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'      => 'required',
            'price'     => 'required|numeric',
            'weight'    => 'required|numeric',
            'user_id'   => 'required'
        ];        
    }
}
