<?php

declare(strict_types=1);

// phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.NotCamelCaps

/**
 * Get the word gender.
 *
 * @param string $key The translation key.
 *
 * @return string The gender ('m' or 'f').
 */
function trans_gender(string $key): string
{
    $gender = trans('genders.' . $key);

    return !in_array($gender, ['m', 'f']) ? 'm' : $gender;
}

/**
 * @param string $key    The translation key.
 * @param int    $count  The translation count (plural).
 * @param bool   $append If the word should be appended.
 *
 * @return string The translated value, prefixed with its defined article.
 */
function trans_def_article(string $key, int $count = 1, bool $append = true): string
{
    $word = trans_choice($key, $count);

    if ($count <= 1) {
        $gender = trans_gender($key);

        if (in_array(strtolower($word[0]), ['a', 'e', 'i', 'o', 'u', 'y', 'h'])) {
            $article = "l'";
        } elseif ($gender === 'm') {
            $article = 'le';
        } else {
            $article = 'la';
        }
    } else {
        $article = 'les';
    }

    return $article . ($append ? ($article !== "l'" ? ' ' : '') . $word : '');
}

/**
 * @param string $key    The translation key.
 * @param int    $count  The translation count (plural).
 * @param bool   $append If the word should be appended.
 *
 * @return string The translated value, prefixed with its undefined article.
 */
function trans_undef_article(string $key, int $count = 1, bool $append = true): string
{
    $word   = trans_choice($key, $count);
    $gender = trans_gender($key);

    if ($count <= 1) {
        if ($gender === 'm') {
            $article = 'un';
        } else {
            $article = 'une';
        }
    } else {
        $article = 'des';
    }

    return $article . ($append ? " $word" : '');
}

/**
 * @param string $key   The translation key.
 * @param int    $count The translation count (plural).
 *
 * @return string The translated pronoun.
 */
function trans_pronoun(string $key, int $count = 1): string
{
    $gender = trans_gender($key);

    if ($count <= 1) {
        $article = $gender === 'm' ? 'il' : 'elle';
    } else {
        $article = $gender === 'm' ? 'ils' : 'elles';
    }

    return $article;
}
