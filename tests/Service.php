<?php

namespace Workup\StateMachine\Test;

class Service
{
    public function guardOnSubmitting(Article $article)
    {
    }

    public static function guardApproval()
    {
        return true;
    }
}
