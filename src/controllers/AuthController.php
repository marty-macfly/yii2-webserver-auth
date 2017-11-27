<?php

namespace macfly\nginxauth\controllers;

use Yii;
use yii\helpers\ArrayHelper;

class AuthController extends \yii\rest\Controller
{
    public function init()
    {
        parent::init();

        if (Yii::$app->has('user')) {
            Yii::$app->user->enableSession = false;
        }
    }

    public function actionIndex()
    {
        $user = Yii::$app->user;

        if ($user->isGuest) { // User has not been authenticated yet
            if (($token = Yii::$app->controller->module->getToken()) !== null) {
                $user->loginByAccessToken($token);
            }
        }

        if ($user->isGuest === false) {
            // Check if the user has one of the permissions provided
            if (($permissions = Yii::$app->request->get('permission')) !== null
                && !empty($permissions)
                && (($user->hasProperty('authManager') && $user->authManager !== null)
                    || ($user->hasProperty('accessChecker') && $user->accessChecker !== null))
                ) {
                $permissions = is_array($permissions) ? $permissions : [$permissions];
                foreach ($permissions as $permission) {
                    if ($user->can($permission)) {
                        return Yii::$app->response->statusCode = 200;
                    }
                }
                throw new \yii\web\ForbiddenHttpException;
            } else {
                return Yii::$app->response->statusCode = 200;
            }
        }

        // User is not valid
        throw new \yii\web\UnauthorizedHttpException;
    }
}
