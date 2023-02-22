<?php

declare(strict_types=1);

namespace App\Config;

use Exception;

abstract class BaseConfiguration implements Configuration
{
    public function hasAll(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->has($key))
                return false;
        }
        return true;
    }

    private static function lowLevelMetaTag(string $key, string $value): string
    {
        return '<meta name="config_' . htmlspecialchars($key) . '" content="' . htmlspecialchars($value) . '" />';
    }

    public function getMetaTag(string $key, ?bool $optional = false): string
    {
        if ($optional && !$this->has($key))
            return "";
        else
            return self::lowLevelMetaTag($key, (string)$this->get($key));
    }

    public function getArray(string $key): array
    {
        $raw = (string)$this->get($key);
        $parsed = json_decode($raw);
        return is_array($parsed) ? $parsed : [$raw];
    }

    public function isDbEnabled(): bool
    {
        return $this->getBool("db_enable");
    }

    public function isDbEnabledMetaTag(): string
    {
        return self::lowLevelMetaTag("db_enable", json_encode($this->isDbEnabled()));
    }

    public function getDbDatabase(): string
    {
        return (string)$this->get("db_database");
    }
}
