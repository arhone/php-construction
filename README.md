# Builder Container
Сборщик - Строитель - Внедрение зависимостей (PHP 7)

# Описание

Builder собирает объекты по заданным инструкциям.

Предназначен для сборки объектов и управления их зависимостями.

Может быть использован как [Dependency Injection Container](https://ru.wikipedia.org/wiki/Внедрение_зависимости) (DIC) или Service Locator.

В отличае от того же DIC, Builder при сборке пакета может вызывать цепочку методов объекта, передавать зависимости в аргументы этих методов, задавать свойства объекта и т.д.

Builder как и Service Locator можно использовать локально в ваших классах, получая объект по псевдониму (alias)

```php
<?php
use arhone\builder\Builder;

Builder::instruction([
    'Alias' => [
        'class' => 'ClassNameAlias'
    ]
]);

$Obj = Builder::make('Alias');
```
или собирать по инструкции

```php
<?php
use arhone\builder\Builder;

Builder::instruction([
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

В следующих версиях можно будет запросить класс на прямую:

```php
<?php
use arhone\builder\Builder;

$Obj = Builder::make('namespace\ClassName');
```

В этом случае Builder сам создаст инструкции на основе зависимостей класса.
Инструкция будет создана для псевдонима "namespace\ClassName", которую вы можете переопределить:

```php
<?php
use arhone\builder\Builder;

Builder::instruction([
    'namespace\ClassName' => [
        'class' => 'ClassNameAlias'
    ]
]);

$Obj = Builder::make('namespace\ClassName');
```
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

Передать/дополнить инструкции можно с помощью метода "instruction"
```php
$Builder->instruction(include 'config/builder/instruction1.php');
$Builder->instruction(include 'config/builder/instruction2.php');
```

Подразумевается что config/builder/instruction.php вернёт массив с инструкциями, вроде тех, что описаны выше:
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

Инструкция строятся по типа Alias => instruction.

По "alias" вы запрашиваете сборку на основе его инструкции.

Builder понимает несколько типов инструкций:

1) class

В инструкции к сборке класса можно указать следующие правила

```php
<?php
return [
    'Alias' => [
        'require' => 'path/ClassName.php', // Может подключить ваш класс, если у вас нет автозагрузчика под него
        'class' => 'ClassName', // Класс, который вы хотите собрать
        'construct' => [
            ['Alias2'], // Передаст "Alias2" в конструктор класса
            [
                'class' => 'ClassName3'
            ] // Создаст и передаст ClassName3 в конструктор класса
        ],
        'property' => [
            'name1' => 'value',
            'name2' => 'value'
        ], // Задаст значения свойствам
        'method' => [
            'config' => [
                [
                    'array' => $config
                ]
            ] // Запустит метод config с переданным массивом $config в качестве аргумента
        ],
        'new' => true, // В этом случае Builder всегда будет возвращать новый экземпляр класса
        'clone' => true // Будет возвращать клонированный экземпляр. clone true/false работает только если new == false
    ]
];
```

2) callback

```php
<?php
return [
    'myFunc' => [
        'callback' => function ($name) {
            return 'Привет ' . $name;
        },
        'argument' => [
            [
                'string' => 'Вася'
            ]
        ]
    ]
];
```
```php
<?php
echo Builder::make('myFunc');
```

В случае с callback типом инструкции, Builder вернёт вам её результат.

С помощью ключа "argument", можно указать набор зависимостей, с которым будет выполнятся функция.

Если вы хотите возвращать функцию, а не результат её выполнения, указывайте тип "callable"

Builder никак не обрабатывает тип "callable", по этому он просто вернёт что есть, по этой же причине вы не можете настроить "argument".

То есть тип "callable" это просто для читабельности, но вы можете его назвать по другому, например "myFunc"

```php
<?php
return [
    'myFunc' => [
        'callable' => function ($name) {
            return 'Привет ' . $name;
        }
    ]
];
```

```php
<?php
$myFunc = Builder::make('myFunc');
echo $myFunc('Вася');
```

3) array, object, integer, float, bool

```php
<?php
return [
    'myArray' => [
        'array' => [1,2,3]
    ]
];
```

По большей части эти типы созданны для наглядности, но Builder будет приводить ваши данные к этим типам

```php
<?php
return [
    'myArray' => [
        'array' => 'строка'
    ] // Вернёт массив ['строка']. Равносильно (array)'строка';
];
```

Если вам не нужно приведение к типу, укажите свой тип.

То есть придумайте любое слово, например добавьте подчёркивание _string или укажите через разделитель.

```php
<?php
return [
    'myArray' => [
        'array|string' => include 'data.php' // include возвращает return 'строка'; а может быть и return ['массив'];
    ] // Ваш выдуманный тип. Ещё варианты что бы вы не думали (data, other, unknown)
];
```

В случае с типом "object" можно использовать "clone" как в примерах с классом.

Если clone == false, объект будет доступен по ссылке, как обычный объект без клонирования.

4) alias

В качестве инструкции можно передать другую инструкцию

```php
<?php
return [
    'Alias1' => [
        'class' => 'ClassName'
    ],
    'Alias2' => [
        'alias' => 'Alias1'
    ],
    'Alias3' => ['Alias2'] // У типа "alias" есть сокращённая форма. Вы просто не указываете тип, по умолчанию это будет "alias"
];
```

5) instruction

```php
<?php
return [
    'Alias' => [
        'instruction' => include 'vendor/name/Alias/config/builder/instruction.php'
    ]
];
```

Тип "instruction" позволяет ссылаться на инструкции, которые лежат за пределами вашей ответственности.

Например это инструкции библиотеки, которую разрабатываете не вы.

Разработчик библиотеки размещяет инструкции для её сборки, а вы просто их подключаете.

```php
<?php
return [
    'Alias' => include 'vendor/name/Alias/config/builder/instruction.php' // Тоже самое
];
```

vendor/name/Alias/config/builder/instruction.php:

```php
<?php
return [
    'class' => 'ClassName'
];
```

# Настройка класса Builder

У класса есть настройки по умолчанию

```php
<?php
use arhone\builder\Builder;

include 'vendor/autoload.php';

$Builder = new Builder([
    'new'   => false, // Создавать новый экземпляр класса или нет, если это не указано в инструкциях
    'clone' => true, // Клонировать объекты или нет, если это не указано в инструкциях
]);
```
