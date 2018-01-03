# Code conventions

This page will try to describe our code conventions and is made to be as precise as possible.

Consider also installing EditorConfig plugin for you IDE, because most of the coding rules are described in the 
project `.editorconfig` file.
## PHP

A lot of templates and rules are included as PhpStorm configuration files. That's why we recommend to use PhpStorm 
(but not required).

### Files templates

#### Generic class (included as PhpStorm template)

```php
<?php
namespace {namespace}
        // <-- 1 line
use ...
        // <-- 1 line
/**
 * Class {class_name}
 * {optionnal comment (if not specified, line has to be removed)}
 *
 * @author Mathrix Back-End Team
 * @package {namespace}
 * @since {version of introduction}
 * @copyright Mathrix SA
 */
class {class} {
    [...]
}
        // <-- 1 line at the END
```
