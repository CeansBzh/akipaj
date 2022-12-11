<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this['clothing_size'] === '--') {
            $this['clothing_size'] = null;
        }
        if ($this['phone'] != null) {
            $this['phone'] = preg_replace('/\s/', '', $this['phone']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'clothing_size' => ['nullable', 'string', 'in:XS,S,M,L,XL,XXL'],
        ];
    }

    /**
     * Get the status for the request.
     * 
     * @return string
     */
    public function status()
    {
        if ($this->name || $this->email) {
            return 'profile-updated';
        } else {
            return 'personal-updated';
        }
    }
}
