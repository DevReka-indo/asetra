<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $client;
    protected $driveService;
    protected $folderId;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->addScope(Drive::DRIVE);

        $clientId = env('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');
        $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');

        if ($clientId && $clientSecret && $refreshToken) {
            $this->client->setClientId($clientId);
            $this->client->setClientSecret($clientSecret);
            $this->client->refreshToken($refreshToken);
        } else {
            $path = env('GOOGLE_DRIVE_CREDENTIALS_PATH', 'storage/app/google-drive-credentials.json');
            $credentialsPath = (strpos($path, ':') !== false) ? $path : base_path($path);
            $this->client->setAuthConfig($credentialsPath);
        }

        $this->folderId = env('GOOGLE_DRIVE_FOLDER_ID');
        $this->driveService = new Drive($this->client);
    }

    /**
     * Upload a file to Google Drive and return the public URL.
     *
     * @param \Illuminate\Http\UploadedFile|string $file File instance or absolute file path
     * @param string $filename
     * @return string|null Public URL of the uploaded file
     */
    public function uploadFile($file, $filename)
    {
        try {
            $filePath = is_string($file) ? $file : $file->getRealPath();
            $mimeType = is_string($file) ? mime_content_type($file) : $file->getClientMimeType();

            $fileMetadata = new DriveFile([
                'name' => $filename,
                'parents' => [$this->folderId]
            ]);

            $content = file_get_contents($filePath);

            $uploadedFile = $this->driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id, webContentLink, webViewLink',
                'supportsAllDrives' => true
            ]);

            $fileId = $uploadedFile->id;

            // Make the file publicly accessible so anyone can view it
            $permission = new Permission([
                'role' => 'reader',
                'type' => 'anyone'
            ]);

            $this->driveService->permissions->create($fileId, $permission, [
                'supportsAllDrives' => true
            ]);

            // Return direct access URL that can be directly used in <img> tags
            return "https://lh3.googleusercontent.com/d/" . $fileId;
        } catch (\Exception $e) {
            Log::error('Google Drive Upload Error: ' . $e->getMessage());
            return null;
        }
    }
}
