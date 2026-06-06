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

    protected function buildInquiryActivityAttachmentUrls(
        mixed $attachmentRaw,
        int $leadId,
        int $leadActId,
        string $serveRoute,
        string $activityRoute
    ): array {
        $urls = [];
        if ($attachmentRaw === null || trim((string) $attachmentRaw) === '') {
            return $urls;
        }

        $attachmentStr = trim((string) $attachmentRaw);
        $attachmentStr = str_replace('\\', '/', $attachmentStr);

        if (str_contains($attachmentStr, ',') || str_starts_with($attachmentStr, 'inquiry-attachments')) {
            foreach (explode(',', $attachmentStr) as $path) {
                $path = trim(str_replace('\\', '/', $path));
                if ($path !== '' && str_starts_with($path, 'inquiry-attachments/')) {
                    $urls[] = route($serveRoute, ['path' => $path]);
                }
            }

            return array_values(array_unique($urls));
        }

        if (str_starts_with($attachmentStr, 'inquiry-attachments/')) {
            $urls[] = route($serveRoute, ['path' => $attachmentStr]);
        } elseif ($leadId > 0 && $leadActId > 0 && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $attachmentStr)) {
            $urls[] = route($activityRoute, ['leadId' => $leadId, 'leadActId' => $leadActId]);
        }

        return array_values(array_unique($urls));
    }
}
