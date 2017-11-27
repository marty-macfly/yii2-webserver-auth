<?php

namespace macfly\yii\webserver\controllers;

use Yii;
use yii\web\Response;
use yii\web\MethodNotAllowedHttpException;

class DefaultController extends \yii\rest\Controller
{
    public function init()
    {
        parent::init();

        if (Yii::$app->has('user')) {
            Yii::$app->user->enableSession = false;
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    public function actionIndex()
    {
        throw new MethodNotAllowedHttpException();
    }
}
