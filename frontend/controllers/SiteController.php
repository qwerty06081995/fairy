<?php

namespace app\controllers;

use app\modules\story\models\StoryForm;
use app\modules\story\models\StoryHistory;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\grid\GridView;
use yii\i18n\Locale;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    public string $story_api_url = 'http://localhost:8000/generate_story';
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
                $response = Yii::$app->httpClient->post($this->story_api_url, [
                    'json' => [
                        'age' => $model->age,
                        'language' => $model->language,
                        'characters' => $model->characters
                    ]
                ]);

                $storyText = $response->getBody()->getContents();

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
