<?php

declare(strict_types=1);
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreRoomRequest extends FormRequest {
 public function authorize(): bool { return $this->user()?->role === 'admin'; }
 public function rules(): array { return ['institution_id'=>['required','integer','exists:institutions,id'],'name'=>['required','string','max:255'],'code'=>['required','string','max:50'],'capacity'=>['required','integer','min:1','max:5000'],'type'=>['required','string','max:50'],'is_active'=>['sometimes','boolean']]; }
}
