<?php

namespace App\Support;

final class BackLink
{
    public static function resolve(
        array $crumbs,
        ?string $active,
        ?string $explicit,
        string $currentUrl,
        ?string $referer,
        string $host,
        array $sections,
    ): ?array {
        if ($explicit !== null) {
            $target = self::safeUrl($explicit, $host);

            return $target === null || self::samePage($target, $currentUrl)
                ? null
                : ['url' => $target, 'label' => 'Back'];
        }

        foreach (array_reverse($crumbs) as $crumb) {
            if (!is_array($crumb) || !isset($crumb['url']) || !is_string($crumb['url'])) {
                continue;
            }

            $target = self::safeUrl($crumb['url'], $host);

            if ($target === null) {
                continue;
            }

            if (self::samePage($target, $currentUrl)) {
                return null;
            }

            $label = isset($crumb['label']) && is_string($crumb['label'])
                ? $crumb['label']
                : 'Back';

            if ($referer !== null) {
                $referrerUrl = self::safeUrl($referer, $host);

                if ($referrerUrl !== null && self::samePath($referrerUrl, $target)) {
                    $target = $referrerUrl;
                }
            }

            return ['url' => $target, 'label' => $label];
        }

        foreach ($sections as $section) {
            if (!is_array($section) || ($section['key'] ?? null) !== $active || !isset($section['url']) || !is_string($section['url'])) {
                continue;
            }

            $target = self::safeUrl($section['url'], $host);

            if ($target === null || self::samePage($target, $currentUrl)) {
                return null;
            }

            return [
                'url' => $target,
                'label' => isset($section['label']) && is_string($section['label'])
                    ? $section['label']
                    : 'Back',
            ];
        }

        return null;
    }

    private static function safeUrl(string $url, string $host): ?string
    {
        if ($url === '' || preg_match('/[\x00-\x20\x7F\\\\]/', $url) === 1) {
            return null;
        }

        $parts = parse_url($url);

        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        if (isset($parts['scheme']) || isset($parts['host'])) {
            if (
                !isset($parts['scheme'], $parts['host'])
                || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
                || strcasecmp($parts['host'], $host) !== 0
            ) {
                return null;
            }
        } elseif (!str_starts_with($url, '/') || str_starts_with($url, '//')) {
            return null;
        }

        return $url;
    }

    private static function samePage(string $left, string $right): bool
    {
        return self::samePath($left, $right);
    }

    private static function samePath(string $left, string $right): bool
    {
        $leftParts = parse_url($left);
        $rightParts = parse_url($right);

        if ($leftParts === false || $rightParts === false) {
            return false;
        }

        if (isset($leftParts['host'], $rightParts['host']) && strcasecmp($leftParts['host'], $rightParts['host']) !== 0) {
            return false;
        }

        $leftPath = rtrim($leftParts['path'] ?? '/', '/') ?: '/';
        $rightPath = rtrim($rightParts['path'] ?? '/', '/') ?: '/';

        return $leftPath === $rightPath;
    }
}