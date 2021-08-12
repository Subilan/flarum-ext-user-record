<?php

namespace SotapMc\PostRecords;

use Flarum\Database\AbstractModel;
use Flarum\Post\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Parsedown;

class UserPostRecords extends AbstractModel
{
    protected $table = 'user_post_records';

    public function createRecord(string $username, $post, string $type)
    {
        $tg = $this->check($post->id);
        if ($tg == null) {
            $post = $type == 'comment' ?  $post : $post->firstPost;
            $this->username = $username;
            $this->chwords = self::getChWords($post->content);
            $this->enwords = self::getEnWords($post->content);
            $this->post_id = $post->id;
            $this->type = $type;
            $this->save();
        }
    }

    public function updateRecord(Post $post)
    {
        $tg = $this->check($post->id);
        if ($tg != null) self::query()->where("post_id", $post->id)->update([
            "chwords" => self::getChWords($post->content),
            "enwords" => self::getEnWords($post->content),
            "type" => $post->type
        ]);
    }

    public function increaseLike(int $id, int $increasement = 1)
    {
        $tg = $this->check($id);
        if ($tg != null) self::query()->where("post_id", $id)->update(["likes" => $tg->likes + $increasement]);
    }

    public function decreaseLike(int $id, int $decreasement = 1)
    {
        $tg = $this->check($id);
        if ($tg != null) self::query()->where("post_id", $id)->update(["likes" => max($tg->likes - $decreasement, 0)]);
    }

    public function deleteRecordById(int $id)
    {
        $tg = $this->check($id);
        if ($tg != null) self::query()->where("post_id", $id)->delete();
    }

    public function check($id)
    {
        try {
            if ($id == null) return null;
            return self::query()->where("post_id", $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    public static function strip(string $str)
    {
        $p = new Parsedown();
        $str = $p->text($str);
        $regex = [
            // [upl-file] [/upl-file]
            "/\s?\[upl-file.*?\[\/upl-file\]\s?/",
            // emoji and emoticons
            "/[\x{1F600}-\x{1F64F}]/u",
            "/[\x{1F300}-\x{1F5FF}]/u",
            "/[\x{1F680}-\x{1F6FF}]/u",
            // latex
            "/\s?\\$\\$?\s?.*?\s?\\$?\\$/"
        ];
        foreach ($regex as $r) {
            $str = preg_replace($r, "", $str);
        }
        $str = strip_tags($str);
        return $str;
    }

    public static function getChWords($input)
    {
        return preg_match_all("/[\x{4E00}-\x{9FA5}]/u", $input);
    }

    public static function getEnWords($input)
    {
        $words = str_word_count(self::strip($input), 1);
        $finalWords = [];
        foreach ($words as $w) {
            if (strlen($w) > 2) {
                array_push($finalWords, $w);
            }
        }
        return count($finalWords);
    }
}
