## Установка

Модуль распространяется как [composer](http://getcomposer.org/download/) пакет
и устанавливается командой
```
php composer.phar require snewer/yii2-images @dev
```
или добавлением
```json
"snewer/yii2-storage": "@dev"
```
в composer.json файл проекта.

После установки пакета необходимо подготовить модуль к работе.

## Добавление миграции

Для работы модуля необходимы таблицы, установить которые
можно с помощью миграции:

1. Создайте новую миграцию командой `yii migrate/create`, например:
```
yii migrate/create init_images_module
```
2. Откройте созданную миграцию и унаследуйте ее от класса
```
snewer\images\migrations\CreateTablesMigration
```
и оставьте тело класса пустым.

Ваша миграция примет примерно следующий вид:
```php
<?php

use snewer\images\migrations\CreateTablesMigration;

class m180211_174817_init_images_module extends CreateTablesMigration
{
}
```

3. Примените миграцию командой `yii migrate`.

## Настройка хранилища изображений

Для хранения изображений используется компонент `snewer/yii2-storage`.

Для детального изучения компонента [перейдите к описанию пакета](https://github.com/snewer/yii2-storage).

Пример конфигурации компонента:
```php
'components' => [
    'storage' => [
        'class' => 'snewer\storage\StorageManager',
        'buckets' => [
            'images' => [
                'class' => 'snewer\storage\drivers\FileSystemDriver',
                'basePath' => '@webroot/uploads/images/',
                'baseUrl' => '@web/uploads/images/'
            ],
        ]
    ],
],
```

## Настройка модуля

Пример конфигурации:
```php
'modules' => [
    'images' => [
        'class' => 'snewer\images\Module',
        'imagesStoreBucketName' => 'images',
        'controllerAccess' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['?', '@']
                ]
            ]
        ]
    ]
],
```
Детальное описание свойств модуля смотрите ниже.

## Свойства модуля

### - storage

Идентификатор компонента `snewer/yii2-storage` или его конфигурация.

По-умолчанию установлен как `storage`, то есть ссылается
на компонент приложения `storage`.

### - imagesStoreBucketName

Название хранилища, в которое будут загружены
оригиналы изображений.

### - imagesQuality

Качество оригинала изображения, которое будет загружено в хранилище.

Целое число от 10 до 100.

По-умолчанию 100.

### - previewsStoreBucketName

Название хранилища, в которое будут загружены
миниатюры изображений.

### - previewsQuality

Качество миниатюр изображений, которые будут загружены в хранилище.

Целое число от 10 до 100.

По-умолчанию 80.

### - driver

Графический драйвер для обработки изображений.

Поддерживаются значения `GD` (по-умолчанию) и `Imagick`.

### - controllerAccess

Конфигурация фильтра доступа к контроллеру модуля.

По-умолчанию используется следующая конфигурация:
```php
[
    'class' => 'yii\filters\AccessControl',
    'rules' => [
        [
            'allow' => true,
            'roles' => ['@']
        ]
    ]
]
```
То есть модуль доступен для всех аутентифицированных пользователей.