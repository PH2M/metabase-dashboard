# Magento 2 — Metabase Dashboard

Replaces the default Magento 2 admin dashboard with a **Metabase embedded dashboard** via a signed JWT iframe.

When disabled, the native Magento dashboard is restored without any code change.

---

## Requirements

- Magento 2.4.x
- PHP 8.1+
- A running Metabase instance with **Embedding enabled**

---

## Installation

### Via Composer

```bash
composer require ph2m/magento2-metabase-dashboard
php bin/magento module:enable Ph2m_MetabaseDashboard
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

---

## Metabase Configuration

Two steps are required on the Metabase side before the iframe will work.

### 1. Enable embedding globally

In Metabase: **Admin → Settings → Embedding** → toggle **Enable Embedding** to ON.

### 2. Enable embedding on the specific dashboard

Open the target dashboard → click the **share icon** → **Embed this dashboard in an application** → toggle to ON → click **Publish**.

> Without this second step Metabase returns `Embedding is not enabled for this object.` even with a valid JWT token.

---

## Magento Configuration

Go to **Stores → Configuration → Robertet → Metabase Dashboard**.

| Field | Description |
|---|---|
| Enable | Activates the Metabase iframe (default: No) |
| Metabase Site URL | Base URL of your Metabase instance, e.g. `http://51.15.9.102:3000` |
| Embedding Secret Key | Found in Metabase Admin → Embedding → Secret key. Stored encrypted. |
| Dashboard ID | The numeric ID from the Metabase dashboard URL |
| Token Expiration (minutes) | JWT lifetime, default 10 min |
| Show Border | Adds a border to the iframe (Metabase option) |
| Show Title | Shows the dashboard title inside the iframe |

---

## How it works

1. When **enabled**, the block is injected first in the admin dashboard content area.
2. A short-lived **JWT token** (HS256) is generated server-side on each page load using the secret key and dashboard ID.
3. The signed URL is passed to an `<iframe>` that fills the viewport.
4. A CSS rule (`.metabase-dashboard-wrapper ~ *`) hides all following sibling blocks (the native Magento dashboard widgets) without removing them from the DOM.
5. When **disabled**, the block renders an empty string and the native dashboard is fully restored.

### JWT generation (no external library)

The module implements HS256 natively in PHP — no `firebase/php-jwt` or other dependency needed:

```
token = base64url(header) + "." + base64url(payload) + "." + base64url(HMAC-SHA256(header + "." + payload, secret))
```

Equivalent Python reference:

```python
import jwt, time

payload = {
    "resource": {"dashboard": DASHBOARD_ID},
    "params": {},
    "exp": round(time.time()) + (60 * 10)
}
token = jwt.encode(payload, METABASE_SECRET_KEY, algorithm="HS256")
iframe_url = METABASE_SITE_URL + "/embed/dashboard/" + token + "#bordered=true&titled=true"
```

---

## Security notes

- The secret key is stored **encrypted** in Magento config via `EncryptorInterface`.
- The JWT expires after the configured duration (default 10 min); a fresh token is generated on every admin dashboard page load.
- The Metabase instance should not be publicly accessible — the iframe serves signed embed URLs only.

---

## License

Proprietary — Copyright © PH2M SARL. All rights reserved.
