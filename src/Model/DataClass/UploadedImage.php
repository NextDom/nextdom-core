<?php

namespace NextDom\Model\DataClass;


class UploadedImage
{
    private $type;
    private $hash;
    private $size;
    private $data;
    private $path;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     * @return UploadedImage
     */
    public function setType($type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     * @return UploadedImage
     */
    public function setHash($hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     * @return UploadedImage
     */
    public function setSize($size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSizeX()
    {
        return $this->size[0];
    }

    /**
     * @return mixed
     */
    public function getSizeY()
    {
        return $this->size[1];
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return UploadedImage
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return UploadedImage
     */
    public function setPath($path): self
    {
        $this->path = $path;
        return $this;
    }
}
