# This is Chapter 1

| Heading |
| ------- |
| Content |

{caption: "Hello, world!", format: php}
```
<?php

declare(strict_types=1);

echo 'Hello, world!';
```

{line-numbers: true, caption: "Cropped source", format: php}
```
public function bar(): void
{
}
```

{format: txt}
```
.                                                         1 / 1 (100%)

Time: 00:00.782, Memory: 64.50 MB
OK (1 test, 1 assertion)
```

{caption: "`EventDispatcherInterface`", format: php}
```
<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;

/**
 * Allows providing hooks on domain-specific lifecycles by dispatching events.
 */
interface EventDispatcherInterface extends PsrEventDispatcherInterface
{
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param object      $event     The event to pass to the event handlers/listeners
     * @param string|null $eventName The name of the event to dispatch. If not supplied,
     *                               the class of $event should be used instead.
     *
     * @return object The passed $event MUST be returned
     */
    public function dispatch(object $event, string $eventName = null): object;
}
```

{format: diff}
```
1 file with changes
===================

1) Foo.php

    ---------- begin diff ----------
--- Original
+++ New
@@ @@

 final class Foo
 {
-    private function bar()
-    {
-        // dead code, will be removed
-    }
 }
    ----------- end diff -----------

Applied rules:
 * RemoveUnusedPrivateMethodRector


 [OK] 1 file would have changed (dry-run) by Rector
```

# This is Chapter 2
