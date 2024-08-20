<?php

namespace Bytes\ImageBundle\Tests\Cache;

use Bytes\ImageBundle\Cache\ImageCache;
use Bytes\ResponseBundle\Enums\ContentType;
use Faker\Factory;
use Generator;
use PHPUnit\Framework\TestCase;

class ImageCacheTest extends TestCase
{
    public static function provideGetImageAsFromUrlCacheKey(): Generator
    {
        $faker = Factory::create();

        foreach (ContentType::cases() as $type) {
            yield $type->name => ['successPrefix' => $faker->word(), 'fallbackPrefix' => $faker->word(), 'url' => $faker->url(), 'contentType' => $type];
        }
    }

    /**
     * @dataProvider provideGetImageAsFromUrlCacheKey
     */
    public function testGetImageAsFromUrlCacheKey($successPrefix, $fallbackPrefix, $url, $contentType)
    {
        $imageCache = new ImageCache(successCachePrefix: $successPrefix, fallbackCachePrefix: $fallbackPrefix);
        $key = $imageCache->getImageAsFromUrlCacheKey($url, $contentType);
        self::assertStringStartsWith($successPrefix, $key);
        self::assertStringEndsWith('.contents', $key);

        self::assertStringStartsNotWith($fallbackPrefix, $key);

        self::assertStringContainsString('.getImageAsFromUrl.', $key);
        self::assertStringContainsString(urlencode($url), $key);
        self::assertStringContainsString(urlencode($contentType->value), $key);
    }

    public function testGetImageAsFromUrlFallbackCacheKey()
    {
        $faker = Factory::create();

        $successPrefix = $faker->word();
        $fallbackPrefix = $faker->word();
        $url = $faker->url();

        $imageCache = new ImageCache(successCachePrefix: $successPrefix, fallbackCachePrefix: $fallbackPrefix);
        $key = $imageCache->getImageAsFromUrlFallbackCacheKey($url);
        self::assertStringStartsWith($fallbackPrefix, $key);
        self::assertStringEndsWith('.contents', $key);

        self::assertStringStartsNotWith($successPrefix, $key);

        self::assertStringContainsString('.getImageAsFromUrl.', $key);
        self::assertStringContainsString(urlencode($url), $key);
    }
}
