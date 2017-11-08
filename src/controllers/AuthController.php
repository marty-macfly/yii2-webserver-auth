<?php

namespace macfly\nginxauth\controllers;

use Yii;

class AuthController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        $token = null;
        $contentType = array_shift(array_keys(Yii::$app->request->acceptableContentTypes));
        $isBrowser = in_array($contentType, ['text/html', 'application/xhtml+xml']);

        if (($token = Yii::$app->request->cookies->getValue($this->module->cookie_token_name)) !== null) {
            Yii::info(sprintf("Cookie name '%s' found", $this->module->cookie_token_name));
        } elseif (($token = Yii::$app->request->headers->get('x-sso-token')) !== null) {
            Yii::info(sprintf("Header name '%s' found", $this->module->cookie_token_name));
        }

        if ($token !== null) {
            if (Yii::$app->user->loginByAccessToken($token)) {
                return Yii::$app->response->statusCode = 200;
            }
        }

        if ($isBrowser) { // Return 401
            throw new \yii\web\UnauthorizedHttpException;
        } else { // Return 403
            throw new \yii\web\ForbiddenHttpException;
        }
    }
}
