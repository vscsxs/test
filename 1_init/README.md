Для запуска `docker-compose up`

После этого можно подключится к контейнеру и написать/запустить требуемый консольный скрипт, который будет использовать класс init.

Например

``` php
<?php
//run.php
include('init.php');

$init = new init();
var_dump($init->get());

```

``` bash
php run.php
```
