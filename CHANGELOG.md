# v1.0.1
## 04/29/2026

1. [](#bugfix)
    * Fixed PHP 8.1+ deprecation notice — explicit string casts where `null` was being passed to string-typed function arguments.
    * Fixed `Cannot use bool as array` fatal on PHP 8.x when the `slack_members` cache entry hadn't been seeded yet. `Cache::fetch()` returns `false` on a miss, but the plugin was destructuring the return value directly. Now guards on `is_array()` before unpacking.
    * Skip all Slack API work when `slack_token` is empty. Previously the plugin would still hit Slack on every frontend request even without a token, paying ~200ms of network round-trip just to receive a 401 — slowing down every page on a freshly-installed-but-unconfigured site.

# v1.0.0
## 02/04/2017

1. [](#new)
    * ChangeLog started...
