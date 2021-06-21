{format: txt}
```
PHPUnit 9.5.5 by Sebastian Bergmann and contributors.

F                                                         1 / 1 (100%)

Time: 00:00.782, Memory: 64.50 MB
There was 1 failure:

1) NotTrueTest::test
Failed asserting that false is true.

NotTrueTest.php:14

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
```

| Line | Token | Value |
| --- | --- | --- |
| 1 | `T_OPEN_TAG` | `<?php\n` |
| 2 | `T_WHITESPACE` | `\n` |
| 3 | `T_DECLARE` | `declare` |
| 3 | `(` | `(` |
| 3 | `T_STRING` | `strict_types` |
| 3 | `=` | `=` |
| 3 | `T_LNUMBER` | `1` |
| 3 | `)` | `)` |
| 3 | `;` | `;` |
| 3 | `T_WHITESPACE` | `\n\n` |
| 5 | `T_ECHO` | `echo` |
| 5 | `T_WHITESPACE` | ` ` |
| 5 | `T_CONSTANT_ENCAPSED_STRING` | `'Hello, world!'` |
| 5 | `;` | `;` |
| 5 | `T_WHITESPACE` | `\n` |

{format: txt}
```
Hello, world!
```

{format: txt}
```
Echo whatever you want
```

![](images/image.diagram.png)
