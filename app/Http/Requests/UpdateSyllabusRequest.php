<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSyllabusRequest extends FormRequest
{
    use SyllabusValidationRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $syllabus = $this->route('syllabus');
        $user = $this->user();

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isFaculty() 
            && $syllabus->submitted_by === $user->id 
            && $syllabus->canBeEdited();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return $this->syllabusRules();
    }

    public function messages(): array
    {
        return $this->syllabusMessages();
    }

    /**
     * Custom validation logic to be run after primary rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateCreditHours($validator);
        });
    }

    private function validateCreditHours($validator)
    {
        if ($this->input('status') === 'draft') return;

        $syllabusLevel = $this->input('level');

        // Audit Course Safety Check
        $isLevelZero = (int)$syllabusLevel === 0;

        if ($isLevelZero) {
            $credits = $this->input('teaching_scheme.credits');
            if ($credits > 0) {
                $validator->errors()->add('teaching_scheme.credits', 'Audit courses (Level 0) must have 0 credits.');
            }
        }
    }
}
