<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/psl-config.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/../include/Handle.php';
use GitHubWebhook\Handler;

$handler = new Handler(SECRET, __DIR__);
if ($handler->handle()) {
    echo "Repository pulled.";
} else {
    echo "Invalid request.";
}