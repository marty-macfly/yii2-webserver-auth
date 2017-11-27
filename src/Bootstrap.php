<?php

namespace macfly\yii\webserver;

use Yii;
use yii\web\Application as WebApplication;

use macfly\nginxauth\events\AuthEvent;

class Bootstrap implements \yii\base\BootstrapInterface
{
    /** @inheritdoc */
    public function bootstrap($app)
    {
        if ($app instanceof WebApplication && $app->has('user')) {
            $user = $app->getUser();
            $user->on($user::EVENT_AFTER_LOGIN, ['macfly\yii\webserver\events\AuthEvent', 'redirectAfterLogin']);
            $user->on($user::EVENT_AFTER_LOGOUT, ['macfly\yii\webserver\events\AuthEvent', 'unsetTokenCookie']);

            AuthEvent::setTokenCookie(null);
        }
    }
}
