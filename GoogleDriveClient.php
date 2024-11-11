<?php

require_once __DIR__.'/vendor/autoload.php';

session_start();

class GoogleDriveClient {
    private $client;

    public function __construct() {
        $this->client = new Google\Client();
        $this->client->setAuthConfig('credentials.json');
        $this->client->addScope([
            Google\Service\Drive::DRIVE,
            Google\Service\Drive::DRIVE_METADATA_READONLY,
            'https://www.googleapis.com/auth/drive.readonly',
        ]);
        $this->client->setAccessType("offline");
    }

    public function driveFileList() {
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = isset($_SESSION['refresh_token']) ? $_SESSION['refresh_token'] : null;
                if ($refreshToken) {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $_SESSION['access_token'] = $this->client->getAccessToken();
                } else {
                    // If there's no refresh token, redirect to re-authorize the application
                    $this->redirectToAuthorization();
                    return;
                }
            }
            $drive = new Google\Service\Drive($this->client);
            $allFiles = [];
            $pageToken = null;

            do {
                $response = $drive->files->listFiles([
                    'pageSize' => 100,
                    'pageToken' => $pageToken,
                ]);

                $files = $response->getFiles();
                $allFiles = array_merge($allFiles, $files);
                $pageToken = $response->getNextPageToken();
            } while ($pageToken != null);

            return $allFiles;
        } else {
            // If there's no access token, redirect to authorize the application
            $this->redirectToAuthorization();
        }
    }

    public function downloadFileById($fileId) {
        if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $this->client->setAccessToken($_SESSION['access_token']);
            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = isset($_SESSION['refresh_token']) ? $_SESSION['refresh_token'] : null;
                if ($refreshToken) {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                    $_SESSION['access_token'] = $this->client->getAccessToken();
                } else {
                    // If there's no refresh token, redirect to re-authorize the application
                    $this->redirectToAuthorization();
                    return;
                }
            }
            $drive = new Google\Service\Drive($this->client);
            $file = $drive->files->get($fileId);
            $fileMimeType = $file->getMimeType();
            var_dump($fileMimeType);
            $fileName = $file->getName();
            if (strpos($fileMimeType, 'application/vnd.google-apps') !== false) {
                // If it's a Google Docs Editors file, export it
                $response = $drive->files->export($fileId, 'application/pdf', array('alt' => 'media'));
                $content = $response->getBody()->getContents();
                $fileName .= '.pdf';
            } else {
                // For other file types, download with the original file name
                $response = $drive->files->get($fileId, ['alt' => 'media']);
                $content = $response->getBody()->getContents();
            }
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename={$fileName}");
            echo $content;
        } else {
            // If there's no access token, redirect to authorize the application
            $this->redirectToAuthorization();
        }
    }


    private function redirectToAuthorization() {
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/callback.php';
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        exit;
    }

    private function getFileExtension($mimeType) {
        $extensions = [
            'application/vnd.google-apps.document' => 'docx',
            'application/vnd.google-apps.spreadsheet' => 'xlsx',
            'application/vnd.google-apps.presentation' => 'pptx'
        ];

        if (array_key_exists($mimeType, $extensions)) {
            return $extensions[$mimeType];
        } else {
            return ''; // Default to empty string if extension not found
        }
    }
}

?>