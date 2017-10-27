<?php

namespace macfly\nginxauth\controllers;

use Yii;
use app\models\User;

class NginxController extends \yii\rest\Controller
{
    public function actionAuth()
    {
        if(User::findIdentityByAccessToken(Yii::$app->request->headers->get('X-SSO-TOKEN'))){
          return Yii::$app->response->statusCode = 200;
        }
        else {
          throw new \yii\web\UnauthorizedHttpException;
        }
    }
}
