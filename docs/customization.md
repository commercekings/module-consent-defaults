# Categories and customization

The rules behind the default categories and how to change them. Start with the [README](../README.md).

## Rules

Optional categories start off. The `personalization` category is labeled “Personalization”; its description names each concrete use (today the cart reminder email), because consent must be specific – when a new purpose joins the category, extend the description and raise the source revision. Always-on categories never grant feature purposes. Declaring and activating a purpose makes its switch available; the visitor must accept it before the feature has consent. The `marketing` category is for third-party advertising (ad measurement, remarketing – e.g. Google Consent Mode's `ad_storage`, `ad_user_data`, `ad_personalization`); its default description names Google and Meta as examples – override it with the networks the shop really uses. Analytics, Personalization and Marketing list no purposes themselves: each integration declares its own purpose with `category = analytics` / `personalization` / `marketing` (`CommerceKings_ConsentGoogleTag`: `google_analytics`, `google_ads`; `CommerceKings_AbandonedCart`: `cart_reminder`), so several tools can share one switch while each keeps its own consent and records. ConsentGoogleTag and AbandonedCart declare their own purposes. External-content integrations must declare `external_content` themselves. Unmapped or undeclared purposes never gain consent.

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

Magento merges these items with the preset. Labels and descriptions use Magento translation. Optional preset categories use `hide_if_unmapped=true`: they are hidden if none of their mapped purposes is declared and active.

To replace the preset for an adapter, supply a complete, nonempty `categories` argument on that adapter's `Model\Source`, as documented in ConsentVanilla. Explicit source categories replace the whole preset; there is no merge with defaults. A site's own source definitions therefore take precedence without changing their wording or mappings. An empty array uses the preset. The adapters require this package, so it cannot be disabled while one is enabled; to stop using the preset, give the adapter its own categories.

Removing site configuration while this module is enabled activates the preset. Consents whose purposes remain mapped can remain valid; raise the source revision when visitors must agree to changed wording or mappings again. Rebuild DI, clear caches and restart long-running consumers after configuration changes.

## Tests

From the Magento root, with the package installed:

```sh
vendor/bin/phpunit --no-configuration --bootstrap vendor/autoload.php vendor/commercekings/module-consent-defaults/Test/Unit
```

The preset tests exercise all installed adapters when available, including absent purposes, accepted choices, frontend mappings and explicit site configuration.
