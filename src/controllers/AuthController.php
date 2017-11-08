<?php

namespace macfly\nginxauth\controllers;

use Yii;

class AuthController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) { // User has not been authenticated yet
            $token = null;

            if (Yii::$app->user->enableAutoLogin && ($token = Yii::$app->request->cookies->getValue(Yii::$app->user->identityCookie)) !== null) {
                // Check if cookie-based login is use and identityCookie exist
                Yii::info(sprintf("Cookie name '%s' found", Yii::$app->user->identityCookie));
            } elseif (($token = Yii::$app->request->cookies->getValue($this->module->cookie_token_name)) !== null) {
                // Check if our own cookie exist
                Yii::info(sprintf("Cookie name '%s' found", $this->module->cookie_token_name));
            } elseif (($token = Yii::$app->request->headers->get('x-sso-token')) !== null) {
                // Check if the header exist
                Yii::info(sprintf("Header name '%s' found", $this->module->cookie_token_name));
            }

            if ($token !== null) {
                Yii::$app->user->loginByAccessToken($token);
            }
        }

        if (Yii::$app->user->isGuest == false) {
            // Check if the user has one of the permissions provided
            if (Yii::$app->user->getAccessChecker() !== null && ($permissions = Yii::$app->request->get('permission')) !== null) {
                $permissions = is_array($permissions) ? $permissions : [$permissions];
                foreach ($permissions as $permission) {
                    if (Yii::$app->user->can($permission)) {
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
