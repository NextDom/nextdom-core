<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\Utils;

trait ImageEntity
{
    abstract public function updateChangeState($oldValue, $newValue);

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="text", length=16777215, nullable=true)
     */
    protected $image;

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getImage($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->image, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setImage($_key, $_value)
    {
        $image = Utils::setJsonAttr($this->image, $_key, $_value);
        $this->updateChangeState($this->image, $image);
        $this->image = $image;
        return $this;
    }

    /**
     *
     * Get data of the object in plain text array
     *
     * @return array
     *
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function toArray() {
        $result = Utils::o2a($this, true);
        unset($result['image']);
        $result['img'] = $this->getImgLink();
        return $result;
    }

    /**
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getImgLink() {
        if ($this->getImage('sha512') == '') {
            return '';
        }
        $filename = self::TABLE_NAME . $this->getId() . '-' . $this->getImage('sha512') . '.' . $this->getImage('type');
        return 'data/' . self::IMG_DIR_NAME . '/' . $filename;
    }

    /**
     * @return string
     * @throws CoreException
     */
    public function displayImage()
    {
        $size = $this->getImage('size');
        return '<img style="z-index:997" src="' . $this->getImgLink() . '" data-size_y="' . $size[1] . '" data-size_x="' . $size[0] . '"/>';
    }
}