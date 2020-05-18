<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Railgun\Models\User;

// phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.NotCamelCaps

if (!function_exists('hasScope')) { // @codeCoverageIgnore

    /**
     * Check if a user has the given scope.
     *
     * @param User|null $user  The user to check.
     * @param string    $scope The scope to check.
     *
     * @return bool If the given user has the given scope.
     */
    function hasScope(?User $user, string $scope): bool
    {
        return $user instanceof User
            && (
                in_array('*', $user->scopes, true)
                || in_array($scope, $user->scopes, true)
            );
    }
}

if (!function_exists('scope')) { // @codeCoverageIgnore

    /**
     * Check if the current logged user has the given scope.
     *
     * @param string $scope The scope to check
     *
     * @return bool If the current user has the given scope.
     */
    function scope(string $scope): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return hasScope($user, $scope);
    }
}

if (!function_exists('isPremium')) { // @codeCoverageIgnore

    /**
     * @return bool Check if the current user is premium.
     */
    function isPremium(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        return $user instanceof User && $user->premium;
    }
}

if (!function_exists('prefix')) { // @codeCoverageIgnore

    /**
     * Get the application prefix.
     *
     * @param string|null $path  An additional path to add.
     * @param bool        $colon If the prefix should be terminated by an underscore.
     *
     * @return string The generated prefix.
     */
    function prefix(?string $path = null, bool $colon = false): string
    {
        $prefix  = env('APP_NAME', 'app') . ':' . env('APP_RELEASE', 'stable') . '';
        $prefix .= $path ? ":$path" : '';
        $prefix .= $colon && $prefix[strlen($prefix) - 1] !== ':' ? ':' : '';

        return $prefix;
    }
}

if (!function_exists('is_serialized')) { // @codeCoverageIgnore

    /**
     * Extracted from Wordpress source.
     *
     * @link https://developer.wordpress.org/reference/functions/is_serialized/
     *
     * @param mixed $data   The data to check.
     * @param bool  $strict Enable strict mode.
     *
     * @return bool If the data was serialized using PHP version of {@see serialize()}.
     *
     * @codeCoverageIgnore
     */
    function is_serialized($data, bool $strict = true): bool
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);

        if ($data === 'N;') {
            return true;
        }

        if (strlen($data) < 4 || $data[1] !== ':') {
            return false;
        }

        if ($strict) {
            $lastc = substr($data, -1);
            if ($lastc !== ';' && $lastc !== '}') {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');
            // Either ; or } must exist.
            if ($semicolon === false && $brace === false) {
                return false;
            }
            // But neither must be in the first X characters.
            if ($semicolon !== false && $semicolon < 3) {
                return false;
            }
            if ($brace !== false && $brace < 4) {
                return false;
            }
        }

        $token = $data[0];

        switch ($token) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 's':
                if ($strict) {
                    if ($data[strlen($data) - 2] !== '"') {
                        return false;
                    }
                } elseif (strpos($data, '"') === false) {
                    return false;
                }
            // Or else fall through.
            case 'a':
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';

                return (bool)preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }

        return false;
    }
}

if (!function_exists('is_json')) {
    /**
     * Check if a string is a valid JSON.
     *
     * @param string $string The string to check.
     *
     * @return bool true if the string is a valid json, false otherwise.
     */
    function is_json(string $string): bool
    {
        try {
            json_decode($string, true, 512, JSON_THROW_ON_ERROR);

            return true;
        } catch (JsonException $exception) {
            return false;
        }
    }
}
