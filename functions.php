<?php
function deleteDirectory($dirPath) {
    $files = glob($dirPath . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            deleteDirectory($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dirPath);
}
