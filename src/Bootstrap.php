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
            $user->on($user::EVENT_AFTER_LOGIN, ['macfly\nginxauth\events\AuthEvent', 'setTokenCookie']);
            $user->on($user::EVENT_AFTER_LOGOUT, ['macfly\nginxauth\events\AuthEvent', 'unsetTokenCookie']);

            if (($module = Module::getMe($app)) !== null) {
                if ($module->return_url !== null && ($url = $app->request->get($module->return_url)) !== null) {
                    Yii::trace(sprintf("Parameter '%s' found after login user will be redirect to '%s'", $module->return_url, $url));
                    $user->setReturnUrl($url);
                }
            }
        }
    }
}
