<?php

namespace App\Services;

use App\Exceptions\WebsiteVerificationException;
use App\Models\Website;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class WebsiteOwnershipVerifier
{
    private const MAX_HTML_BYTES = 1048576;

    private const MAX_REDIRECTS = 3;

    public function verify(Website $website): void
    {
        if (! preg_match('/^[a-f0-9]{64}$/', (string) $website->verification_token)) {
            throw new WebsiteVerificationException('This listing has no valid verification token. Please contact support.');
        }

        $domain = strtolower(rtrim($website->domain, '.'));

        if (! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $domain)) {
            throw new WebsiteVerificationException('The listing domain is invalid. Update the website domain and try again.');
        }

        $html = $this->fetchHomepage('https://'.$domain.'/');

        if (trim($html) === '') {
            throw new WebsiteVerificationException('The verification meta tag was not found on the homepage. Add it inside the <head> section and publish the change.');
        }

        $document = new \DOMDocument;
        $previousLibxmlSetting = libxml_use_internal_errors(true);

        try {
            $document->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
        }

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query('//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "yourmarketplace-verification"]');

        if ($nodes === false || $nodes->length === 0) {
            throw new WebsiteVerificationException('The verification meta tag was not found on the homepage. Add it inside the <head> section and publish the change.');
        }

        foreach ($nodes as $node) {
            if (hash_equals($website->verification_token, $node->getAttribute('content'))) {
                return;
            }
        }

        throw new WebsiteVerificationException('The verification meta tag is present, but its token does not match this listing.');
    }

    protected function resolvePublicAddresses(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (! $this->isPublicIp($host)) {
                throw new WebsiteVerificationException('Verification was blocked because the website resolves to a non-public network address.');
            }

            return [[
                'ip' => $host,
                'family' => str_contains($host, ':') ? CURL_IPRESOLVE_V6 : CURL_IPRESOLVE_V4,
            ]];
        }

        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        $addresses = [];

        foreach ($records ?: [] as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;

            if ($ip === null) {
                continue;
            }

            if (! $this->isPublicIp($ip)) {
                throw new WebsiteVerificationException('Verification was blocked because the website resolves to a non-public network address.');
            }

            $addresses[] = [
                'ip' => $ip,
                'family' => str_contains($ip, ':') ? CURL_IPRESOLVE_V6 : CURL_IPRESOLVE_V4,
            ];
        }

        if ($addresses === []) {
            throw new WebsiteVerificationException('The website domain did not resolve to a public IP address.');
        }

        return $addresses;
    }

    protected function isPublicIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }

        if (str_contains($ip, ':') && ! $this->ipIsInRange($ip, '2000::/3')) {
            return false;
        }

        $ranges = str_contains($ip, ':')
            ? [
                '::/96',
                '::ffff:0:0/96',
                '64:ff9b::/96',
                '64:ff9b:1::/48',
                '100::/64',
                '2001::/23',
                '2001:db8::/32',
                '2002::/16',
                '3fff::/20',
                'fec0::/10',
                'ff00::/8',
            ]
            : [
                '0.0.0.0/8',
                '10.0.0.0/8',
                '100.64.0.0/10',
                '127.0.0.0/8',
                '169.254.0.0/16',
                '172.16.0.0/12',
                '192.0.0.0/24',
                '192.0.2.0/24',
                '192.88.99.0/24',
                '192.168.0.0/16',
                '198.18.0.0/15',
                '198.51.100.0/24',
                '203.0.113.0/24',
                '224.0.0.0/4',
                '240.0.0.0/4',
            ];

        foreach ($ranges as $range) {
            if ($this->ipIsInRange($ip, $range)) {
                return false;
            }
        }

        return true;
    }

    private function ipIsInRange(string $ip, string $range): bool
    {
        [$network, $prefix] = explode('/', $range);
        $addressBytes = inet_pton($ip);
        $networkBytes = inet_pton($network);
        $prefix = (int) $prefix;
        $wholeBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;

        return substr($addressBytes, 0, $wholeBytes) === substr($networkBytes, 0, $wholeBytes)
            && ($remainingBits === 0 || (ord($addressBytes[$wholeBytes]) & (0xFF << (8 - $remainingBits))) === (ord($networkBytes[$wholeBytes]) & (0xFF << (8 - $remainingBits))));
    }

    private function fetchHomepage(string $url): string
    {
        for ($redirects = 0; $redirects <= self::MAX_REDIRECTS; $redirects++) {
            $uri = new Uri($url);
            $scheme = strtolower($uri->getScheme());
            $host = strtolower(rtrim($uri->getHost(), '.'));
            $port = $uri->getPort() ?? ($scheme === 'https' ? 443 : 80);

            if (
                ! in_array($scheme, ['http', 'https'], true)
                || $host === ''
                || $uri->getUserInfo() !== ''
                || $uri->getFragment() !== ''
                || $port !== ($scheme === 'https' ? 443 : 80)
            ) {
                throw new WebsiteVerificationException('The homepage URL or redirect uses an unsupported or unsafe address.');
            }

            $url = (string) $uri->withScheme($scheme)->withHost($host);
            $addresses = $this->resolvePublicAddresses($host);
            $address = $addresses[0];
            $resolveAddress = str_contains($address['ip'], ':')
                ? '['.$address['ip'].']'
                : $address['ip'];

            try {
                $response = Http::accept('text/html')
                    ->timeout(5)
                    ->connectTimeout(3)
                    ->withOptions([
                        'allow_redirects' => false,
                        'proxy' => '',
                        'stream' => true,
                        'on_headers' => function (ResponseInterface $response): void {
                            $contentLength = $response->getHeaderLine('Content-Length');

                            if ($contentLength !== '' && (int) $contentLength > self::MAX_HTML_BYTES) {
                                throw new WebsiteVerificationException('The homepage HTML is too large to verify (maximum 1 MB).');
                            }
                        },
                        'curl' => [
                            CURLOPT_RESOLVE => [$host.':'.$port.':'.$resolveAddress],
                            CURLOPT_IPRESOLVE => $address['family'],
                        ],
                    ])
                    ->get($url);
            } catch (WebsiteVerificationException $exception) {
                throw $exception;
            } catch (ConnectionException $exception) {
                if ($scheme === 'https' && $redirects === 0) {
                    $url = 'http://'.$host.'/';

                    continue;
                }

                throw new WebsiteVerificationException('The homepage could not be reached before the connection timed out.', previous: $exception);
            } catch (GuzzleException $exception) {
                throw new WebsiteVerificationException('The homepage could not be fetched securely. Check that it is publicly accessible.', previous: $exception);
            }

            if (in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                if ($redirects === self::MAX_REDIRECTS) {
                    throw new WebsiteVerificationException('The homepage exceeded the allowed number of redirects.');
                }

                $location = $response->header('Location');

                if (blank($location)) {
                    throw new WebsiteVerificationException('The homepage returned a redirect without a destination.');
                }

                $url = (string) UriResolver::resolve(new Uri($url), new Uri($location));

                continue;
            }

            if (! $response->successful()) {
                throw new WebsiteVerificationException('The homepage returned HTTP '.$response->status().'. Make sure it is publicly accessible and try again.');
            }

            $contentLength = $response->header('Content-Length');

            if ($contentLength !== null && (int) $contentLength > self::MAX_HTML_BYTES) {
                throw new WebsiteVerificationException('The homepage HTML is too large to verify (maximum 1 MB).');
            }

            $stream = $response->toPsrResponse()->getBody();
            $html = '';

            while (! $stream->eof()) {
                $html .= $stream->read(8192);

                if (strlen($html) > self::MAX_HTML_BYTES) {
                    throw new WebsiteVerificationException('The homepage HTML is too large to verify (maximum 1 MB).');
                }
            }

            return $html;
        }

        throw new WebsiteVerificationException('The homepage could not be verified.');
    }
}
