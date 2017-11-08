<?php

namespace macfly\nginxauth\controllers;

use Yii;

class AuthController extends \yii\rest\Controller
{
    public function actionIndex()
    {
        $token          = null;
        $contentType    = array_shift(array_keys(Yii::$app->request->acceptableContentTypes));
        $isBrowser      = in_array($contentType, ['text/html', 'application/xhtml+xml']);

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
            if (Yii::$app->user->loginByAccessToken($token)) {
                return Yii::$app->response->statusCode = 200;
            }
        }

        if ($isBrowser) {
            // Return 401 if it's a browser doing the request, so nginx can behave differently (redirect browser)
            throw new \yii\web\UnauthorizedHttpException;
        } else {
            // Return 403, if it's a cli or a bot, nginx can behave differently display message about missing token
            throw new \yii\web\ForbiddenHttpException;
        }
    }
}
