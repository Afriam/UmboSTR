<?php
declare(strict_types=1);

/**
 * MODEL (Register)
 * Validates the registration and sends it to the UMBO Google Form.
 * The Google "entry" IDs are discovered automatically from the live form,
 * so nothing has to be copied by hand when the form is edited.
 */
class RegisterModel
{
    private const FORM_ID   = '1FAIpQLScK9UHrCs5_wDNwIT851MHla2-Sh3JHSO9rRRXSy1d-15y20Q';
    private const CACHE_TTL = 86400; // 1 day

    /**
     * Only for hosting WITHOUT PHP (e.g. GitHub Pages): paste the Google entry IDs here,
     * for example ['name' => '123456789', 'birthday' => '234567890', ...]. Leave empty on a PHP host.
     */
    public const STATIC_ENTRY_IDS = [];

    /** Our form fields (same order as the Google Form). 'match' = text found in the Google question title. */
    public const FIELDS = [
        'name'     => ['label' => 'Full name (Last name, First name, Middle name)', 'match' => 'name',           'required' => true,  'type' => 'text',     'max' => 150, 'autocomplete' => 'name'],
        'birthday' => ['label' => 'Birthday',                                       'match' => 'birthday',       'required' => true,  'type' => 'date',     'max' => 10,  'autocomplete' => 'bday'],
        'address'  => ['label' => 'Residential address',                            'match' => 'residential',    'required' => true,  'type' => 'text',     'max' => 250, 'autocomplete' => 'street-address'],
        'barangay' => ['label' => 'Barangay in Mangali, Tanudan',                   'match' => 'barangay',       'required' => true,  'type' => 'text',     'max' => 100, 'autocomplete' => 'off'],
        'purpose'  => ['label' => 'Purpose of joining the organization',            'match' => 'purpose',        'required' => true,  'type' => 'textarea', 'max' => 500, 'autocomplete' => 'off'],
        'contact'  => ['label' => 'Contact number',                                 'match' => 'contact number', 'required' => true,  'type' => 'tel',      'max' => 20,  'autocomplete' => 'tel'],
        'email'    => ['label' => 'Email address',                                  'match' => 'email',          'required' => false, 'type' => 'email',    'max' => 150, 'autocomplete' => 'email'],
    ];

    public function viewUrl(): string
    {
        return 'https://docs.google.com/forms/d/e/' . self::FORM_ID . '/viewform';
    }

    public function actionUrl(): string
    {
        return 'https://docs.google.com/forms/d/e/' . self::FORM_ID . '/formResponse';
    }

