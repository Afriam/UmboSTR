<?php
declare(strict_types=1);

/**
 * FRONT CONTROLLER for Activities (switch case).
 * URL examples: Activities/index.php  |  Activities/index.php?action=view&id=1
 */
require_once __DIR__ . '/../model.php';
require_once __DIR__ . '/controller.php';

$action = $_GET['action'] ?? 'list';
$controller = new ActivitiesController(new OrganizationModel());

switch ($action) {
    case 'list':
        $controller->index();
        break;

    case 'view':
        $controller->show((int) ($_GET['id'] ?? 0));
        break;

    default:
        $controller->notFound();
        break;
}
