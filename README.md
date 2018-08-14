[**ПРИМЕР ИСПОЛЬЗОВАНИЯ**](http://yii2-images.snewer.ru/).

[(исходный код примера)](https://github.com/snewer/yii2-images-demo).

## Установка

Модуль распространяется как [composer](http://getcomposer.org/download/) пакет
и устанавливается командой
```
php composer.phar require snewer/yii2-images "^1.0.0@dev"
```
или добавлением
```json
"snewer/yii2-storage": "^1.0.0@dev"
```
в composer.json файл проекта.

После установки пакета необходимо подготовить модуль к работе.

## Добавление миграции

Для работы модуля необходимы таблицы, установить которые
можно с помощью команды:

```
php yii migrate/up --migrationPath=@vendor/snewer/yii2-images/src/migrations
```

Или можно добавить путь к миграциям в migrationNamespaces в настройках
controllerMap консольного приложения:
```php
'controllerMap' => [
    // ...
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => null,
        'migrationNamespaces' => [
            // ...
            'snewer\images\migrations',
        ],
    ],
],
```

После чего запустить команду `migrate/up`:
```
yii migrate/up
```

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

Поддерживаются значения `GD` и `Imagick`.

По-умолчанию модуль автоматически провряет наличие библиотеки `Imagick`, и,
в случае обнаружения использует ее, иначе — используется `GD`.

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

## Подготовка моделей

Для использования изображений в ваших моделях необходимо
добавить в них свойство, в котором будет хранится
идентификатор изображения (**целое положительное число**), например, `image_id`.

После этого добавьте связь один-к-одному между вашей моделью
и моделью изображения
```
snewer\images\models\Image
```

Например:
```
public function getImage()
{
    return $this->hasOne(\snewer\images\models\Image::className(), ['id' => 'image_id']);
}
```

Также не забудьте настроить правило валидации для вашего атрибута, например:
```
public function rules()
{
    return [
        ['image_id', 'exist', 'targetAttribute' => 'id', 'targetClass' => 'snewer\images\models\Image']
    ];
}
```

## Загрузка изображений

После того, как свойство для хранения идентификатора
изображения готово, можно использовать виджет
```
snewer\images\widgets\ImageUploadWidget
```
для загрузки
изображения в представлении, например:
```php
$form = ActiveForm::begin();

echo $form->field($model, 'image_id')->widget('snewer\images\widgets\ImageUploadWidget') ?>

$form::end();
```
Свойства виджета описаны ниже.

## Свойства виджета

### - getImageUrl

Ссылка на действие для получения информации о загруженном изображении.

По-умолчанию используется контроллер модуля.

### - uploadImageUrl

Ссылка на действие для загрузки изображения.

По-умолчанию используется контроллер модуля.

### - proxyImageUrl

Ссылка на действие для получения изображения по URL.

По-умолчанию используется контроллер модуля.

### - aspectRatio

Отношение сторон для выбора области при обрезании изображения.

Если установлено значение 0, то возможен произвольный выбор.

По-умолчанию 0.

### - bgColor

Фон полотна изображения.

По-умолчанию `#FFFFFF`.

### - supportAC

Необходимоть поддержки альфа-канала изображения (прозрачности).

По-умолчанию `false`.

### - emptyImage

Ссылка на изображение-заглушку, которое отображается
в виджете, пока не загружено изображение.

### - jqueryAsset

Класс пакета jQuery.

По-умолчанию `yii\web\JqueryAsset`.

### - bootstrapAsset

Класс пакета со стилями Bootstrap.

По-умолчанию `yii\bootstrap\BootstrapAsset`.

### - bootstrapPluginAsset

Класс пакета со скриптами Bootstrap.

По-умолчанию `yii\bootstrap\BootstrapPluginAsset`.

### - cropperAsset

Класс пакета [Cropper](https://github.com/fengyuanchen/cropper).

По-умолчанию `snewer\images\assets\CropperAsset`.

### - laddaAsset

Класс пакета [Ladda](https://github.com/hakimel/Ladda).

По-умолчанию `snewer\images\assets\LaddaAsset`.

### - fontAwesomeAsset

Класс пакета [Font Awesome](https://fontawesome.com).

По-умолчанию `snewer\images\assets\FontAwesomeAsset`.

### - magnificPopupAsset

Класс пакета [Magnific Popup](http://dimsemenov.com/plugins/magnific-popup/).

По-умолчанию `snewer\images\assets\MagnificPopupAsset`.

## Использование загруженных изображений

После загрузки изображения и сохранения модели можно выводить
следующие полезные свойства изображения:
```
$image = $model->image;

// URL ссылка изображения.
echo $image->url;

// Ширина изображения.
echo $image->width;

// Высота изображения.
echo $image->height;

// Качество изображения.
echo $image->quality;

// Timestamp времени загрузки изображения
echo $image->uploaded_at;
```

## Миниатюры (preview) изображения

У загруженного изображения можно получить неограниченное
количесто миниатюр различных вариантов.

Например, для непропорционального изменения размера
изображения можно воспользоваться следующим методом:
```php
$image = $model->image;
$preview = $image->getPreviewCustom(300, 300);
echo $preview->url;
```

В результате получим ссылку на растянутое (сжатое) до 300х300 пикселей
изображение.

**Внимание!** Миниатюра создается только один раз. При следующих
вызовах метода будет возвращаться уже существующее изображение.

## Доступные методы для создания миниатюр

### - getPreviewCustom(width, height)

Размер изменяется непропорционально.

Необходимо передать два аргумента:

- width - ширину требуемой миниатюры;
- height - высоту.

### - getPreviewBestFit(width, height)

Размер изменяется пропорционально.

Лишние участки изображения обрезаются.

Необходимо передать два аргумента:

- width - ширину требуемой миниатюры;
- height - высоту.

### - getPreviewToWidth(width)

Возвращает пропорционально измененный размер изображения
с указанной шириной.

Необходимо передать один аргумент:

- width - ширину требуемой миниатюры.

### - getPreviewToHeight(height)

Возвращает пропорционально измененный размер изображения
с указанной высотой.

Необходимо передать один аргумент:

- height - высоту требуемой миниатюры.

### - getPreviewBackgroundColor(width, height, bgColor)

Возвращает пропорционально измененный размер изображения
с указанной шириной и высотой.

Свободные участки полотна закрашиваются в цвет `bgColor`.

Необходимо передать до трех аргументов:

- width - ширину требуемой миниатюры;
- height - высоту;
- bgColor - цвет фона (не обязательно, по-умолчанию `#FFFFFF`).

### - getPreviewBackgroundImage(width, height, background, greyscale, blur, pixelate)

Возвращает пропорционально измененный размер изображения
с указанной шириной и высотой.

Свободные участки полотна занимает искаженное фоновое изображение.

Необходимо передать до шести аргументов:

- width - ширину требуемой миниатюры;
- height - высоту;
- background - фоновое изображение (не обязательно, по-умолчанию само изображение);
- greyscale - сделать фон черно-белым (не обязательно, по-умолчанию `true`);
- blur - уровень размытия фона (не обязательно, по-умолчанию `30`);
- pixelate - уровень пикселирезации фона (не обязательно, по-умолчанию `5`).