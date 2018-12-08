<?php
/**
 * Created by PhpStorm.
 * User: mehdi
 * Date: 06/12/2018
 * Time: 11:02
 */

namespace App\Entity;
use Core\Entity;

class Comment extends Entity
{
    private $authorName;
    private $content;
    private $creationDate;
    private $status;
    private $postId;


    protected function isValid()
    {
        return ! empty($this->author) || !empty($this->content) || !empty($this->title);
    }

    /**
     * @return mixed
     */
    public function authorName()
    {
        return $this->authorName;
    }

    /**
     * @param mixed $author
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
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
     * @param mixed $publicationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
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
    public function postId()
    {
        return $this->postId;
    }

    /**
     * @param mixed $postId
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
    }

}