<?php
include_once '../include/psl-config.php';
include_once '../include/Handle.php';
use GitHubWebhook\Handler;

$handler = new Handler(SECRET, '../');
if ($handler->handle()) {
    echo "Repository pulled.";
} else {
    echo "Invalid request.";
}