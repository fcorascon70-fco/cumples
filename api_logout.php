<?php
session_start();
session_unset();
session_destroy();

require_once 'config.php';
sendSecurityHeaders();
echo json_encode(['success' => true]);
