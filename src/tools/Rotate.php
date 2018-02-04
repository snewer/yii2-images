<?php

namespace snewer\images\tools;

class Rotate extends Tool
{

    public $angle = 0;

    public $bgColor = '#FFFFFF';

    public function process()
    {
        $angle = floatval($this->angle);
        // Была обнаружена проблема при повороте изображения на 0 градусов:
        // вокруг него добавлялась белая рамка.
        // Поэтому не вызываем метод rotate когда поворавичвать изображение не нужно.
        if ($angle % 360 > 0) {
            $this->image->rotate(-$angle, $this->bgColor);
        }
        // На некоторых изображениях обнаружились проблемы
        // с последовательным вызовом методов rotate и crop для драйвера Imagick.
        // Добавил issue: https://github.com/Intervention/image/issues/723
        // Это решает проблему:
        if ($this->image->getDriver() instanceof \Imagick) {
            $this->image->getCore()->setImagePage($this->image->width(), $this->image->height(), 0, 0);
        }
    }

}