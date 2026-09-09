<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerDocumentRequest;
use App\Models\Customer;
use App\Models\CustomerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerDocumentController extends Controller
{
    public function store(StoreCustomerDocumentRequest $request, Customer $customer)
    {
        if ($customer->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $file = $request->file('document_file');
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filePath = $file->storeAs("customer_documents/{$customer->company_id}/{$customer->id}", $fileName, 'local');

        CustomerDocument::create([
            'company_id' => auth()->user()->company_id,
            'customer_id' => $customer->id,
            'booking_id' => $request->booking_id,
            'document_type' => $request->document_type,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_by_user_id' => auth()->id(),
            'verification_status' => 'Pending',
            'verification_notes' => $request->verification_notes,
        ]);

        return back()->with('success', "Document '{$file->getClientOriginalName()}' uploaded successfully to secure vault!");
    }

    public function download(CustomerDocument $document)
    {
        if ($document->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            return back()->with('error', 'Requested document file was not found on secure storage disk.');
        }

        return Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function verify(Request $request, CustomerDocument $document)
    {
        if ($document->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $request->validate([
            'verification_status' => 'required|in:Verified,Rejected,Pending',
            'verification_notes' => 'nullable|string',
        ]);

        $document->update([
            'verification_status' => $request->verification_status,
            'verification_notes' => $request->verification_notes,
        ]);

        return back()->with('success', "Document status updated to {$request->verification_status}!");
    }
}
