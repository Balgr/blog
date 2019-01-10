<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 10:35
 */

namespace Blog\app\Entity;
use Blog\core\Entity;


class Post extends Entity
{
    private $title;
    private $subtitle;
    private $content;
    private $creationDate;
    private $lastEditDate;
    private $lastEditReason;
    private $featuredImage;
    private $status;
    private $creatorId;
    private $author;
    private $comments;
    private $commentsNb;

    // Constantes d'exceptions
    const INVALID_AUTHOR = 1;
    const INVALID_TITLE = 2;
    const INVALID_CONTENT = 3;
    const INVALID_REASON_EDITION = 4;

    // Constantes de statut
    const POST_STATUS_PUBLISHED = "Published";
    const POST_STATUS_TRASH = "Trash";

    public function isValid()
    {
        if(!is_null($this->title) && !is_null($this->subtitle) && !is_null($this->content) && !is_null($this->creationDate) && !is_null($this->status) && !is_null($this->creatorId)) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function title()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function subtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param mixed $subtitle
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    /**
     * @return mixed
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function creationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param mixed $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return mixed
     */
    public function lastEditDate()
    {
        return $this->lastEditDate;
    }

    /**
     * @param mixed $lastEditDate
     */
    public function setLastEditDate($lastEditDate = null)
    {
        $this->lastEditDate = $lastEditDate;
    }

    /**
     * @return mixed
     */
    public function lastEditReason()
    {
        return $this->lastEditReason;
    }

    /**
     * @param mixed $lastEditReason
     */
    public function setLastEditReason($lastEditReason = null)
    {
        $this->lastEditReason = $lastEditReason;
    }

    /**
     * @return mixed
     */
    public function featuredImage()
    {
        return $this->featuredImage;
    }

    /**
     * @param mixed $featuredImage
     */
    public function setFeaturedImage($featuredImage = null)
    {
        $this->featuredImage = $featuredImage;
    }

    /**
     * @return mixed
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function creatorId()
    {
        return $this->creatorId;
    }

    /**
     * @param mixed $creatorId
     */
    public function setCreatorId($creatorId)
    {
        $this->creatorId = $creatorId;
    }


    /**
     * @return mixed
     */
    public function author()
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function comments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function commentsNb()
    {
        return $this->commentsNb;
    }

    /**
     * @param mixed $commentsNb
     */
    public function setCommentsNb($commentsNb)
    {
        $this->commentsNb = $commentsNb;
    }
}