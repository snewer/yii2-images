<?php

namespace snewer\images\models;

/**
 * Список констант для указания различных типов изображений.
 *
 * Class ImageTypes
 * @package snewer\images\models
 */
class ImageTypes
{

    // Внимание!
    // Не использовать значения от 50 до 99 включительно!
    // Данные значения остаются для расширения пакета (типов используемых изображений) в рамках отдельных проектов.

    const ORIGINAL = 0;

    const RESIZED_TO_BOX = 1;

    const RESIZED_SELF_BACKGROUND = 2;

    const RESIZED_AND_CROPPED = 3;

}