<?php

declare(strict_types=1);
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDepartmentRequest extends FormRequest {
    public function authorize(): bool { return $this->user()?->role === 'admin'; }
    public function rules(): array { return ['institution_id'=>['required','integer','exists:institutions,id'],'name'=>['required','string','max:255'],'code'=>['required','string','max:50'],'is_active'=>['sometimes','boolean']]; }
}
