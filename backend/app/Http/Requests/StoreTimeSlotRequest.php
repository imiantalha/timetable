<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\TimeSlot;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreTimeSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'institution_id' => ['required', 'integer', 'exists:institutions,id'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'is_break' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $query = TimeSlot::query()
                    ->where('institution_id', $this->integer('institution_id'))
                    ->where('day_of_week', $this->integer('day_of_week'))
                    ->where('starts_at', '<', $this->input('ends_at'))
                    ->where('ends_at', '>', $this->input('starts_at'));

                $existingSlot = collect($this->route()->parameters())
                    ->first(fn ($parameter) => $parameter instanceof TimeSlot);

                if ($existingSlot instanceof TimeSlot) {
                    $query->where('id', '!=', $existingSlot->id);
                }

                if ($query->exists()) {
                    $validator->errors()->add(
                        'starts_at',
                        'This time slot overlaps an existing slot for the same day and institution.',
                    );
                }
            },
        ];
    }
}
