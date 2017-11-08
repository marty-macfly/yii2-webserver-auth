<?php

namespace macfly\nginxauth\controllers;

use Yii;
use yii\helpers\ArrayHelper;

class AuthController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        $user = Yii::$app->user;
        if ($user->isGuest) { // User has not been authenticated yet
            $token = null;

            if ($user->enableAutoLogin && ($token = Yii::$app->request->cookies->getValue(ArrayHelper::getValue($user->identityCookie, 'name', '_identity'))) !== null) {
                // Check if cookie-based login is use and identityCookie exist
                Yii::info(sprintf("Cookie name '%s' found", $user->identityCookie));
            } elseif (($token = Yii::$app->request->cookies->getValue($this->module->cookie_token_name)) !== null) {
                // Check if our own cookie exist
                Yii::info(sprintf("Cookie name '%s' found", $this->module->cookie_token_name));
            } elseif (($token = Yii::$app->request->headers->get('x-sso-token')) !== null) {
                // Check if the header exist
                Yii::info(sprintf("Header name '%s' found", $this->module->cookie_token_name));
            }

            if ($token !== null) {
                $user->loginByAccessToken($token);
            }
        }

        if ($user->isGuest == false) {
            // Check if the user has one of the permissions provided
            if (($permissions = Yii::$app->request->get('permission')) !== null
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
