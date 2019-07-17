<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReviewRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Review
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
    private $info_id;

    /**
     * @ORM\Column(type="string", length=10000)
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $pid;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default": null})
     */
    private $reply_to;

    /**
     * @ORM\Column(type="integer")
     */
    private $fav_count;

    /**
     * @ORM\Column(type="integer")
     */
    private $reply_count;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(int $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getReplyTo(): ?int
    {
        return $this->reply_to;
    }

    public function setReplyTo(int $reply_to): self
    {
        $this->reply_to = $reply_to;

        return $this;
    }

    public function getFavCount(): ?int
    {
        return $this->fav_count;
    }

    public function setFavCount(int $fav_count): self
    {
        $this->fav_count = $fav_count;

        return $this;
    }

    public function getReplyCount(): ?int
    {
        return $this->reply_count;
    }

    public function setReplyCount(int $reply_count): self
    {
        $this->reply_count = $reply_count;

        return $this;
    }
}
