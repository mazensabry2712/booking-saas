<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalizationTest extends TestCase
{
    /**
     * Test English translations file exists
     */
    public function test_english_translations_exist(): void
    {
        $this->assertFileExists(resource_path('lang/en/messages.php'));
    }

    /**
     * Test Arabic translations file exists
     */
    public function test_arabic_translations_exist(): void
    {
        $this->assertFileExists(resource_path('lang/ar/messages.php'));
    }

    /**
     * Test English translations return array
     */
    public function test_english_translations_return_array(): void
    {
        $translations = include resource_path('lang/en/messages.php');
        $this->assertIsArray($translations);
    }

    /**
     * Test Arabic translations return array
     */
    public function test_arabic_translations_return_array(): void
    {
        $translations = include resource_path('lang/ar/messages.php');
        $this->assertIsArray($translations);
    }

    /**
     * Test locale can be changed
     */
    public function test_locale_can_be_changed(): void
    {
        app()->setLocale('en');
        $this->assertEquals('en', app()->getLocale());

        app()->setLocale('ar');
        $this->assertEquals('ar', app()->getLocale());
    }

    /**
     * Test supported locales
     */
    public function test_supported_locales(): void
    {
        $supportedLocales = ['en', 'ar'];

        foreach ($supportedLocales as $locale) {
            $this->assertDirectoryExists(resource_path("lang/{$locale}"));
        }
    }
}
