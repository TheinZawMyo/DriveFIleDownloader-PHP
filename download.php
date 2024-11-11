<?php
require_once 'GoogleDriveClient.php';

$googleClient = new GoogleDriveClient();
if(isset($_GET['file_id'])) {
    $fileId = $_GET['file_id'];
    $googleClient->downloadFileById($fileId);
}else {
    echo 'File id not found!';
}