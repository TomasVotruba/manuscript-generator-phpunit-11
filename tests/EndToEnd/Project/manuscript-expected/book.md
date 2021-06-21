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

{format: diff}
```
1 file with changes
===================

1) src/Foo.php

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
