<?php
use GitHubWebhook\Handler;

$handler = new Handler(SECRET, __DIR__);
if ($handler->handle()) {
    echo "Repository pulled.";
} else {
    echo "Invalid request.";
}