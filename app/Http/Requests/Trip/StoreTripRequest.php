<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:50',
            'description' => 'required|string|max:500',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'albums' => 'nullable|array',
            'albums.*' => 'nullable|integer|distinct|exists:albums,id',
            'users' => 'nullable|array',
            'users.*' => 'nullable|integer|distinct|exists:users,id',
            'image' => 'nullable|mimes:png,jpg,jpeg,gif|max:10000|dimensions:max_width=2560,max_height=1600',
            'boats' => 'nullable|array',
            'boats.*.id' => 'exclude',
            'boats.*.name' => 'required_with:boats|max:255',
            'boats.*.type' => 'nullable|max:255',
            'boats.*.year' => 'nullable|integer',
            'boats.*.builder' => 'nullable|max:255',
            'boats.*.renter' => 'nullable|max:255',
            'boats.*.city' => 'nullable|max:255',
            'boats.*.crew' => 'nullable|integer',
        ];
    }
}
