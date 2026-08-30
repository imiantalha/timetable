<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array { return ['institution_id'=>['required','integer','exists:institutions,id'],'employee_number'=>['required','string','max:100'],'first_name'=>['required','string','max:100'],'last_name'=>['required','string','max:100'],'email'=>['nullable','email','max:255'],'is_active'=>['sometimes','boolean']]; }
}
