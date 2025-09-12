<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'start-work' => [
                'required',
                'date_format:H:i',
                'before:finish-work'
            ],

            'finish-work' => [
                'required',
                'date_format:H:i',
                'after:start-work'
            ],

            'start-rest.*' => [
                'required',
                'date_format:H:i',
                'after:start-work',
                'before:finish-work'
            ],

            'finish-rest.*' => [
                'required',
                'date_format:H:i',
                'before:finish-work'
            ],

            'add-start-rest' => [
                'nullable',
                'date_format:H:i',
                'after:start-work',
                'before:finish-work',
                'required_with:add-finish-rest',
            ],

            'add-finish-rest' => [
                'nullable',
                'date_format:H:i',
                'before:finish-work',
                'required_with:add-start-rest',
            ],

            'remarks' => [
                'required',
                'max:255'
            ],
        ];
    }

    public function messages(){
        return[
            'start-work.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'finish-work.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'start-rest.*.before' => '休憩時間が不適切な値です',
            'start-rest.*.after' => '休憩時間が不適切な値です',
            'finish-rest.*.before' => '休憩時間が不適切な値です',
            'remarks.required' => '備考を入力してください',
            'remarks.max' => '備考は255文字以内で入力してください',
        ];
    }
}
