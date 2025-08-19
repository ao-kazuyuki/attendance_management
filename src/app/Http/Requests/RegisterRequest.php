<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required',
            'password' => 'required|min:8|same:password_confirmation',
            'password_confirmation' => 'required|min:8|same:password',
        ];
    }

    public function messages(){
        return[
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.same' => 'パスワード確認と一致しません',
            'password_confirmation.required' => 'パスワード確認を入力してください',
            'password_confirmation.min' => 'パスワード確認は8文字以上で入力してください',
            'password_confirmation.same' => 'パスワードと一致しません',
        ];
    }

}
