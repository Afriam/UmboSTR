<?php
declare(strict_types=1);

/**
 * STATIC EXPORT for GitHub Pages (which cannot run PHP).
 *
 * Usage (in the umboSTR folder):   php build.php
 * It runs the same Model/Controller/View code and writes:
 *   index.html
 *   Activities/index.html, Activities/view-1.html, ...
 *   Register/index.html
 * Re-run it after every change to the content, then upload the .html files.
 *
 * NOTE: the Admin dashboard and the automatic Google Form submission need PHP,
 * so on GitHub Pages the admin link is removed and Register links to the Google Form
 * (unless RegisterModel::STATIC_ENTRY_IDS is filled in).
 */
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/model.php';
require_once __DIR__ . '/controller.php';
require_once __DIR__ . '/Activities/controller.php';
require_once __DIR__ . '/Register/model.php';
require_once __DIR__ . '/Register/controller.php';

final class StaticBuilder
{
    public function __construct(private string $outDir)
    {
    }

    public function build(): void
    {
        $model      = new OrganizationModel();
        $home       = new HomeController($model);
        $activities = new ActivitiesController($model);
        $register   = new RegisterController($model, new RegisterModel());

        $this->write('index.html', $this->capture(static function () use ($home) { $home->home(); }));
        $this->write('Activities/index.html', $this->capture(static function () use ($activities) { $activities->index(); }));

        foreach (array_keys($model->getActivities()) as $id) {
            $this->write("Activities/view-{$id}.html", $this->capture(static function () use ($activities, $id) { $activities->show($id); }));
        }

        $this->write('Register/index.html', $this->capture(static function () use ($register) { $register->formStatic(); }));
    }

    private function capture(callable $action): string
    {
        ob_start();
        $action();
        return $this->rewriteLinks((string) ob_get_clean());
    }

    /** Turn the PHP router links into plain file links. */
    private function rewriteLinks(string $html): string
    {
        $html = preg_replace('/index\.php\?action=view&amp;id=(\d+)/', 'view-$1.html', $html);
        $html = str_replace('index.php?page=activities', 'Activities/index.html', $html);
        $html = str_replace('index.php?page=register', 'Register/index.html', $html);
        $html = preg_replace('#<a class="admin-link"[^>]*>.*?</a>\s*#s', '', $html);
        $html = str_replace('../index.php', '../index.html', $html);
        return str_replace('href="index.php"', 'href="index.html"', $html);
    }

    private function write(string $relativePath, string $html): void
    {
        $path = $this->outDir . '/' . $relativePath;
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        file_put_contents($path, $html);
        echo "Wrote {$relativePath}\n";
    }
}

(new StaticBuilder(__DIR__))->build();
