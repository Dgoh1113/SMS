<?php

namespace App\Support;

/**
 * Helper for building attachment URLs with consistent logic.
 */
class AttachmentUrlBuilder
{
    /**
     * Build attachment URLs from attachment data
     */
    public static function buildUrls(
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

        // Handle comma-separated or inquiry-attachments prefixed paths
        if (str_contains($attachmentStr, ',') || str_starts_with($attachmentStr, 'inquiry-attachments')) {
            foreach (explode(',', $attachmentStr) as $path) {
                $path = trim(str_replace('\\', '/', $path));
                if ($path !== '' && str_starts_with($path, 'inquiry-attachments/')) {
                    $urls[] = route($serveRoute, ['path' => $path]);
                }
            }

            return array_values(array_unique($urls));
        }

        // Single path handling
        if (str_starts_with($attachmentStr, 'inquiry-attachments/')) {
            $urls[] = route($serveRoute, ['path' => $attachmentStr]);
        } elseif ($leadId > 0 && $leadActId > 0 && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $attachmentStr)) {
            // Binary data detected
            $urls[] = route($activityRoute, ['leadId' => $leadId, 'leadActId' => $leadActId]);
        }

        return array_values(array_unique($urls));
    }
}
