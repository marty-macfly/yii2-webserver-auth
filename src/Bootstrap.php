<?php

namespace macfly\nginxauth;

use Yii;

class Bootstrap implements \yii\base\BootstrapInterface
{
    /** @inheritdoc */
    public function bootstrap($app)
    {
        if ($app->hasModule('nginx') && ($module = $app->getModule('nginx')) instanceof Module)
        {
            if ($app instanceof ConsoleApplication)
            {
                $module->controllerNamespace = 'macfly\nginxauth\commands';
            } else
            {
                $configUrlRule = [
                    'class'		=> 'yii\web\GroupUrlRule',
                    'prefix'    => $module->urlPrefix,
                    'rules'		=> $module->urlRules,
                ];

                if ($module->urlPrefix != 'api')
                {
                    $configUrlRule['routePrefix'] = 'api';
                }

                $rule	= Yii::createObject($configUrlRule);

                $app->urlManager->addRules([$rule], false);
            }
        }
    }
}
