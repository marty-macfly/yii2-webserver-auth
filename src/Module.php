<?php

namespace macfly\nginxauth;

use Yii;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
    public $cookie_token_name   = 'x-sso-token';
    public $return_url          = 'return_url';

    public static function getMe($app)
    {
        foreach ($app->getModules() as $id => $mod) {
            if (is_array($mod) && (ArrayHelper::getValue($mod, 'class') == self::className() || ArrayHelper::getValue($mod, 0) == self::className())) {
                return $app->getModule($id);
            } elseif (is_object($mod) && is_a($mod, self::className())) {
                return $mod;
            }
        }

        return null;
    }

    public static function getToken()
    {
        $cookie_token_name = ArrayHelper::getValue(self::getMe(Yii::$app), 'cookie_token_name');
        
        if ($cookie_token_name === null) {
            return null;
        }

        $user  = Yii::$app->user;
        $token = null;

        if ($user->enableAutoLogin && ($token = Yii::$app->request->cookies->getValue(ArrayHelper::getValue($user->identityCookie, 'name', '_identity'))) !== null) {
            // Check if cookie-based login is use and identityCookie exist
            Yii::info(sprintf("Cookie name '%s' found", $user->identityCookie));
        } elseif (($token = Yii::$app->request->cookies->getValue($cookie_token_name)) !== null) {
            // Check if our own cookie exist
            Yii::info(sprintf("Cookie name '%s' found", $cookie_token_name));
        } elseif (($token = Yii::$app->request->headers->get('x-sso-token')) !== null) {
            // Check if the header exist
            Yii::info(sprintf("Header name '%s' found", $cookie_token_name));
        }

        return $token;
    }
}
