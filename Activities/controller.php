<?php
declare(strict_types=1);

/**
 * CONTROLLER (Activities)
 */
class ActivitiesController
{
    public function __construct(private OrganizationModel $model)
    {
    }

    public function index(): void
    {
        $this->render('activities.php', [
            'orgName'    => $this->model->getName(),
            'activities' => $this->model->getActivities(),
            'selected'   => null,
        ]);
    }

    public function show(int $id): void
    {
        $activity = $this->model->getActivity($id);
        if ($activity === null) {
            $this->notFound();
            return;
        }
        $this->render('activities.php', [
            'orgName'    => $this->model->getName(),
            'activities' => $this->model->getActivities(),
            'selected'   => $activity,
        ]);
    }

    public function notFound(): void
    {
        http_response_code(404);
        echo '<h1>404 - Activity not found</h1><p><a href="index.php">Back to activities</a></p>';
    }

    private function render(string $view, array $data = []): void
    {
        $e = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        extract($data, EXTR_SKIP);
        require __DIR__ . '/' . $view;
    }
}
