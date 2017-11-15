<?php

namespace macfly\nginxauth\events;

use Yii;

use macfly\nginxauth\Module;

class AuthEvent
{
    public static function setTokenCookie($event)
    {
        if (($module = Module::getMe(Yii::$app)) !== null) {
            if (Module::getToken() !== null) {
                Yii::info('Token already set');
                return;
            }

            # Get main domain to set cookie
            $domain = Yii::$app->request->getHostName();
            if (($ldot = strrpos($domain, '.')) !== false && ($sdot = strrpos($domain, '.', -1 * (strlen($domain) - $ldot + 1))) !== false) {
                $domain = substr($domain, $sdot + 1);
            }

            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'domain' => $domain,
                'name'   => $module->cookie_token_name,
                'value'  => Yii::$app->user->identity->getAuthKey(),
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
