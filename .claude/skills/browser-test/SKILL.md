---
name: browser-test
description: Use whenever the user asks for browser tests, to "test it in the browser", to see the feature working, or for screenshots of the app. Drives the real xlearn app at http://xlearn.test with Playwright and shows the user screenshots of each step.
---

# Browser tests for xlearn

A browser test here means driving the real app in Chromium with Playwright and **showing the user screenshots**. It does not mean `php artisan test`, which is the feature test suite.

## 1. Check the app is up

- **App:** Laravel Herd serves `http://xlearn.test`. Check it with `curl -s -o /dev/null -w "%{http_code}" http://xlearn.test/sign-in`, which should return 200. If it doesn't, ask the user to start Herd. Don't run `php artisan serve` instead.
- **Assets:** if `public/hot` exists, pages load CSS/JS from the Vite dev server at the URL inside it. Curl `<that URL>/@vite/client`. If it's down, ask the user to run `npm run dev`, or the pages render unstyled.
- **Reverb** (`localhost:8080`) is optional. Without it, live updates (Echo) don't arrive, so reload the page to see new data. Say so in the report.
- **Queue:** `QUEUE_CONNECTION=database`, and notifications are queued. After an action that sends one, run `php artisan queue:work --stop-when-empty` (in PowerShell, where `php` is on the PATH) before checking the result.
- **With Reverb down,** every message sent queues a `MessageSent` broadcast job that fails and lands in `failed_jobs`. That's expected. Delete those rows at cleanup, matching `failed_at` from the run and `payload LIKE '%MessageSent%'`.

## 2. Test data

Never change the user's real accounts or passwords. Create throwaway records with an `@example.test` email and delete them at the end.

Run seeding as a plain PHP script that boots the app, from the scratchpad:

```php
<?php
require 'C:/Users/Benchanan Ken-Idehen/Documents/Dev/xlearn/vendor/autoload.php';
$app = require 'C:/Users/Benchanan Ken-Idehen/Documents/Dev/xlearn/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// e.g. User::factory()->instructor()->create([... 'email' => 'bt-instructor@example.test', 'password' => 'password']);
```

Run it with `php <script>`, then query to confirm the rows exist before testing.

- `php artisan tinker` rejects `<?php` on stdin.
- A multi-line `tinker --execute` printed nothing and created nothing.

Clean up the same way, by deleting by the `@example.test` emails. Users soft delete, so use `forceDelete()`.

## 3. Drive it with Playwright

Playwright is in `node_modules` (`playwright` 1.64.1, Chromium already downloaded). Write the script in the scratchpad as a `.cjs` file. Load Playwright by absolute path, because the scratchpad has no `node_modules`:

```js
const { chromium } = require('C:/Users/Benchanan Ken-Idehen/Documents/Dev/xlearn/node_modules/playwright');
const shots = process.argv[2]; // a folder in the scratchpad

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    page.on('console', m => m.type() === 'error' && console.log('console error:', m.text()));

    // Log in
    await page.goto('http://xlearn.test/sign-in');
    await page.fill('#email', 'bt-student@example.test');
    await page.fill('#password', 'password');
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
    await page.screenshot({ path: `${shots}/01-logged-in.png` });

    await browser.close();
})();
```

- Number the screenshots in step order (`01-...png`, `02-...png`) with names that say what they show.
- Use a fresh `browser.newContext()` for each user, e.g. student and instructor, so sessions don't mix.
- To show one component, use `locator.screenshot()`; otherwise take the full page.
- Wait on the element you need (`waitForSelector`, `expect`-style checks), not fixed sleeps. `networkidle` never settles while Echo is retrying Reverb, so use `waitForLoadState('load')` on pages that subscribe to Echo.
- Print console errors. A page can render while a fetch behind it fails.

## 4. Show the user

- **Read every screenshot** with the Read tool, so the images appear in the conversation. Look at each one: a blank or unstyled page is a failure.
- **Report each step:** what was done, what the screenshot shows, and the screenshot's full absolute path.
- **Say what passed and what didn't.** Mention anything skipped, like live updates without Reverb.
- **Delete the test data** (step 2) and confirm it's gone.
