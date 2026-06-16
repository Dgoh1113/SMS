<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttachmentSaver
{
    /**
     * Parse attachment string (comma separated paths or blob), generate SHA1,
     * and insert into ATTACHMENT and LEAD_ACT_ATTACHMENT tables.
     *
     * @param int $leadActId
     * @param mixed $attachmentValue The raw attachment data
     */
    public static function saveFileAsAttachment(int $leadActId, $file): void
    {
        if ($file && $file->isValid() && str_starts_with($file->getMimeType(), 'image/')) {
            $sha1 = sha1_file($file->path());
            
            $exists = DB::selectOne('SELECT 1 FROM "ATTACHMENT" WHERE "SHA1" = ?', [$sha1]);
            
            if (!$exists) {
                $dir = 'inquiry-attachments/lead_act_'.$leadActId;
                $path = $file->store($dir, 'public');
                
                if ($path) {
                    try {
                        DB::insert('INSERT INTO "ATTACHMENT" ("SHA1", "CONTENT") VALUES (?, ?)', [$sha1, $path]);
                    } catch (\Exception $e) {
                        Log::error("Failed to insert into ATTACHMENT: " . $e->getMessage());
                        throw $e;
                    }
                }
            }

            $linkExists = DB::selectOne(
                'SELECT 1 FROM "LEAD_ACT_ATTACHMENT" WHERE "LEAD_ACTID" = ? AND "SHA1" = ?', 
                [$leadActId, $sha1]
            );
            
            if (!$linkExists) {
                try {
                    DB::insert(
                        'INSERT INTO "LEAD_ACT_ATTACHMENT" ("LEAD_ACTID", "SHA1") VALUES (?, ?)', 
                        [$leadActId, $sha1]
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to insert into LEAD_ACT_ATTACHMENT: " . $e->getMessage());
                    throw $e;
                }
            }
        }
    }

    public static function saveAttachments(int $leadActId, $attachmentValue): void
    {
        if ($attachmentValue === null) {
            return;
        }

        $raw = (string) $attachmentValue;
        if (trim($raw) === '') {
            return;
        }

        $blobs = [];

        // Check if it is a comma separated string of paths
        if (!preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $raw) && str_contains($raw, ',')) {
            $parts = explode(',', $raw);
            foreach ($parts as $part) {
                $clean = trim($part);
                if ($clean !== '') {
                    $blobs[] = $clean;
                }
            }
        } else {
            $blobs[] = $raw;
        }

        foreach ($blobs as $blobContent) {
            $sha1 = sha1($blobContent);

            // Insert into ATTACHMENT if not exists
            $exists = DB::selectOne('SELECT 1 FROM "ATTACHMENT" WHERE "SHA1" = ?', [$sha1]);
            if (!$exists) {
                try {
                    DB::insert('INSERT INTO "ATTACHMENT" ("SHA1", "CONTENT") VALUES (?, ?)', [$sha1, $blobContent]);
                } catch (\Exception $e) {
                    Log::error("Failed to insert into ATTACHMENT: " . $e->getMessage());
                }
            }

            // Insert into LEAD_ACT_ATTACHMENT if not exists
            $linkExists = DB::selectOne(
                'SELECT 1 FROM "LEAD_ACT_ATTACHMENT" WHERE "LEAD_ACTID" = ? AND "SHA1" = ?', 
                [$leadActId, $sha1]
            );
            if (!$linkExists) {
                try {
                    DB::insert(
                        'INSERT INTO "LEAD_ACT_ATTACHMENT" ("LEAD_ACTID", "SHA1") VALUES (?, ?)', 
                        [$leadActId, $sha1]
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to insert into LEAD_ACT_ATTACHMENT: " . $e->getMessage());
                }
            }
        }
    }
}
