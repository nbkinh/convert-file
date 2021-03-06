<?php

namespace app\controllers;

use app\models\UploadForm;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use yii\web\UploadedFile;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstance($model, 'file');
            if ($model->upload()) {

                $content = @file_get_contents($model->path);

                $actualContent = $this->getActualContent($content);

                unlink($model->path);

                header("Content-type: text/plain");
                header("Content-Disposition: attachment; filename=result.txt");
                echo json_encode($actualContent);
                die;
            }
        }

        return $this->render('index', ['model' => $model]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page update comment.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    private function getActualContent($content)
    {
        $result = [];
        if (empty($content)) {
            return $result;
        }

        $arr = explode('|', $content);

        $newContent = '';
        foreach ($arr as $a) {
            if (strpos($a, 'presence=') !== false) {
                $newContent = $a;
                break;
            }
        }

        if (empty($newContent)) {
            return $result;
        }

        $arrSemicolons = explode(';', $newContent);
        foreach ($arrSemicolons as $contentSemicolon) {
            $arrEqual = explode('=', $contentSemicolon);
            if (count($arrEqual) >= 1) {
                $result[] = [
                    'Name' => $arrEqual[0],
                    'Value' => isset($arrEqual[1]) ? $arrEqual[1] : '',
                    'Path' => '/',
                    'Secure' => false,
                    'HttpOnly' => false,
                    'Domain' => '.facebook.com',
                    'Url' => 'https://www.facebook.com',
                    'Expires' => '',
                ];
            }
        }

        return $result;
    }
}
