<?php
require_once 'GoogleDriveClient.php';

$googleClient = new GoogleDriveClient();
$files = $googleClient->driveFileList();
$totalFiles = count($files);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive Files</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5 mb-4">Google Drive Files</h1>
        <p>Total Files: <?php echo $totalFiles; ?></p>
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">File Name</li>
            <?php foreach ($files as $file): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?php echo $file->getName(); ?>
                    <a href="download.php?file_id=<?php echo $file->getId(); ?>" class="btn btn-primary">Download</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <!-- Bootstrap JS (optional, for certain components that require it) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>