<?php
declare(strict_types=1);

/**
 * CONTROLLER (root)
 * Gets data from the Model and passes it to a View.
 */
class HomeController
{
    public function __construct(private OrganizationModel $model)
    {
    }

    public function home(): void
    {
        $this->render('home.php', [
            'org' => [
                'name'     => $this->model->getName(),
                'fullName' => $this->model->getFullName(),
                'location' => $this->model->getLocation(),
                'founded'  => $this->model->getFounded(),
                'about'    => $this->model->getAbout(),
                'mission'  => $this->model->getMission(),
                'vision'   => $this->model->getVision(),
                'contact'  => $this->model->getContact(),
                'objectives' => $this->model->getObjectives(),
            ],
            'peopleByGroup' => $this->model->getPeopleByGroup(),
            // Home page previews the first three activities only.
            'activities' => array_slice($this->model->getActivities(), 0, 3, true),
        ]);
    }

    public function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 - Page not found</h1><p><a href="index.php">Back to home</a></p>';
    }

    private function render(string $view, array $data = []): void
    {
        $e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        extract($data, EXTR_SKIP);
        require __DIR__ . '/' . $view;
    }
}
