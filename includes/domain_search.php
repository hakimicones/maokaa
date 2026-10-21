<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/env.php';

function domain_search_json(bool $success, $data = null, $error = null): void {
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'error'   => $error,
    ]);
    exit;
}

function domain_search_clean(string $domain): string {
    $domain = preg_replace('#^https?://#i', '', $domain);
    $domain = preg_replace('#^www\.#i', '', $domain);
    $domain = preg_replace('#/.*$#', '', trim($domain));
    $domain = strtolower(trim($domain));
    return $domain;
}

function domain_search_get_whois_server(string $domain): string {
    $tld = strtolower((string) substr(strrchr($domain, '.'), 1));
    $servers = [
        'com' => 'whois.verisign-grs.com',
        'net' => 'whois.verisign-grs.com',
        'org' => 'whois.pir.org',
        'fr' => 'whois.afnic.fr',
        'io' => 'whois.nic.io',
        'co' => 'whois.nic.co',
        'uk' => 'whois.nic.uk',
        'de' => 'whois.denic.de',
        'dz' => 'whois.nic.dz',
        'xyz' => 'whois.nic.xyz',
    ];

    return $servers[$tld] ?? 'whois.iana.org';
}

function domain_search_resolve_nameserver(string $nameserver): string {
    $nameserver = trim($nameserver);
    $host = strtolower(rtrim($nameserver, '.'));

    if (!preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/i', $host)) {
        return $nameserver;
    }

    $records = @dns_get_record($host, DNS_A | DNS_AAAA);
    if (!is_array($records)) {
        return $host;
    }

    $addresses = [];
    foreach ($records as $record) {
        if (!empty($record['ip'])) {
            $addresses[] = $record['ip'];
        } elseif (!empty($record['ipv6'])) {
            $addresses[] = $record['ipv6'];
        }
    }

    return $addresses ? $host . ' ' . implode(' ', array_unique($addresses)) : $host;
}

function domain_search_lookup_nameservers(string $domain): array {
    $records = @dns_get_record($domain, DNS_NS);
    if (!is_array($records)) {
        return [];
    }

    $nameservers = [];
    foreach ($records as $record) {
        if (!empty($record['target'])) {
            $nameservers[] = domain_search_resolve_nameserver((string) $record['target']);
        }
    }

    return array_values(array_unique($nameservers));
}

function domain_search_format_date($value): ?string {
    if (!is_scalar($value) || trim((string) $value) === '') {
        return null;
    }

    $timestamp = strtotime((string) $value);
    return $timestamp === false ? null : date('Y-m-d', $timestamp);
}

function domain_search_format_whoisxml_response(array $data): array {
    $whoisRecord = $data['WhoisRecord'] ?? [];
    $registryData = $whoisRecord['registryData'] ?? [];
    $rawText = $registryData['rawText'] ?? ($whoisRecord['rawText'] ?? '');
    $dataError = $whoisRecord['dataError'] ?? ($registryData['dataError'] ?? '');

    $status = 'unknown';
    $rawLower = strtolower((string) $rawText);

    if (strpos($rawLower, 'domain not found') !== false || strpos($rawLower, 'no match') !== false || $dataError === 'MISSING_WHOIS_DATA') {
        $status = 'available';
    } elseif (!empty($registryData['createdDate']) || !empty($whoisRecord['createdDate']) || !empty($registryData['expiresDate']) || !empty($whoisRecord['expiresDate']) || (!empty($registryData['registrarName']) && $registryData['registrarName'] !== 'not found') || (!empty($whoisRecord['registrarName']) && $whoisRecord['registrarName'] !== 'not found')) {
        $status = 'active';
    } elseif (isset($whoisRecord['dataError'])) {
        $status = 'available';
    }

    $registrarName = $registryData['registrarName'] ?? ($whoisRecord['registrarName'] ?? '');
    $createdDate = $registryData['createdDate'] ?? ($whoisRecord['createdDate'] ?? null);
    $expiresDate = $registryData['expiresDate'] ?? ($whoisRecord['expiresDate'] ?? null);

    $nameServers = [];
    if (isset($registryData['nameServers']['hostNames'])) {
        $nameServers = $registryData['nameServers']['hostNames'];
    } elseif (isset($whoisRecord['nameServers']['hostNames'])) {
        $nameServers = $whoisRecord['nameServers']['hostNames'];
    }
    if (is_string($nameServers)) {
        $nameServers = [$nameServers];
    }
    $nameservers = array_values(array_filter(array_map(
        static fn($nameserver): string => domain_search_resolve_nameserver((string) $nameserver),
        is_array($nameServers) ? $nameServers : []
    )));
    if ($status === 'unknown' && $nameservers) {
        $status = 'active';
    }

    return [
        'status' => $status,
        'domain' => $whoisRecord['domainName'] ?? $data['domainName'] ?? '',
        'registrar' => $registrarName,
        'creation_date' => domain_search_format_date($createdDate),
        'expiration_date' => domain_search_format_date($expiresDate),
        'nameservers' => $nameservers,
        'raw_data' => $data,
    ];
}

