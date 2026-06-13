<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesInquiryAttachments
{
    protected function resolveInquiryAttachmentPath(string $path): ?string
    {
        $path = ltrim($path, '/');
        $candidates = [
            Storage::disk('public')->path($path),
            storage_path('app/public/'.$path),
            storage_path('app/private/'.$path),
            storage_path('app/'.$path),
            public_path($path),
            public_path('storage/'.$path),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
