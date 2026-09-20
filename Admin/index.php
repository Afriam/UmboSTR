<?php
declare(strict_types=1);

/**
 * FRONT CONTROLLER for Admin (switch case).
 * URL examples: Admin/index.php  |  Admin/index.php?action=dashboard&section=people
 */
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../model.php';
require_once __DIR__ . '/model.php';
require_once __DIR__ . '/controller.php';

$action = $_GET['action'] ?? 'dashboard';
$controller = new AdminController(new OrganizationModel(), new AdminModel());

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->authenticate();
        } else {
            $controller->login();
        }
        break;

    case 'logout':
        $controller->logout();
        break;

    case 'dashboard':
        $controller->dashboard((string) ($_GET['section'] ?? 'info'));
        break;

    case 'save':
        $controller->save((string) ($_GET['section'] ?? ''));
        break;

    default:
        $controller->notFound();
        break;
}
