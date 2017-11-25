<?php

namespace macfly\nginxauth;

use Yii;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
    public $token_name   = 'x-sso-token';
    public $return_url   = 'return_url';

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
        $token_name = ArrayHelper::getValue(self::getMe(Yii::$app), 'token_name');

        if ($token_name === null) {
            return null;
        }

        $user  = Yii::$app->user;
        $token = null;

        if ($user->enableAutoLogin && ($token = Yii::$app->request->cookies->getValue(ArrayHelper::getValue($user->identityCookie, 'name', '_identity'))) !== null) {
            // Check if cookie-based login is use and identityCookie exist
            Yii::info(sprintf("Cookie name '%s' found", $user->identityCookie));
        } elseif (($token = Yii::$app->request->cookies->getValue($token_name)) !== null) {
            // Check if our own cookie exist
            Yii::info(sprintf("Cookie name '%s' found", $token_name));
        } elseif (($token = Yii::$app->request->headers->get('x-sso-token')) !== null) {
            // Check if the header exist
            Yii::info(sprintf("Header name '%s' found", $token_name));
        } elseif (($user = Yii::$app->request->getAuthUser()) === $token_name) {
            Yii::info(sprintf("Header name 'Authorization' found token in password"));
            $token = Yii::$app->request->getAuthPassword();
        } elseif (($password = Yii::$app->request->getAuthPassword()) === $token_name) {
            Yii::info(sprintf("Header name 'Authorization' found token in user"));
            $token = Yii::$app->request->getAuthUser();
        }

        return $token;
    }
}
