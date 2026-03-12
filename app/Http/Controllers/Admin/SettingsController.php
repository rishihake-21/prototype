<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'institution_name' => config('sms.institution_name', 'Institution Name'),
            'allowed_domains' => config('sms.allowed_domains', ['institution.edu']),
            'require_admin_approval' => config('sms.require_admin_approval', true),
            'pdf_paper_size' => config('sms.pdf_paper_size', 'A4'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'allowed_domains' => 'required|string',
            'require_admin_approval' => 'boolean',
            'pdf_paper_size' => 'required|in:A4,Letter',
        ]);

        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $envContent = preg_replace(
            '/SMS_INSTITUTION_NAME=.*/',
            'SMS_INSTITUTION_NAME="' . $validated['institution_name'] . '"',
            $envContent
        );

        $envContent = preg_replace(
            '/SMS_ALLOWED_DOMAINS=.*/',
            'SMS_ALLOWED_DOMAINS=' . $validated['allowed_domains'],
            $envContent
        );

        $envContent = preg_replace(
            '/SMS_REQUIRE_ADMIN_APPROVAL=.*/',
            'SMS_REQUIRE_ADMIN_APPROVAL=' . ($validated['require_admin_approval'] ? 'true' : 'false'),
            $envContent
        );

        $envContent = preg_replace(
            '/SMS_PDF_PAPER_SIZE=.*/',
            'SMS_PDF_PAPER_SIZE=' . $validated['pdf_paper_size'],
            $envContent
        );

        file_put_contents($envPath, $envContent);

        Artisan::call('config:clear');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
