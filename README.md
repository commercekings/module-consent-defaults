# Default consent categories<a href="https://commercekings.net"><picture><source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/commercekings/module-consent-defaults/main/.github/commercekings-dark.svg"><img align="right" width="152" src="https://raw.githubusercontent.com/commercekings/module-consent-defaults/main/.github/commercekings-light.svg" alt="Built by CommerceKings. Ready to sell more? Talk to us"></picture></a>

Works with Magento 2.4.4+ · Luma and Hyvä · PHP 8.1–8.5

[![License](https://badgen.net/github/license/commercekings/module-consent-defaults)](LICENSE) [![Release](https://badgen.net/github/release/commercekings/module-consent-defaults)](https://github.com/commercekings/module-consent-defaults/releases) [![Checks](https://github.com/commercekings/module-consent-defaults/actions/workflows/checks.yml/badge.svg?branch=main)](https://github.com/commercekings/module-consent-defaults/actions/workflows/checks.yml)

**Sensible banner categories, ready on day one.**

- **Five categories:** Strictly necessary, Analytics, External content, Personalization, Marketing.
- **Shown only when used.** An empty category never appears in the banner.
- **Yours to change.** Rename or replace any of them in your site module.

## Installation

Usually a banner (e.g. `commercekings/module-consent-vanilla`) brings it. On its own:

```sh
composer require commercekings/module-consent-defaults:^0.1
bin/magento setup:upgrade
```

## Categories

| Category | Required | Grants | Shown |
|---|---|---|---|
| Strictly necessary | Yes | nothing | always |
| Analytics | No | purposes declared with `category = analytics` (e.g. `google_analytics`) | while one is in use |
| External content | No | `external_content` | while in use |
| Personalization | No | purposes declared with `category = personalization` (e.g. `cart_reminder`) | while one is in use |
| Marketing | No | purposes declared with `category = marketing` (e.g. `google_ads`) | while one is in use |

Optional categories start off. A required category never grants a feature purpose. Each description names its
concrete uses – when you add a purpose to a category, extend the description and raise *Source revision*.

## Customization

Override labels or descriptions in your site module's `di.xml` (argument `defaultCategories` of
`CommerceKings\Consent\Model\CategoryMapper`), or give the banner its own complete `categories` – they then replace
these defaults entirely. Examples: [categories and customization](docs/customization.md).

## More

[Categories and customization](docs/customization.md) · [Compatibility](COMPATIBILITY.md) · [Changes](CHANGELOG.md)

## Disclaimer

These modules help you ask for, record and respect your shoppers' choices – they don't make your store compliant on
their own. You are responsible for your store's privacy: what you collect, the wording you show, your privacy policy
and whether your setup meets the laws that apply to you (e.g. GDPR, ePrivacy, email marketing rules). Get legal advice
if you are unsure.

---

**Need help installing or setting it up?** **[Get a quote →](https://commercekings.net)**
