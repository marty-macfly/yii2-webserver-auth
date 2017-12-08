<?php

namespace macfly\yii\webserver\events;

use Yii;
use yii\helpers\ArrayHelper;
use yii\validators\IpValidator;

use macfly\yii\webserver\Module;

class AuthEvent
{
    public static function sendCookie($value, $expire = 0)
    {
        if (($module = Module::getMe(Yii::$app)) !== null) {
            # Get main domain to set cookie
            $domain = Yii::$app->request->getHostName();
            $validator = new IpValidator();
            if (!$validator->validate($domain) && ($ldot = strrpos($domain, '.')) !== false && ($sdot = strrpos($domain, '.', -1 * (strlen($domain) - $ldot + 1))) !== false) {
                $domain = substr($domain, $sdot + 1);
            }

            Yii::$app->response->cookies->add(new \yii\web\Cookie([
                'domain' => $domain,
                'name'   => $module->token_name,
                'value'  => $value,
                'expire' => $expire,
            ]));
            Yii::info(sprintf("Set cookie to domain: '%s', name: '%s', value: '%s', expire: ''%s'", $domain, $module->token_name, $value, $expire));
        } else {
            Yii::error('Module macfly\yii\webserver\Module not loaded');
        }
    }

    public static function setTokenCookie($event)
    {
        if (($module = Module::getMe(Yii::$app)) === null) {
            Yii::error('Module macfly\yii\webserver\Module not loaded');
            return;
        }

        if (($token = Yii::$app->request->cookies->getValue($module->token_name)) !== null) {
            // Check if our own cookie exist
            Yii::info(sprintf("Cookie name '%s' found", $module->token_name));
        }

        if (Yii::$app->user->isGuest) {
            Yii::info('User not logged in');
            return;
        }

        self::sendCookie(ArrayHelper::getValue(Yii::$app->user->identity, 'accessToken'));
    }

    public static function unsetTokenCookie($event)
    {
        self::sendCookie('deleted', time() - 86400);
    }

    public static function redirectAfterLogin()
    {
        if (($module = Module::getMe(Yii::$app)) !== null) {
            AuthEvent::setTokenCookie(null);

            if ($module->return_url !== null && ($url = Yii::$app->request->get($module->return_url)) !== null) {
                Yii::trace(sprintf("Parameter '%s' found after login user will be redirect to '%s'", $module->return_url, $url));
                Yii::$app->user->setReturnUrl($url);
            }
        }
    }
}
