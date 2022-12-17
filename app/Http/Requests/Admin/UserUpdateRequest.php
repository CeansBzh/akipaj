<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->route('user')->id)],
            'profile_picture' => ['nullable', 'mimes:png,jpg,jpeg,gif', 'max:10000', 'dimensions:max_width=100,max_height=100'],
            'remove_image' => ['required', 'boolean'],
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'clothing_size' => ['nullable', 'string', 'in:XS,S,M,L,XL,XXL'],
        ];
    }
}