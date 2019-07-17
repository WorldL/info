<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InfoImgRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class InfoImg
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    const IMG_DOMAIN = 'cdn.xiaohailang.net';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $info_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="integer")
     */
    private $file_size;

    /**
     * @ORM\Column(type="integer")
     */
    private $height;

    /**
     * @ORM\Column(type="integer")
     */
    private $weight;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $format;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfoId(): ?int
    {
        return $this->info_id;
    }

    public function setInfoId(int $info_id): self
    {
        $this->info_id = $info_id;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        // 获取图片信息
        $imgInfo = $this->getImgInfoByApi();
        $this->setFormat($imgInfo['Format']['value'])
             ->setFileSize($imgInfo['FileSize']['value'])
             ->setHeight($imgInfo['ImageHeight']['value'])
             ->setWeight($imgInfo['ImageWidth']['value']);

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(int $file_size): self
    {
        $this->file_size = $file_size;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    // 获取图片信息
    public function getImgInfoByApi()
    {
        try {
            $client = new Client();
            $res = $client->get($this->getImgUrl() . '?x-oss-process=image/info');
            return json_decode($res->getBody()->getContents(), true);
        } catch (\Exception $e) {
            throw new \Exception('图片上传失败,请重新发布。');
        }
    }
    
    // 获取图片url
    public function getImgUrl($rule = '', $scheme = 'http')
    {
        $rule = empty($rule) ? '' : '?x-oss-process=style/'.$rule;
        return $scheme.'://'.self::IMG_DOMAIN.'/'.$this->getPath().$rule;
    }
}
