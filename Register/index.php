<?php
declare(strict_types=1);

/**
 * FRONT CONTROLLER for Register (switch case).
 * URL examples: Register/index.php  |  Register/index.php?action=submit (POST)
 */
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../model.php';
require_once __DIR__ . '/model.php';
require_once __DIR__ . '/controller.php';

$action = $_GET['action'] ?? 'form';
$controller = new RegisterController(new OrganizationModel(), new RegisterModel());

switch ($action) {
    case 'form':
        $controller->form();
        break;

    case 'submit':
        $controller->submit();
        break;

    case 'success':
        $controller->success();
        break;

    default:
        $controller->notFound();
        break;
}
