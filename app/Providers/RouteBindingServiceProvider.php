<?php

namespace App\Providers;

use App\Models\Country;
use mmghv\LumenRouteBinding\RouteBindingServiceProvider as BaseServiceProvider;

/**
 * Class RouteBindingServiceProvider
 * Provide advanced explicit route bindings
 *
 * @author Mathrix Team
 * @package App\Providers
 */
class RouteBindingServiceProvider extends BaseServiceProvider
{
    /**
     * Boot the service provider
     */
    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | Resolvers
        |--------------------------------------------------------------------------
        |
        | Closures made to resolve bindings
        |
        */
        
        /**
         * /{country}/{class} resolver
         *
         * @param string $countrySlugShort
         * @param string $classSlugShort
         *
         * @return array[Country, Class_]
         */
        $countryClass = function (string $countrySlugShort, string $classSlugShort) {
            $country = Country::whereSlugShort($countrySlugShort)->firstOrFail();
            $class = $country->classes()->whereSlugShort($classSlugShort)->firstOrFail();

            return [$country, $class];
        };

        /**
         * /{country}/{class}/{discipline} resolver
         *
         * @param string $countrySlugShort
         * @param string $classSlugShort
         * @param string $disciplineSlug
         *
         * @return array
         */
        $countryClassDiscipline = function (
            string $countrySlugShort,
            string $classSlugShort,
            string $disciplineSlug
        ) use ($countryClass) {
            [$country, $class] = $countryClass($countrySlugShort, $classSlugShort);
            $discipline = $class->disciplines()->whereSlug($disciplineSlug)->firstOrFail();

            return [$country, $class, $discipline];
        };

        /**
         * /{country}/{class}/{discipline}/{chapterSlug}-{chapter} resolver
         *
         * @param string $countrySlugShort
         * @param string $classSlugShort
         * @param string $disciplineSlug
         * @param string $chapterSlug
         * @param string $chapterId
         *
         * @return array
         */
        $countryClassDisciplineChapter = function (
            string $countrySlugShort,
            string $classSlugShort,
            string $disciplineSlug,
            string $chapterSlug,
            string $chapterId
        ) use ($countryClassDiscipline) {
            [$country, $class, $discipline] = $countryClassDiscipline($countrySlugShort, $classSlugShort,
                $disciplineSlug);

            $chapter = $discipline
                ->chapters()
                ->whereClassId($class->id)
                ->whereId($chapterId)
                ->firstOrFail();

            return [$country, $class, $discipline, $chapterSlug, $chapter];
        };

        /**
         * /{country}/{class}/{discipline}/{chapterSlug}-{chapter}/{notion-slug}-{notion} resolver
         *
         * @param string $countrySlugShort
         * @param string $classSlugShort
         * @param string $disciplineSlug
         * @param string $chapterSlug
         * @param string $chapterId
         * @param string $notionSlug
         * @param string $notionId
         *
         * @return array
         */
        $countryClassDisciplineChapterNotion = function (
            string $countrySlugShort,
            string $classSlugShort,
            string $disciplineSlug,
            string $chapterSlug,
            string $chapterId,
            string $notionSlug,
            string $notionId
        ) use ($countryClassDisciplineChapter) {
            [$country, $class, $discipline, $chapterSlug, $chapter] = $countryClassDisciplineChapter(
                $countrySlugShort,
                $classSlugShort,
                $disciplineSlug,
                $chapterSlug,
                $chapterId
            );

            $notion = $chapter
                ->notions()
                ->whereId($notionId)
                ->firstOrFail();

            return [$country, $class, $discipline, $chapterSlug, $chapter, $notionSlug, $notion];
        };

        /*
        |--------------------------------------------------------------------------
        | Bindings
        |--------------------------------------------------------------------------
        */

        // /{country} route
        $this->binder->bind('country', function ($value) {
            return Country::whereSlugShort($value)->firstOrFail();
        });

        // /{country}/{class} route
        $this->binder->compositeBind(['country', 'class'], $countryClass);

        // /{country}/{class}/{discipline} route
        $this->binder->compositeBind(['country', 'class', 'discipline'], $countryClassDiscipline);

        // /{country}/{class}/{discipline}/{chapterSlug}-{chapter} route
        $this->binder->compositeBind(
            ['country', 'class', 'discipline', 'chapterSlug', 'chapter'],
            $countryClassDisciplineChapter
        );

        // /{country}/{class}/{discipline}/{chapterSlug}-{chapter}/{notionSlug}-{notion} route
        $this->binder->compositeBind(
            ['country', 'class', 'discipline', 'chapterSlug', 'chapter', 'notionSlug', 'notion'],
            $countryClassDisciplineChapterNotion
        );
    }
}