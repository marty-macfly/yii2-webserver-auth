<?php

namespace macfly\nginxauth\events;

use Yii;

use macfly\nginxauth\Module;

class AuthEvent
{
    public static function setTokenCookie($event)
    {
        if (($module = Module::getMe(Yii::$app)) !== null) {
            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'name' => $module->cookie_token_name,
                'value' => Yii::$app->user->identity->getAuthKey(),
            ]));
        } else {
            Yii::error('Module macfly\nginxauth\Module not loaded');
        }
    }

    public static function unsetTokenCookie($event)
    {
        if (($module = Module::getMe(Yii::$app)) !== null) {
            Yii::$app->response->cookies->remove($module->cookie_token_name);
        } else {
            Yii::error('Module macfly\nginxauth\Module not loaded');
        }
    }
}
