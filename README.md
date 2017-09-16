# Builder Container
Сборщик - Строитель - Внедрение зависимостей (PHP)

# Описание

Builder собирает объекты по заданным инструкциям.

Предназначен для сборки объектов и управления их зависимостями.

Может быть использован как [Dependency Injection Container](https://ru.wikipedia.org/wiki/Внедрение_зависимости) (DIC) или Service Locator.

В отличае от того же DIC, Builder при сборке пакета может вызывать цепочку методов объекта, передавать зависимости в аргументы этих методов, задавать свойства объекта и т.д.

Builder как и Service Locator можно использовать локально в ваших классах, получаю объект по псевдониму (alias)

```php
<?php
use arhone\builder\Builder;

Builder::config([
    'Alias' => [
        'class' => 'ClassNameAlias'
    ]
]);

$Obj = Builder::get('Alias');
```
или собирать по инструкции

```php
<?php
use arhone\builder\Builder;

Builder::config([
    'Alias' => [
        'class' => 'ClassNameAlias'
    ]
]);

$Obj = Builder::make([
    'class' => 'ClassName',
    'construct' => [
        ['Alias'],
        [
            'class' => 'ClassName2'
        ]
    ],
    'method' => [
        'config' => [
            [
                'array' => $config
            ]
        ]
    ]
]);
```

Создаст объкт класса ClassName, передас в конструктор Alias, создаст и передаст в конструктор ClassName2, передаст в метод ClassName->config($config) массив с настройками $config

Работает с PHP 7 и выше.

# Подключение

1) Загрузите пакет с помощью composer или скачайте с github

```composer require arhone/builder```

2) Подключите Builder с помощью автозагрузчика

```php
<?php
use arhone\builder\Builder;

include 'vendor/autoload.php';

$Builder = new Builder();
```

Передать инструкци можно сразу же в конструктор

```php
$Builder = new Builder(include 'config/boulder/instruction.php');
```

И/или дополнить набор инструкций позже
```php
$Builder->config(include 'config/boulder/instruction1.php');
$Builder->config(include 'config/boulder/instruction2.php');
```

Подразумевается что config/boulder/instruction.php вернёт массив с инструкциями, вроде тех, что описаны выше:
```php
<?php
return [
    'Alias1' => [
        'class' => 'ClassName1'
    ],
    'Alias2' => [
        'class' => 'ClassName2'
    ]
];
```
# Инструкции для сборки
