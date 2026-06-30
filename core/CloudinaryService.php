<?php

/**
 * CloudinaryService
 *
 * Server-side signed upload and delete via Cloudinary REST API.
 * No SDK needed — uses PHP curl directly.
 * Credentials from config/app.php → .env (never in repo).
 *
 * Usage:
 *   $result = CloudinaryService::upload($_FILES['image'], 'team');
 *   $url    = $result['secure_url'];
 *
 *   CloudinaryService::delete($publicId);
 *
 * Folder structure:
 *   {cloudinary_folder}/{subfolder}/{public_id}
 *   e.g. ows/wkk/team/martha-olgen
 */

class CloudinaryService
{
    /**
     * Upload a file to Cloudinary.
     * Returns Cloudinary response array with secure_url and public_id.
     *
     * @param array  $file       $_FILES['field'] entry
     * @param string $subfolder  'team' | 'events' | 'participants' | 'pages'
     * @param string|null $publicId  Custom public_id (optional — Cloudinary auto-generates if null)
     * @return array             ['secure_url' => ..., 'public_id' => ...]
     * @throws RuntimeException  On upload failure
     */
    public static function upload(array $file, string $subfolder, ?string $publicId = null): array
    {
        $config    = require __DIR__ . '/../config/app.php';
        $cloud     = $config['cloudinary_cloud'];
        $apiKey    = $config['cloudinary_key'];
        $apiSecret = $config['cloudinary_secret'];
        $folder    = $config['cloudinary_folder'] . '/' . $subfolder;
        $timestamp = time();

        // Detect resource type from mime type
        $resourceType = match (true) {
            str_starts_with($file['type'], 'video/') => 'video',
            default                                   => 'image',
        };

        // Build signature params
        $params = [
            'folder'    => $folder,
            'timestamp' => $timestamp,
        ];

        if ($publicId) {
            $params['public_id'] = $publicId;
        }

        // Generate signature
        ksort($params);
        $paramString = '';
        foreach ($params as $key => $value) {
            $paramString .= $key . '=' . $value . '&';
        }
        $paramString = rtrim($paramString, '&');

        $signature = sha1($paramString . $apiSecret);

        // Build POST data
        $postData = $params + [
            'api_key'   => $apiKey,
            'signature' => $signature,
            'file'      => new CURLFile($file['tmp_name'], $file['type'], $file['name']),
        ];

        // Send to Cloudinary — resource type determines endpoint
        $url = "https://api.cloudinary.com/v1_1/{$cloud}/{$resourceType}/upload";
        $ch  = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException('Cloudinary upload failed: ' . $response);
        }

        $result = json_decode($response, true);

        if (empty($result['secure_url'])) {
            throw new RuntimeException('Cloudinary upload failed: no secure_url in response');
        }

        return [
            'secure_url' => $result['secure_url'],
            'public_id'  => $result['public_id'],
        ];
    }

    /**
     * Delete a file from Cloudinary by public_id.
     *
     * @param string $publicId  Cloudinary public_id
     * @return bool
     */
    public static function delete(string $publicId, string $resourceType = 'image'): bool
    {
        $config    = require __DIR__ . '/../config/app.php';
        $cloud     = $config['cloudinary_cloud'];
        $apiKey    = $config['cloudinary_key'];
        $apiSecret = $config['cloudinary_secret'];
        $timestamp = time();

        $params = ['public_id' => $publicId, 'timestamp' => $timestamp];
        ksort($params);
        $paramString = '';
        foreach ($params as $key => $value) {
            $paramString .= $key . '=' . $value . '&';
        }
        $paramString = rtrim($paramString, '&');
        $signature = sha1($paramString . $apiSecret);

        $postData = $params + [
            'api_key'   => $apiKey,
            'signature' => $signature,
        ];

        // Resource type determines destroy endpoint
        $url = "https://api.cloudinary.com/v1_1/{$cloud}/{$resourceType}/destroy";
        $ch  = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        return ($result['result'] ?? '') === 'ok';
    }

    /**
     * Extract Cloudinary public_id from a secure_url.
     * URL pattern: https://res.cloudinary.com/{cloud}/image/upload/v{version}/{public_id}.{ext}
     *
     * @param string $url  Cloudinary secure_url
     * @return string|null public_id, or null if the URL doesn't match
     */
    public static function extractPublicId(string $url): ?string
    {
        if (preg_match('#/upload/(?:v\d+/)?(.+)\.\w{3,4}$#', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Generate a clean public_id from a display name.
     * Used for team members, participants etc.
     *
     * @param string $name
     * @return string
     */
    public static function generatePublicId(string $name): string
    {
        $id = strtolower(trim($name));
        $id = iconv('UTF-8', 'ASCII//TRANSLIT', $id);
        $id = preg_replace('/[^a-z0-9\-]/', '-', $id);
        $id = preg_replace('/-+/', '-', $id);
        return trim($id, '-');
    }
}
