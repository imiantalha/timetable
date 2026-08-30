<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeSlotRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array { return ['institution_id'=>['required','integer','exists:institutions,id'],'day_of_week'=>['required','integer','between:0,6'],'starts_at'=>['required','date_format:H:i'],'ends_at'=>['required','date_format:H:i','after:starts_at'],'is_break'=>['sometimes','boolean']]; }
}
