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

    /**
     * Render an HTML wrapper for viewing the attachment natively with a custom title/favicon.
     */
    public static function serveHtmlWrapper(string $name, string $mime, \Illuminate\Http\Request $request): ?\Symfony\Component\HttpFoundation\Response
    {
        if ($request->query('view') !== '1') {
            return null;
        }

        if (empty($name)) {
            $name = (string) $request->route('filename');
            $name = trim(urldecode($name));
        }

        $title = htmlspecialchars($name ?: 'Attachment');
        $query = array_diff_key($request->query(), ['view' => '']);
        $imgSrc = htmlspecialchars(url()->current() . ($query ? '?' . http_build_query($query) : ''));
        $icon = htmlspecialchars(asset('sql-logo.png') . '?v=' . time());
        
        $content = str_starts_with($mime, 'image/') 
            ? "<img src=\"{$imgSrc}\" alt=\"{$title}\">"
            : "<embed src=\"{$imgSrc}\" type=\"{$mime}\" width=\"100%\" height=\"100%\">";

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="icon" type="image/png" href="{$icon}">
    <style>
        body { margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #0e0e0e; overflow: hidden; }
        img { max-width: 100%; max-height: 100vh; object-fit: contain; }
        embed { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>
    {$content}
</body>
</html>
HTML;
        return response($html);
    }
}
