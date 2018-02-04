<?php

namespace snewer\images\tools;

use ArrayAccess;
use yii\base\BaseObject;
use snewer\images\ModuleTrait;

/**
 * Class Tool
 * @package snewer\images\tools
 * @property \Intervention\Image\Image $image
 */
abstract class Tool extends BaseObject implements ArrayAccess
{

    use ModuleTrait;

    const MIN_SIZE = 1;

    const MAX_SIZE = 10000;

    /**
     * @var \Intervention\Image\Image
     */
    private $_image;

    public function setImage($image)
    {
        $this->_image = $image;
    }

    /**
     * @return \Intervention\Image\Image
     */
    public function getImage()
    {
        return $this->_image;
    }

    public function init()
    {
    }

    abstract public function process();

    /**
     * @inheritdoc
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @inheritdoc
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }

}