    /** @return array{0: array<string,string>, 1: array<string,string>} [clean values, errors] */
    public function validate(array $input): array
    {
        $clean = [];
        $errors = [];
        foreach (self::FIELDS as $key => $def) {
            $value = Security::clean($input[$key] ?? '', 1000);
            if ($value === '' && $def['required']) {
                $errors[$key] = 'This field is required.';
            } elseif (mb_strlen($value) > $def['max']) {
                $errors[$key] = 'Please keep this under ' . $def['max'] . ' characters.';
            }
            $clean[$key] = $value;
        }

        if (!isset($errors['birthday'])) {
            $date = DateTime::createFromFormat('Y-m-d', $clean['birthday']);
            $valid = $date !== false && $date->format('Y-m-d') === $clean['birthday'];
            if (!$valid || $date > new DateTime('today') || (int) $date->format('Y') < 1900) {
                $errors['birthday'] = 'Enter a valid birthday.';
            }
        }
        if (!isset($errors['contact']) && !preg_match('/^[0-9+()\-\s]{7,20}$/', $clean['contact'])) {
            $errors['contact'] = 'Enter a valid contact number.';
        }
        if ($clean['email'] !== '' && !isset($errors['email']) && !filter_var($clean['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        return [$clean, $errors];
    }

    /** @return array{0: bool, 1: string} [success, message] */
    public function submit(array $clean): array
    {
        try {
            $map = $this->getEntryMap();
            [$status, $body] = $this->http('POST', $this->actionUrl(), $this->buildPayload($clean, $map));
        } catch (Throwable $e) {
            @unlink($this->cacheFile());
            return [false, 'We could not reach the registration form. Please try again in a moment.'];
        }

        if (($status === 200 || $status === 302) && stripos($body, 'ErrorMessage') === false) {
            return [true, ''];
        }
        @unlink($this->cacheFile()); // the form may have changed; look it up again next time
        return [false, 'The registration was not accepted. Please check your answers and try again.'];
    }

    /** Google expects entry.<id> for text answers and entry.<id>_year/_month/_day for dates. */
    private function buildPayload(array $clean, array $map): array
    {
        $post = ['fvv' => '1', 'pageHistory' => '0'];
        foreach ($map as $key => $entryId) {
            if ($key === 'birthday') {
                [$year, $month, $day] = explode('-', $clean['birthday']);
                $post["entry.{$entryId}_year"]  = $year;
                $post["entry.{$entryId}_month"] = (string) (int) $month;
                $post["entry.{$entryId}_day"]   = (string) (int) $day;
            } else {
                $post["entry.{$entryId}"] = $clean[$key] ?? '';
            }
        }
        return $post;
    }

    /** @return array<string,string> field key => Google entry ID */
    public function getEntryMap(): array
    {
        if (self::STATIC_ENTRY_IDS !== []) {
            return self::STATIC_ENTRY_IDS;
        }
        $cache = $this->cacheFile();
        if (is_file($cache) && time() - filemtime($cache) < self::CACHE_TTL) {
            $cached = json_decode((string) file_get_contents($cache), true);
            if (is_array($cached) && $cached !== []) {
                return $cached;
            }
        }
        $map = $this->discoverEntryMap();
        @file_put_contents($cache, json_encode($map), LOCK_EX);
        return $map;
    }

    private function discoverEntryMap(): array
    {
        [$status, $body] = $this->http('GET', $this->viewUrl());
        if ($status !== 200 || !preg_match('/FB_PUBLIC_LOAD_DATA_\s*=\s*(.*?);\s*<\/script>/s', $body, $m)) {
            throw new RuntimeException('Could not read the Google Form.');
        }
        $data = json_decode($m[1], true);
        $questions = is_array($data) ? ($data[1][1] ?? []) : [];

        $found = [];
        foreach ($questions as $q) {
            $entryId = $q[4][0][0] ?? null;
            if ($entryId !== null) {
                $found[] = ['title' => (string) ($q[1] ?? ''), 'id' => (string) $entryId];
            }
        }

        $map = [];
        $used = [];
        foreach (self::FIELDS as $key => $def) {
            foreach ($found as $question) {
                if (!in_array($question['id'], $used, true) && stripos($question['title'], $def['match']) !== false) {
                    $map[$key] = $question['id'];
                    $used[] = $question['id'];
                    break;
                }
            }
            if (!isset($map[$key]) && $def['required']) {
                throw new RuntimeException("Google Form question not found for: {$key}");
            }
        }
        return $map;
    }

    private function cacheFile(): string
    {
        return __DIR__ . '/../data/google_form_map.json';
    }

    /** @return array{0:int,1:string} [HTTP status, body] */
    private function http(string $method, string $url, ?array $post = null): array
    {
        $agent = 'Mozilla/5.0 (compatible; UMBO-registration)';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_ENCODING       => '',
                CURLOPT_USERAGENT      => $agent,
                CURLOPT_HTTPHEADER     => ['Accept-Language: en'],
            ]);
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post ?? []));
            }
            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($body === false) {
                throw new RuntimeException('Network error');
            }
            return [$status, (string) $body];
        }

        $context = stream_context_create(['http' => [
            'method'        => $method,
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\nUser-Agent: {$agent}\r\nAccept-Language: en\r\n",
            'content'       => $method === 'POST' ? http_build_query($post ?? []) : '',
            'timeout'       => 20,
            'ignore_errors' => true,
        ]]);
        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            throw new RuntimeException('Network error');
        }
        $status = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }
        return [$status, $body];
    }
}
