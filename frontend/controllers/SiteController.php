<?php

namespace app\controllers;

use app\modules\story\models\StoryForm;
use app\modules\story\models\StoryHistory;
use yii\httpclient\Client;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public string $story_api_url = 'http://127.0.0.1:8000/generate_story';
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
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
                'class' => VerbFilter::class,
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
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionStory(): string
    {

        $dataProvider = new ActiveDataProvider([
            'query' => StoryHistory::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('story/index', [
            'dataProvider'=>$dataProvider
        ]);
    }
    public function actionForm()
    {
        $model = new StoryForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            //  Заголовки — ТОЛЬКО через PHP
            header('Content-Type: text/markdown; charset=utf-8');
            header('Cache-Control: no-cache');
            header('X-Accel-Buffering: no');

            // Отключаем буферы
            while (ob_get_level() > 0) {
                ob_end_flush();
            }
            ob_implicit_flush(true);

            $ch = curl_init($this->story_api_url);

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'age'        => $model->age,
                    'language'   => $model->language,
                    'genre'      => $this->getGenreName($model->genre),
                    'characters' => $this->getCharacterName($model->characters),
                ], JSON_UNESCAPED_UNICODE),
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_WRITEFUNCTION => function ($ch, $data) {
                    echo $data;
                    flush();
                    return strlen($data);
                },
            ]);

            curl_exec($ch);
            curl_close($ch);

            // Просто выходим, без Yii::$app->end()
            exit;
        }

        return $this->render('story/form', [
            'model' => $model,
        ]);
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
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    private function getGenreName($id){
        $genres = [
            'Приключения',
            'Фэнтези',
            'Волшебная сказка',
            'Комедия',
            'Драма',
            'Сказка о животных',
            'Семейная сказка',
            'Поучительная сказка',
            'Детектив',
            'Путешествие'
        ];
        return $genres[intval($id)];
    }

    private function getCharacterName($ids){
        $arr = ['Заяц','Волк','Лиса','Алдар Көсе','Әйел Арстан'];
        $res = [];
        for ($i=0;$i<count($ids);$i++){
            $res[$i] = $arr[$ids[$i]];
        }
        return $res;
    }

}
