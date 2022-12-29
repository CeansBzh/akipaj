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
        $this['remove_image'] = filter_var($this['remove_image'], FILTER_VALIDATE_BOOLEAN);
        if ($this['clothing_size'] === '--') {
            $this['clothing_size'] = null;
        }
        if ($this['mobile_phone'] != null) {
            $this['mobile_phone'] = preg_replace('/\s/', '', $this['mobile_phone']);
        }
        if ($this['home_phone'] != null) {
            $this['home_phone'] = preg_replace('/\s/', '', $this['home_phone']);
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
            'profile_picture' => ['nullable', 'mimes:png,jpg,jpeg,gif', 'max:10000', 'dimensions:max_width=100,max_height=100'],
            'remove_image' => ['required', 'boolean'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'mobile_phone' => ['nullable', 'string', 'max:10'],
            'home_phone' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'integer', 'min:1000', 'max:99999'],
            'city' => ['nullable', 'string', 'max:255'],
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
