<?php
declare(strict_types=1);

/**
 * FRONT CONTROLLER - decides which action to run (switch case).
 * URL examples: index.php  |  index.php?page=activities  |  index.php?page=register  |  index.php?page=admin
 */
require_once __DIR__ . '/model.php';
require_once __DIR__ . '/controller.php';

$page = $_GET['page'] ?? 'home';
$controller = new HomeController(new OrganizationModel());

switch ($page) {
    case 'home':
        $controller->home();
        break;

    case 'activities':
        header('Location: Activities/index.php');
        exit;

    case 'register':
        header('Location: Register/index.php');
        exit;

    case 'admin':
        header('Location: Admin/index.php');
        exit;

    default:
        $controller->notFound();
        break;
}
