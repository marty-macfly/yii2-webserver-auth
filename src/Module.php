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
}