function domain_search_parse_socket_response(string $domain, string $text): array {
    $registrar = '';
    if (preg_match('/registrar\s*[:=]\s*(.+)/i', $text, $matches)) {
        $registrar = trim($matches[1]);
    }

    $creationDate = null;
    if (preg_match('/creation date\s*[:=]\s*(.+)/i', $text, $matches)) {
        $creationDate = domain_search_format_date(trim($matches[1]));
    }

    $expirationDate = null;
    if (preg_match('/expir(?:y|ation) date\s*[:=]\s*(.+)/i', $text, $matches)) {
        $expirationDate = domain_search_format_date(trim($matches[1]));
    }

    $nameservers = [];
    if (preg_match_all('/(?:name server|nserver)\s*[:=]\s*(.+)/i', $text, $matches)) {
        foreach ($matches[1] as $match) {
            $value = trim($match);
            if ($value !== '') {
                $nameservers[] = domain_search_resolve_nameserver($value);
            }
        }
    }
    $hasRegistrationData = ($registrar !== '' && !preg_match('/^(?:not found|none|n\/a)$/i', $registrar))
        || $creationDate !== null
        || $expirationDate !== null
        || $nameservers;
    $status = $hasRegistrationData
        ? 'active'
        : (preg_match('/no match|not found|no entries|available/i', $text) ? 'available' : 'unknown');

    return [
        'status' => $status,
        'domain' => $domain,
        'registrar' => $registrar,
        'creation_date' => $creationDate,
        'expiration_date' => $expirationDate,
        'nameservers' => $nameservers,
        'raw_data' => $text,
    ];
}

function domain_search_query_provider(string $domain): array {
    $provider = getenv('WHOIS_API_PROVIDER') ?: 'socket';
    $apiKey = (string) (getenv('WHOIS_API_KEY') ?: '');
    $endpoint = rtrim((string) (getenv('WHOIS_API_ENDPOINT') ?: 'https://www.whoisxmlapi.com/whoisserver/WhoisService'), '/');
    $timeout = (int) (getenv('WHOIS_API_TIMEOUT') ?: 10);

    if ($provider === 'whoisxmlapi' && $apiKey !== '') {
        $url = $endpoint . '?' . http_build_query([
            'apiKey' => $apiKey,
            'domainName' => $domain,
            'outputFormat' => 'JSON',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($response === false || curl_errno($ch) !== 0) {
            curl_close($ch);
            throw new Exception('Erreur API WHOIS : ' . $curlError);
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Erreur API WHOIS : HTTP ' . $httpCode);
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data)) {
            throw new Exception('Réponse API WHOIS invalide');
        }

        return domain_search_format_whoisxml_response($data);
    }

    $server = domain_search_get_whois_server($domain);
    $fp = @fsockopen($server, 43, $errno, $errstr, $timeout);
    if ($fp === false) {
        throw new Exception('Impossible de contacter le serveur WHOIS : ' . $errstr);
    }

    fwrite($fp, $domain . "\r\n");
    $raw = '';
    while (!feof($fp)) {
        $raw .= fgets($fp, 4096);
    }
    fclose($fp);

    return domain_search_parse_socket_response($domain, $raw);
}

try {
    $domain = $_POST['domain'] ?? '';
    if (!is_string($domain) || trim($domain) === '') {
        throw new Exception('Domaine requis');
    }

    $domain = domain_search_clean($domain);
    if (!preg_match('/^([a-z0-9]+([\-\.]?[a-z0-9]+)*\.[a-z]{2,63})$/i', $domain)) {
        throw new Exception('Format de domaine invalide');
    }

    try {
        $result = domain_search_query_provider($domain);
    } catch (Throwable $e) {
        $nameservers = domain_search_lookup_nameservers($domain);
        if (!$nameservers) {
            throw $e;
        }
        $result = [
            'status' => 'active',
            'registrar' => '',
            'creation_date' => null,
            'expiration_date' => null,
            'nameservers' => $nameservers,
        ];
    }

    $nameservers = $result['nameservers'] ?? [];
    if (!is_array($nameservers) || !$nameservers) {
        $nameservers = domain_search_lookup_nameservers($domain);
    } else {
        $nameservers = array_values(array_filter(array_map(
            static fn($nameserver): string => domain_search_resolve_nameserver((string) $nameserver),
            $nameservers
        )));
    }
    $result['nameservers'] = $nameservers;
    if (($result['status'] ?? 'unknown') === 'unknown' && $nameservers) {
        $result['status'] = 'active';
    }

    $status = strtolower((string) ($result['status'] ?? 'unknown'));

    domain_search_json(true, [
        'status' => $status,
        'domain' => $domain,
        'registrar' => $result['registrar'] ?? '',
        'creation_date' => $result['creation_date'] ?? null,
        'expiration_date' => $result['expiration_date'] ?? null,
        'nameservers' => $result['nameservers'] ?? [],
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    domain_search_json(false, null, $e->getMessage());
}
