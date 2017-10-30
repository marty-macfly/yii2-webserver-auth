<?php

namespace macfly\nginxauth\events;

use Yii;

class NginxAuthEvent
{
    public static function setTokenOnCookie()
    {
        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => 'app_sso_token',
            'value' => Yii::$app->user->identity->accessToken,
        ]));
    }

    public static function unsetTokenOnCookie()
    {
        Yii::$app->response->cookies->remove('app_sso_token');
    }
}
