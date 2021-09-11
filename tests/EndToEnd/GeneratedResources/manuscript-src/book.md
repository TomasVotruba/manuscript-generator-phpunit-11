{generator: phpunit_output}
![](tests/phpunit-output.txt)

{generator: table_of_tokens, source: tokens/hello_world.php}
![](tokens/hello_world.table_of_tokens.md)

{generator: php_script_output, source: php_script/script.php}
![](php_script/script.php_script_output.txt)

{generator: buffered_output}
![](example.buffered-output.txt)

{generator: diagram}
![](images/image.diagram.png)

{generator: copy_from_vendor, source: vendor/symfony/event-dispatcher-contracts/EventDispatcherInterface.php}
![`EventDispatcherInterface`](EventDispatcherInterface.php)

{generator: rector_output}
![](rector/rector-output.diff)
