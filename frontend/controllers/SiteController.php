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

    public function actionForm(): string
    {
        $model = new StoryForm();
        $storyText = null;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            try {
                $client = new Client(); // создаём клиент
                $response = $client->post($this->story_api_url, [
                    'age' => $model->age,
                    'language' => $model->language,
                    'characters' => $model->characters
                ])
                    ->setFormat(Client::FORMAT_JSON) // важно для JSON
                    ->send();

                if ($response->isOk) {
                    // Получаем тело как строку, без автоматического парсинга
                    $raw = $response->getContent();
                    // Найти позицию ",done_reason"
                    $pos = strpos($raw, 'done_reason');

                    if ($pos !== false) {
                        $raw = substr($raw, 0, $pos); // обрезаем всё после
                    }

                    // Чистим лишние куски Ollama
                    $storyText = preg_replace('/\\\"|,\\s*\"done\":(true|false)/u', '', $raw);

                    // Убираем лишние кавычки и экранирование
                    $storyText = str_replace(['\"', '"'], '', $storyText);

                    // Заменяем "\n" на реальные переносы строк
                    $storyText = str_replace(['\\n', '\n'], "\n", $storyText);

                    // Если есть лишние пробелы после переносов
                    $storyText = preg_replace("/\s+\n/", "\n", $storyText);

                    $storyText = strip_tags($storyText); // на всякий случай убираем HTML теги

                } else {
                    throw new \Exception('Python-сервис вернул ошибку: '.$response->statusCode);
                }

                // Сохраняем историю
                $history = new StoryHistory();
                $history->age = $model->age;
                $history->language = $model->language;
                $history->characters = json_encode($model->characters);
                $history->story_text = $storyText;
                $history->created_at = date('Y-m-d H:i:s');
                $history->save();

            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Python-сервис недоступен: '.$e->getMessage());
            }
        }

        return $this->render('story/form', [
            'model' => $model,
            'storyText' => $storyText
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

}
