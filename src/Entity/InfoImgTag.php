<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InfoImgTagRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class InfoImgTag
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $info_img_id;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=3)
     */
    private $x;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=3)
     */
    private $y;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $toward;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInfoImgId(): ?int
    {
        return $this->info_img_id;
    }

    public function setInfoImgId(int $info_img_id): self
    {
        $this->info_img_id = $info_img_id;

        return $this;
    }

    public function getX()
    {
        return $this->x;
    }

    public function setX($x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y): self
    {
        $this->y = $y;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getToward(): ?string
    {
        return $this->toward;
    }

    public function setToward(string $toward): self
    {
        $this->toward = $toward;

        return $this;
    }
}
