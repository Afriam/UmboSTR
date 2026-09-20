<?php
declare(strict_types=1);

/**
 * CONTROLLER (Register)
 */
class RegisterController
{
    private const MAX_PER_WINDOW = 3;     // registrations per browser session...
    private const WINDOW_SECONDS = 600;   // ...per 10 minutes

    public function __construct(private OrganizationModel $org, private RegisterModel $model)
    {
    }

    /** Show the registration form. */
    public function form(array $old = [], array $errors = [], string $notice = ''): void
    {
        $fields = [];
        foreach (RegisterModel::FIELDS as $key => $def) {
            $fields[] = $def + [
                'key'    => $key,
                'value'  => (string) ($old[$key] ?? ''),
                'error'  => (string) ($errors[$key] ?? ''),
                'suffix' => $def['required'] ? ' *' : ' (optional)',
            ];
        }

        // On hosting without PHP the browser sends to Google directly (needs the entry IDs).
        $direct = RegisterModel::STATIC_ENTRY_IDS !== [] && !empty($GLOBALS['umbo_static_build']);
        $formAttrs = $direct
            ? ' data-google-post="' . htmlspecialchars($this->model->actionUrl(), ENT_QUOTES, 'UTF-8') . '"'
              . ' data-entries="' . htmlspecialchars((string) json_encode(RegisterModel::STATIC_ENTRY_IDS), ENT_QUOTES, 'UTF-8') . '"'
            : '';

        $this->render('register.php', [
            'title'      => 'Register',
            'heading'    => 'Membership registration',
            'fields'     => $fields,
            'notice'     => $notice,
            'csrf'       => $direct ? '' : Security::csrfToken(),
            'formAction' => $direct ? '#' : 'index.php?action=submit',
            'formAttrs'  => $formAttrs,
            'direct'     => $direct,
        ]);
    }

    /** Fallback page for hosting without PHP and without entry IDs: link to the Google Form. */
    public function formStatic(): void
    {
        $GLOBALS['umbo_static_build'] = true;
        if (RegisterModel::STATIC_ENTRY_IDS !== []) {
            $this->form();
            return;
        }
        $this->render('register_link.php', [
            'title'   => 'Register',
            'heading' => 'Membership registration',
            'viewUrl' => $this->model->viewUrl(),
        ]);
    }

    /** Handle the POST: validate, then fill in and submit the Google Form. */
    public function submit(): void
    {
        Security::startSession();
        Security::noCacheHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf'] ?? null)) {
            $this->form([], [], 'Your session expired. Please fill in the form again.');
            return;
        }
        if (!empty($_POST['website'])) { // hidden field: only bots fill it in
            $this->redirect('success');
            return;
        }

        $recent = array_filter($_SESSION['reg_times'] ?? [], static fn ($t) => $t > time() - self::WINDOW_SECONDS);
        if (count($recent) >= self::MAX_PER_WINDOW) {
            $this->form($_POST, [], 'Too many registrations from this browser. Please wait a few minutes.');
            return;
        }

        [$clean, $errors] = $this->model->validate($_POST);
        if ($errors !== []) {
            $this->form($clean, $errors, 'Please fix the highlighted fields.');
            return;
        }

        [$ok, $message] = $this->model->submit($clean);
        if (!$ok) {
            $this->form($clean, [], $message);
            return;
        }

        $recent[] = time();
        $_SESSION['reg_times'] = array_values($recent);
        $_SESSION['registered'] = true;
        $this->redirect('success');
    }

    public function success(): void
    {
        Security::startSession();
        if (empty($_SESSION['registered'])) {
            $this->redirect('form');
            return;
        }
        unset($_SESSION['registered']);
        $this->render('register_done.php', ['title' => 'Registered', 'heading' => 'Registration sent']);
    }

    public function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 - Page not found</h1><p><a href="index.php">Back to registration</a></p>';
    }

    private function redirect(string $action): void
    {
        header('Location: index.php?action=' . $action);
        exit;
    }

    /** Render a body view inside layout.php. */
    private function render(string $view, array $data = []): void
    {
        $e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $orgName = $this->org->getName();
        extract($data, EXTR_SKIP);

        ob_start();
        require __DIR__ . '/' . $view;
        $content = ob_get_clean();

        require __DIR__ . '/layout.php';
    }
}
