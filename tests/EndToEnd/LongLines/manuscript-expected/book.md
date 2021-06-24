{format: php}
```
<?php

declare(strict_types=1);

use ANamespace\...\ToALineThatIsWayTooLong; // (abbreviated)

final class LongClassName extends LongParentClassName implements
LongInterfaceName
{
    public function __construct()
    {
    }
}
```

{format: diff}
```
 use ANamespace\...\ToALineThatIsWayTooLong; // (abbreviated)
-foo
+bar
```
