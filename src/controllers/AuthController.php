<?php

namespace macfly\yii\webserver\controllers;

use Yii;
use yii\helpers\ArrayHelper;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use macfly\yii\filters\auth\CookieAuth;

class AuthController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                'basicAuth' => [
                    'class' => HttpBasicAuth::className(),
/*
                    'auth' => function ($username, $password) {
                        $user = User::find()->where(['username' => $username])->one();
                        if ($user->verifyPassword($password)) {
                            return $user;
                        }
                        return null;
                    },
*/
                ],
                HttpBearerAuth::className(),
                'cookieAuth' => [
                    'class' => CookieAuth::className(),
                    'cookieName' => $this->module->token_name,
                ],
            ],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        $user = Yii::$app->user;

/*
        if ($user->isGuest) { // User has not been authenticated yet
            if (($token = Yii::$app->controller->module->getToken()) !== null && $user->loginByAccessToken($token) === null) {
                Yii::error(sprintf("Failed to loginByAccessToken with token: %s", $token));
            }
        }
*/
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
