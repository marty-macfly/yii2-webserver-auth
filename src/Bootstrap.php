<?php

namespace macfly\nginxauth;

use Yii;
use yii\web\Application as WebApplication;

class Bootstrap implements \yii\base\BootstrapInterface
{
    /** @inheritdoc */
    public function bootstrap($app)
    {
        if ($app instanceof WebApplication && $app->has('user')) {
            $user = $app->getUser();
            $user->on($user::AFTER_LOGIN, ['macfly\nginxauth\events\AuthEvent', 'setTokenCookie']);
            $user->on($user::AFTER_LOGOUT, ['macfly\nginxauth\events\AuthEvent', 'unsetTokenCookie']);
        }
    }
}
