<?php
declare(strict_types=1);

/**
 * CONTROLLER (Admin)
 * Login, logout and editing of the site content.
 */
class AdminController
{
    private const IDLE_TIMEOUT = 1800; // 30 minutes
    private const SECTIONS = [
        'info'       => 'General info',
        'contact'    => 'Contact',
        'objectives' => 'Objectives',
        'people'     => 'Officers & members',
        'activities' => 'Activities',
        'password'   => 'Password',
    ];

    public function __construct(private OrganizationModel $org, private AdminModel $auth)
    {
        Security::startSession();
        Security::noCacheHeaders();
    }

    /* ---------- login / logout ---------- */
    public function login(string $error = ''): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $notice = $error;
        if ($notice === '' && !$this->auth->accountExists()) {
            $notice = 'The admin account file (data/admin.json) is missing.';
        }
        $this->render('login.php', ['title' => 'Admin login', 'heading' => 'Admin login', 'error' => $notice, 'csrf' => Security::csrfToken(), 'loggedIn' => false]);
    }

    public function authenticate(): void
    {
        $ip = Security::clientIp();
        if (!Security::verifyCsrf($_POST['csrf'] ?? null)) {
            $this->login('Your session expired. Please try again.');
            return;
        }
        $wait = $this->auth->lockedFor($ip);
        if ($wait > 0) {
            $this->login('Too many failed attempts. Try again in ' . (int) ceil($wait / 60) . ' minute(s).');
            return;
        }
        $username = Security::clean($_POST['username'] ?? '', 100);
        $password = (string) ($_POST['password'] ?? '');

        if ($this->auth->verify($username, $password)) {
            $this->auth->clearFailures($ip);
            session_regenerate_id(true);
            $_SESSION['admin'] = true;
            $_SESSION['admin_last'] = time();
            $this->redirect('dashboard');
        }
        $this->auth->recordFailure($ip);
        $this->login('Wrong username or password.');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Security::verifyCsrf($_POST['csrf'] ?? null)) {
            $_SESSION = [];
            session_destroy();
        }
        $this->redirect('login');
    }

    /* ---------- dashboard ---------- */
    public function dashboard(string $section): void
    {
        $this->requireLogin();
        if (!isset(self::SECTIONS[$section])) {
            $section = 'info';
        }
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('dashboard.php', [
            'title'    => 'Dashboard',
            'heading'  => 'Admin dashboard',
            'sections' => self::SECTIONS,
            'section'  => $section,
            'data'     => $this->org->getData(),
            'flash'    => $flash,
            'csrf'     => Security::csrfToken(),
            'loggedIn' => true,
        ]);
    }

    public function save(string $section): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Security::verifyCsrf($_POST['csrf'] ?? null)) {
            $this->flash('error', 'Your session expired. Please try again.', $section);
        }

        switch ($section) {
            case 'info':
                $this->org->updateInfo([
                    'name'     => Security::clean($_POST['name'] ?? '', 120),
                    'fullName' => Security::clean($_POST['fullName'] ?? '', 200),
                    'location' => Security::clean($_POST['location'] ?? '', 150),
                    'founded'  => Security::clean($_POST['founded'] ?? '', 20),
                    'about'    => Security::clean($_POST['about'] ?? '', 3000),
                    'mission'  => Security::clean($_POST['mission'] ?? '', 2000),
                    'vision'   => Security::clean($_POST['vision'] ?? '', 2000),
                ]);
                break;

            case 'contact':
                $facebook = Security::clean($_POST['facebook'] ?? '', 300);
                if ($facebook !== '' && !preg_match('#^https?://#i', $facebook)) {
                    $this->flash('error', 'The Facebook link must start with http:// or https://', $section);
                }
                $this->org->updateContact([
                    'email'    => Security::clean($_POST['email'] ?? '', 150),
                    'phone'    => Security::clean($_POST['phone'] ?? '', 50),
                    'address'  => Security::clean($_POST['address'] ?? '', 250),
                    'facebook' => $facebook,
                ]);
                break;

            case 'objectives':
                $lines = preg_split('/\R/', (string) ($_POST['objectives'] ?? '')) ?: [];
                $objectives = [];
                foreach ($lines as $line) {
                    $line = Security::clean($line, 300);
                    if ($line !== '' && count($objectives) < 30) {
                        $objectives[] = $line;
                    }
                }
                $this->org->setObjectives($objectives);
                break;

            case 'people':
                $names = (array) ($_POST['people']['name'] ?? []);
                $positions = (array) ($_POST['people']['position'] ?? []);
                $groups = (array) ($_POST['people']['group'] ?? []);
                $people = [];
                foreach ($names as $i => $name) {
                    $name = Security::clean($name, 120);
                    $position = Security::clean($positions[$i] ?? '', 120);
                    if ($name === '' && $position === '') {
                        continue;
                    }
                    $people[] = ['name' => $name, 'position' => $position, 'group' => ($groups[$i] ?? '') === 'Officers' ? 'Officers' : 'Members'];
                    if (count($people) >= 200) {
                        break;
                    }
                }
                $this->org->setPeople($people);
                break;

            case 'activities':
                $titles = (array) ($_POST['activities']['title'] ?? []);
                $dates = (array) ($_POST['activities']['date'] ?? []);
                $descriptions = (array) ($_POST['activities']['description'] ?? []);
                $activities = [];
                foreach ($titles as $i => $title) {
                    $title = Security::clean($title, 150);
                    if ($title === '') {
                        continue;
                    }
                    $activities[] = [
                        'title'       => $title,
                        'date'        => Security::clean($dates[$i] ?? '', 60),
                        'description' => Security::clean($descriptions[$i] ?? '', 2000),
                    ];
                    if (count($activities) >= 100) {
                        break;
                    }
                }
                $this->org->setActivities($activities);
                break;

            case 'password':
                $current = (string) ($_POST['current'] ?? '');
                $new = (string) ($_POST['new'] ?? '');
                $confirm = (string) ($_POST['confirm'] ?? '');
                if (!$this->auth->verifyPassword($current)) {
                    $this->flash('error', 'The current password is wrong.', $section);
                }
                if (strlen($new) < 8 || $new !== $confirm || $new === $current) {
                    $this->flash('error', 'Use a new password of at least 8 characters, typed the same twice.', $section);
                }
                if ($this->auth->changePassword($new)) {
                    $this->flash('ok', 'Password changed.', $section);
                }
                $this->flash('error', 'Could not save the new password.', $section);
                return;

            default:
                $this->redirect('dashboard');
        }

        if ($this->org->save()) {
            $this->flash('ok', 'Saved. The changes are live on the site.', $section);
        }
        $this->flash('error', 'Could not save. Make sure the data folder is writable.', $section);
    }

    public function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 - Page not found</h1><p><a href="index.php">Back</a></p>';
    }

    /* ---------- helpers ---------- */
    private function isLoggedIn(): bool
    {
        if (empty($_SESSION['admin'])) {
            return false;
        }
        if (time() - (int) ($_SESSION['admin_last'] ?? 0) > self::IDLE_TIMEOUT) {
            $_SESSION = [];
            return false;
        }
        $_SESSION['admin_last'] = time();
        return true;
    }

    private function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }

    private function flash(string $type, string $message, string $section): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        $this->redirect('dashboard', $section);
    }

    private function redirect(string $action, string $section = ''): void
    {
        header('Location: index.php?action=' . $action . ($section !== '' ? '&section=' . urlencode($section) : ''));
        exit;
    }

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
