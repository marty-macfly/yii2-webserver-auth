<?php

namespace macfly\nginxauth\events;

use Yii;

use macfly\nginxauth\Module;

class AuthEvent
{
    public static function sendCookie($value, $expire = 0)
    {
        if (($module = Module::getMe(Yii::$app)) !== null) {
            # Get main domain to set cookie
            $domain = Yii::$app->request->getHostName();
            if (($ldot = strrpos($domain, '.')) !== false && ($sdot = strrpos($domain, '.', -1 * (strlen($domain) - $ldot + 1))) !== false) {
                $domain = substr($domain, $sdot + 1);
            }

            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'domain' => $domain,
                'name'   => $module->cookie_token_name,
                'value'  => $value,
                'expire' => $expire,
            ]));
            Yii::info(sprintf("Set cookie to domain: '%s', name: '%s', value: '%s', expire: ''%s'", $domain, $name, $value, $expire));
        } else {
            Yii::error('Module macfly\nginxauth\Module not loaded');
        }
    }

    public static function setTokenCookie($event)
    {
        if (Module::getToken() !== null) {
            Yii::info('Token already set');
            return;
        }

        self::sendCookie(Yii::$app->user->identity->getAuthKey());
    }

    public static function unsetTokenCookie($event)
    {
        self::sendCookie('deleted', time() - 86400);
    }
}
