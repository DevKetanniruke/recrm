<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'document_type' => 'required|string|in:Identity Proof,Address Proof,PAN Card,Photograph,Sale Agreement,Booking Form,Other',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB
            'booking_id' => 'nullable|exists:bookings,id',
            'verification_notes' => 'nullable|string',
        ];
    }
}
