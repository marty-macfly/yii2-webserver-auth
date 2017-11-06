<?php

namespace macfly\nginxauth\controllers;

use Yii;
use app\models\User;

class NginxController extends \yii\rest\Controller
{
    public function actionAuth()
    {
      if(User::findIdentityByAccessToken(Yii::$app->request->headers->get('X-SSO-TOKEN'))
      ||User::findIdentityByAccessToken(Yii::$app->request->headers->get('Authorization'))){
        return Yii::$app->response->statusCode = 200;
      }
      elseif(empty(Yii::$app->request->headers->get('Browser'))){ //if request not from browser
        throw new \yii\web\ForbiddenHttpException;
      }
      else {
        throw new \yii\web\UnauthorizedHttpException;
      }
    }
}
