<?php

namespace Workup\StateMachine\Test;

use Illuminate\Foundation\Auth\User;

class ArticlePolicy
{
    public function submitChanges(User $user, Article $article, $string)
    {
        return $string === 'foo';
    }
}
