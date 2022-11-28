<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this['amount'] = (float)str_replace(',', '.', $this['amount']);
        $this['message'] = trim($this['message']);
        $this['card_number'] = (int)preg_replace('/\s/', '', $this['card_number']);
        $this['card_cvc'] = (int)$this['card_cvc'];
        if ($this['card_expiry'] != null) {
            $this['card_expiry'] = explode('/', preg_replace('/\s/', '', $this['card_expiry']));
            $this->merge([
                'card_expiry_month' => $this['card_expiry'][0],
                'card_expiry_year' => $this['card_expiry'][1],
                'card_expiry_date' => date_create_from_format('m/y', $this['card_expiry'][0] . '/' . $this['card_expiry'][1])->format('Y-m-d'),
            ]);
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
            'amount' => ['required', 'numeric', 'gt:0', 'lt:1000'],
            'message' => ['nullable', 'string', 'max:256'],
            'card_number' => ['required', 'digits_between:13,16'],
            'card_expiry_date' => ['required', 'date', 'after_or_equal:tomorrow'],
            'card_cvc' => ['required', 'integer', 'digits_between:3,4'],
            'card_holder_name' => ['required', 'string', 'max:128'],
        ];
    }
}
