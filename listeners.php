<?php

require "src\UserPostRecordsModel.php";

use Flarum\Likes\Event\PostWasLiked;
use Flarum\Likes\Event\PostWasUnliked;
use Flarum\Post\Event\Deleted;
use Flarum\Post\Event\Posted;
use Flarum\Post\Event\Hidden;
use Flarum\Post\Event\Restored;
use Flarum\Post\Event\Saving;
use Flarum\Discussion\Event\Started;
use Symfony\Contracts\Translation\TranslatorInterface;
use Subilan\PostRecords\UserPostRecords;

class PostEventListener
{
    protected $translator;
    protected $r;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->r = new UserPostRecords();
    }

    public function subscribe($events)
    {
        $events->listen(Deleted::class, [$this, 'deleted']);
        $events->listen(Hidden::class, [$this, 'deleted']);
        $events->listen(Posted::class, [$this, 'posted']);
        $events->listen(Started::class, [$this, 'discussionStarted']);
        $events->listen(Restored::class, [$this, 'posted']);
        $events->listen(Saving::class, [$this, 'saved']);
        $events->listen(PostWasLiked::class, [$this, 'liked']);
        $events->listen(PostWasUnliked::class, [$this, 'unliked']);
    }

    public function deleted($ev)
    {
        $this->r->deleteRecordById($ev->post->id);
    }

    public function saved($ev) {
        $this->r->updateRecord($ev->post);
    }

    public function liked(PostWasLiked $ev)
    {
        $this->r->increaseLike($ev->post->id);
    }

    public function unliked(PostWasUnliked $ev)
    {
        $this->r->decreaseLike($ev->post->id);
    }

    public function posted($ev)
    {
        $this->r->createRecord($ev->actor->username, $ev->post, 'comment');
    }

    public function discussionStarted(Started $ev) {
        $this->r->createRecord($ev->actor->username, $ev->discussion, 'discussion');
    }
}
