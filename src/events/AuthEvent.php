<?php

namespace macfly\nginxauth\events;

use Yii;
use yii\helpers\ArrayHelper;

class AuthEvent
{
    protected static $moduleClass = 'macfly\nginxauth\Module';

    protected static function getModule()
    {
        $module = null;

        foreach (Yii::$app->getModules() as $id => $mod) {
            if (is_array($mod) && (ArrayHelper::getValue($mod, 'class') == self::$moduleClass || ArrayHelper::getValue($mod, 0) == self::$moduleClass)) {
                $module = Yii::$app->getModule($id);
                break;
            } elseif (is_object($mod) && is_a($mod, self::$moduleClass)) {
                $module = $mod;
                break;
            }
        }

        return $module;
    }

    public static function setTokenCookie($event)
    {
        $module = self::getModule();

        Yii::$app->response->cookies->add(new \yii\web\Cookie([
            'name' => $module->cookie_token_name,
            'value' => Yii::$app->user->identity->accessToken,
        ]));
    }


    public static function unsetTokenCookie($event)
    {
        $module = self::getModule();
        Yii::$app->response->cookies->remove($module->cookie_token_name);
    }
}
