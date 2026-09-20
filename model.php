<?php
declare(strict_types=1);

/**
 * MODEL
 * All organization content is stored in data/content.json.
 * The admin dashboard edits that file; the pages only read from here.
 */
class OrganizationModel
{
    private string $file;
    private array $data;

    public function __construct(?string $file = null)
    {
        $this->file = $file ?? __DIR__ . '/data/content.json';
        $this->data = $this->load();
    }

    public static function defaults(): array
    {
        return [
            'name'       => 'UMBO STR ORGANIZATION',
            'fullName'   => 'United Mangali Brotherhood/Sisterhood Organization (UMBO-STR)',
            'location'   => 'Mangali, Tanudan, Kalinga',
            'founded'    => '2023',
            'about'      => '[About the organization]',
            'mission'    => '[Mission statement]',
            'vision'     => '[Vision statement]',
            'objectives' => [],
            'people'     => [],
            'contact'    => ['email' => '', 'phone' => '', 'address' => '', 'facebook' => ''],
            'activities' => [],
        ];
    }

    private function load(): array
    {
        $defaults = self::defaults();
        if (!is_file($this->file)) {
            return $defaults;
        }
        $json = json_decode((string) file_get_contents($this->file), true);
        if (!is_array($json)) {
            return $defaults;
        }
        $data = array_replace($defaults, $json);
        $data['contact'] = array_replace($defaults['contact'], is_array($data['contact']) ? $data['contact'] : []);
        return $data;
    }

    public function save(): bool
    {
        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json !== false && file_put_contents($this->file, $json, LOCK_EX) !== false;
    }

    /* ---------- read ---------- */
    public function getData(): array      { return $this->data; }
    public function getName(): string     { return (string) $this->data['name']; }
    public function getFullName(): string { return (string) $this->data['fullName']; }
    public function getLocation(): string { return (string) $this->data['location']; }
    public function getFounded(): string  { return (string) $this->data['founded']; }
    public function getAbout(): string    { return (string) $this->data['about']; }
    public function getMission(): string  { return (string) $this->data['mission']; }
    public function getVision(): string   { return (string) $this->data['vision']; }
    public function getObjectives(): array { return $this->data['objectives']; }
    public function getContact(): array   { return $this->data['contact']; }

    /** @return array<string, array<int, array{name:string,position:string,group:string}>> */
    public function getPeopleByGroup(): array
    {
        $groups = ['Officers' => [], 'Members' => []];
        foreach ($this->data['people'] as $person) {
            $group = ($person['group'] ?? '') === 'Officers' ? 'Officers' : 'Members';
            $groups[$group][] = $person;
        }
        return array_filter($groups);
    }

    /** @return array<int, array{title:string,date:string,description:string}> keyed 1..n */
    public function getActivities(): array
    {
        $list = array_values($this->data['activities']);
        return $list ? array_combine(range(1, count($list)), $list) : [];
    }

    public function getActivity(int $id): ?array
    {
        return $this->getActivities()[$id] ?? null;
    }

    /* ---------- write (used by the admin dashboard) ---------- */
    public function updateInfo(array $info): void
    {
        foreach (['name', 'fullName', 'location', 'founded', 'about', 'mission', 'vision'] as $key) {
            if (array_key_exists($key, $info)) {
                $this->data[$key] = (string) $info[$key];
            }
        }
    }

    public function updateContact(array $contact): void
    {
        foreach (['email', 'phone', 'address', 'facebook'] as $key) {
            if (array_key_exists($key, $contact)) {
                $this->data['contact'][$key] = (string) $contact[$key];
            }
        }
    }

    public function setObjectives(array $objectives): void { $this->data['objectives'] = array_values($objectives); }
    public function setPeople(array $people): void         { $this->data['people'] = array_values($people); }
    public function setActivities(array $activities): void { $this->data['activities'] = array_values($activities); }
}
