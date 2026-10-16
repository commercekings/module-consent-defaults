<?php
declare(strict_types=1);

namespace CommerceKings\ConsentDefaults\Test\Unit;

use CommerceKings\Consent\Api\SourceConfigInterface;
use CommerceKings\Consent\Api\SourceInterface;
use CommerceKings\Consent\Model\CategoryMapper;
use CommerceKings\Consent\Model\PurposePool;
use CommerceKings\ConsentVanilla\Model\CookieFormat\V3;
use CommerceKings\ConsentVanilla\Model\Source as Vanilla;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class PresetTest extends TestCase
{
    /** @dataProvider installedPurposes */
    #[\PHPUnit\Framework\Attributes\DataProvider('installedPurposes')]
    public function testPresetOnlyShowsCategoriesForInstalledPurposes(array $purposes, array $expectedMap): void
    {
        foreach ($this->adapters() as $class => $mapKey) {
            $source = $this->source($class, $purposes);
            $categories = $source->getCategories();
            $this->assertSame(array_merge(['necessary'], array_values(array_unique($expectedMap))), array_keys($categories));
            $this->assertTrue($categories['necessary']['required']);
            $this->assertSame([], $categories['necessary']['purposes']);
            foreach (array_slice($categories, 1) as $category) {
                $this->assertFalse($category['required']);
                $this->assertNotEmpty($category['purposes']);
            }
            $this->assertSame($expectedMap, $source->getClientConfig()[$mapKey]);
            $this->assertNull($source->getChoice(), 'showing a category does not grant consent');
        }
    }

    public static function installedPurposes(): array
    {
        return [
            'no feature modules' => [[], []],
            'analytics only' => [['google_analytics'], ['google_analytics' => 'analytics']],
            'reminders only' => [['cart_reminder'], ['cart_reminder' => 'personalization']],
            'external content only' => [['external_content'], ['external_content' => 'external']],
            'marketing only' => [['google_ads'], ['google_ads' => 'marketing']],
            'two integrations share one category' => [
                ['google_analytics', 'another_analytics'],
                ['google_analytics' => 'analytics', 'another_analytics' => 'analytics'],
            ],
            'all features' => [
                ['google_analytics', 'external_content', 'cart_reminder', 'google_ads'],
                ['google_analytics' => 'analytics', 'external_content' => 'external', 'cart_reminder' => 'personalization', 'google_ads' => 'marketing'],
            ],
        ];
    }

    public function testExplicitSiteCategoriesReplaceThePreset(): void
    {
        foreach ($this->adapters() as $class => $mapKey) {
            $source = $this->source($class, ['analytics', 'cart_reminder'], [
                'site_emails' => ['label' => 'Site emails', 'purposes' => ['cart_reminder']],
            ]);
            $this->assertSame(['site_emails'], array_keys($source->getCategories()));
            $this->assertSame('Site emails', $source->getCategories()['site_emails']['label']);
            $this->assertSame(['cart_reminder' => 'site_emails'], $source->getClientConfig()[$mapKey]);
        }
    }

    public function testAcceptedPresetCategoriesOnlyGrantInstalledPurposes(): void
    {
        $time = gmdate('Y-m-d\TH:i:s.000\Z');
        $cookies = [
            'banner' => json_encode([
                'categories' => ['necessary', 'analytics', 'external', 'personalization', 'marketing'],
                'revision' => 1,
                'consentId' => 'fixture-id',
                'consentTimestamp' => $time,
                'lastConsentTimestamp' => $time,
                'expirationTime' => (time() + 86400) * 1000,
            ]),
        ];
        foreach ($this->adapters() as $class => $mapKey) {
            $this->assertSame(['google_analytics'], $this->source($class, ['google_analytics'], [], $cookies)->getChoice()->purposes);
            $this->assertSame([], $this->source($class, [], [], $cookies)->getChoice()->purposes);
        }
    }

    private function adapters(): array
    {
        $adapters = array_filter([Vanilla::class => 'categories'],
            fn ($class) => class_exists($class), ARRAY_FILTER_USE_KEY);
        if (!$adapters) {
            $this->markTestSkipped('Install a banner adapter to run preset integration tests.');
        }
        return $adapters;
    }

    private function source(string $class, array $purposes, array $categories = [], array $cookies = []): SourceInterface
    {
        // integrations name their preset category themselves (purpose "category")
        $categoryOf = ['google_analytics' => 'analytics', 'another_analytics' => 'analytics', 'cart_reminder' => 'personalization',
            'google_ads' => 'marketing'];
        $registry = new PurposePool(array_combine($purposes, array_map(
            fn ($purpose) => ['terms_revision' => '1'] + (isset($categoryOf[$purpose]) ? ['category' => $categoryOf[$purpose]] : []),
            $purposes
        )));
        $xml = simplexml_load_file(dirname(__DIR__, 2) . '/etc/di.xml');
        $defaults = $this->items($xml->xpath('//argument[@name="defaultCategories"]')[0]);
        $config = $this->createStub(SourceConfigInterface::class);
        $config->method('getCookieName')->willReturn('banner');
        $config->method('getRevision')->willReturn('1');
        $config->method('getCookieLifetimeDays')->willReturn(182);
        $manager = $this->createStub(CookieManagerInterface::class);
        $manager->method('getCookie')->willReturnCallback(fn ($name) => $cookies[$name] ?? null);
        return new $class($manager, new CategoryMapper($registry, $defaults), $config, new Json(), $categories,
            [new V3()]);
    }

    /** Read the actual preset's typed DI array, so tests exercise the shipped definitions. */
    private function items(SimpleXMLElement $node): array
    {
        $items = [];
        foreach ($node->item as $item) {
            $type = (string)$item->attributes('http://www.w3.org/2001/XMLSchema-instance')->type;
            $items[(string)$item['name']] = match ($type) {
                'array' => $this->items($item),
                'boolean' => (string)$item === 'true',
                'number' => (int)$item,
                default => (string)$item,
            };
        }
        return $items;
    }
}
