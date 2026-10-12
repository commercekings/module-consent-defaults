# CommerceKings_ConsentDefaults

Install in a Magento project with `composer require commercekings/module-consent-defaults:^0.1` after the first
release is published. Enable the Magento module(s) listed below and run `bin/magento setup:upgrade`.
For development/tests use source checkouts (`--prefer-source`); release archives omit `Test/`.


Shared banner categories for the consent banner adapters (e.g. `CommerceKings_ConsentVanilla`) – every adapter requires this package, so a banner always comes with working categories. Each optional category shows only while a feature module uses it. The preset supplies no purpose declarations, tracking, content loading or cookie cleanup.

| Category | Required | Mapped purpose | When shown |
|---|---|---|---|
| Strictly necessary (`necessary`) | Yes | None | Always |
| Analytics (`analytics`) | No | purposes declaring `category = analytics` (e.g. `google_analytics`) | Purpose declared |
| External content (`external`) | No | `external_content` | Purpose declared |
| Personalization (`personalization`) | No | `cart_reminder` | Purpose declared |
| Marketing (`marketing`) | No | purposes declaring `category = marketing` (e.g. `google_ads`) | Purpose declared |

Optional categories start off. The `personalization` category is labelled “Personalization”; its description names each concrete use (today the cart reminder email), because consent must be specific – when a new purpose joins the category, extend the description and raise the source revision. Always-on categories never grant feature purposes. Declaring a purpose makes its switch available; the visitor must accept it before the feature has consent. The `marketing` category is for third-party advertising (ad measurement, remarketing – e.g. Google Consent Mode's `ad_storage`, `ad_user_data`, `ad_personalization`); its default description names Google and Meta as examples – override it with the networks the shop really uses. Analytics and Marketing list no purposes themselves: each integration declares its own purpose with `category = analytics` / `marketing` (`CommerceKings_ConsentGoogleTag`: `google_analytics`, `google_ads`), so several tools can share one switch while each keeps its own consent and records. ConsentGoogleTag and AbandonedCart declare their own purposes. External-content integrations must declare `external_content` themselves. Unmapped or undeclared purposes never gain consent.

## Setup

Install a banner adapter (this preset comes with it) and the feature modules you need. Once published, a Vanilla + Google setup is:

```sh
composer require commercekings/module-consent-vanilla commercekings/module-consent-google-tag
bin/magento module:enable CommerceKings_Consent CommerceKings_ConsentVanilla CommerceKings_ConsentDefaults CommerceKings_ConsentGoogleTag
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:clean
```

Follow the shop's normal static-content deployment process, then select the banner in **Stores > Configuration > CommerceKings > Consent > Source** and set the privacy-policy URL. The preset does not select a source or enable analytics configuration in Magento. Configure the owning feature module separately.

## Customization

The preset is injected once into `Consent\Model\CategoryMapper` through the `defaultCategories` DI argument. All adapters use it only when their own `categories` argument is empty.

To change the shared preset, sequence your site module after `CommerceKings_ConsentDefaults` and override named array items:

```xml
<type name="CommerceKings\Consent\Model\CategoryMapper">
    <arguments>
        <argument name="defaultCategories" xsi:type="array">
            <item name="analytics" xsi:type="array">
                <item name="label" xsi:type="string">Usage statistics</item>
                <item name="description" xsi:type="string">Your shop's explanation of its analytics.</item>
            </item>
        </argument>
    </arguments>
</type>
```

Magento merges these items with the preset. Labels and descriptions use Magento translation. Optional preset categories use `hide_if_unmapped=true`: they are hidden if none of their mapped purposes is declared.

To replace the preset for an adapter, supply a complete, nonempty `categories` argument on that adapter's `Model\Source`, as documented in ConsentVanilla. Explicit source categories replace the whole preset; there is no merge with defaults. A site's own source definitions therefore take precedence without changing their wording or mappings. An empty array uses the preset. The adapters require this package, so it cannot be disabled while one is enabled; to stop using the preset, give the adapter its own categories.

Removing site configuration while this module is enabled activates the preset. Consents whose purposes remain mapped can remain valid; raise the source revision when visitors must agree to changed wording or mappings again. Rebuild DI, clear caches and restart long-running consumers after configuration changes.

## Tests

From the Magento root, with the package installed:

```sh
vendor/bin/phpunit --no-configuration --bootstrap vendor/autoload.php vendor/commercekings/module-consent-defaults/Test/Unit
```

The preset tests exercise all installed adapters when available, including absent purposes, accepted choices, frontend mappings and explicit site configuration.
