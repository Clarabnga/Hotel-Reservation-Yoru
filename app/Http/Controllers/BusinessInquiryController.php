<?php

namespace App\Http\Controllers;

use App\Models\BusinessInquiry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessInquiryController extends Controller
{
    public function create()
    {
        return view('home.business');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'], 'contact_person' => ['required', 'string', 'max:150'],
            'business_email' => ['required', 'email', 'max:255'], 'phone' => ['required', 'string', 'max:30'],
            'request_type' => ['required', Rule::in(['corporate_stay', 'long_stay', 'group_booking', 'meeting', 'partnership', 'other'])],
            'guests' => ['required', 'integer', 'min:1', 'max:1000'], 'rooms' => ['required', 'integer', 'min:1', 'max:500'],
            'check_in' => ['nullable', 'date', 'after_or_equal:today'], 'check_out' => ['nullable', 'date', 'after:check_in'],
            'budget' => ['nullable', 'string', 'max:100'], 'message' => ['required', 'string', 'max:3000'],
        ]);
        BusinessInquiry::create($data);

        return back()->with('success', 'Thank you. Our business team will contact you shortly.');
    }
}
