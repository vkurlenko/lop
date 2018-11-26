<?php

namespace app\controllers;

use Yii;
use app\models\Data;
use app\models\DataSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
//use Da\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;
use yii\filters\AccessControl;

/**
 * DataController implements the CRUD actions for Data model.
 */
class DataController extends AppController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }



    /**
     * Lists all Data models.
     * @return mixed
     */
    public function actionIndex()
    {
        //debug(Yii::$aliases);
        $searchModel = new DataSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Data model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Data model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Data();

        $model->discount = 0;
        $model->activation_date = date('Y-m-d');

        if ($model->load(Yii::$app->request->post()) /*&& $model->save()*/) {

            $check = self::check($model);

            if($check && $model->save())
                return $this->redirect(['view', 'id' => $model->id]);
        }
        else{
            // сформируем номер новой карты клиента
            $next_card = Data::find()->max('card') + 1;
            $model->card = $next_card;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }


    /**
     * проверка на создание дубликата записи по имени, email и телефону
     */
    public static function check($model)
    {
        $flag = true;
        $records = Data::find()->select('user_name, phone, email')->asArray()->all();

        foreach($records as $record){
            if(self::convertString($model->user_name) == self::convertString($record['user_name'])){
                Yii::$app->session->addFlash('danger', 'Клиент с именем <em><b>' . $record['user_name'] . '</b></em> уже существует');
                $flag = false;
                break;
            }

            if(self::convertString($model->phone) == self::convertString($record['phone'])){
                Yii::$app->session->addFlash('danger', 'Клиент с телефоном <em><b>' . $record['phone'] . '</b></em> уже существует');
                $flag = false;
                break;
            }

            if(self::convertString($model->email) == self::convertString($record['email'])){
                Yii::$app->session->addFlash('danger', 'Клиент с email <em><b>' . $record['email'] . '</b></em> уже существует');
                $flag = false;
                break;
            }
        }
        return $flag;
    }


    public static function convertString($string)
    {
        return str_replace(' ', '', mb_strtolower($string));
    }

    public static function setCardNumber()
    {
        $arr = Data::find()->where(['card' => 0])->asArray()->all();

        if(!empty($arr)){
            $max = Data::find()->max('card');
            $max++;
            echo $max;
            foreach($arr as $row){
                $q = Data::findOne($row['id']);
                $q->card = $max++;
                $q->save(false);
            }
        }

        debug($arr);
    }

    /**
     * Updates an existing Data model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->send_status = 0;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Data model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    // генерация QR-кода номера карты
    public function actionQr($string = null)
    {
        $string = Yii::$app->request->get('string');

        $str = sprintf("%'013d\n", $string); // . ' Скидка 50%';

        $qrCode = new QrCode($str);

        //$qrCode->setWriterByName('png');
        $qrCode->setSize(252);
        $qrCode->setMargin(10);
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrCode->setValidateResult(false);
        $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH);

        $qrCode->writeFile('./qr/qrcode.png');

        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
    }

    // отправка QR-кода номера карты на mail клиента
    public function actionSendqrcode($id = null)
    {
        $send = false;
        $subject = 'Ваш уникальный QR-код для участия в программе лояльности Lion Of Porches';

        if(Yii::$app->request->get('id'))
            $id = Yii::$app->request->get('id');

        if($id){
            $user = self::findModel($id);

            if($user){
                if($user->email){
                    $params = [
                        'user_name' => $user->user_name,
                        'card' => $user->card,
                        'imageFileName' => './qr/qrcode.png'
                    ];

                    $message = Yii::$app->mailer->compose('mail-html', $params);

                    //$message->attach('/path/to/source/file.pdf');

                    $send = $message
                        ->setTo($user->email)
                        ->setBcc('vkurlenko@yandex.ru')
                        ->setFrom('lofporches@yandex.ru')
                        ->setSubject($subject)
                        //->setTextBody('Текст сообщения')
                        //->attach('./passes/sample.pkpass')
                        ->send();
                }
            }
        }

        if($send)
            Yii::$app->session->addFlash('success', 'QR-код отправлен');
        else
            Yii::$app->session->addFlash('danger', 'Ошибка отправки QR-кода');

        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Data model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Data the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Data::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    // конвертация даты из dd.mm.yyyy -> yyyy-mm-dd
    public static function convertDate($field){
        $arr = Data::find()->asArray()->all();

        foreach($arr as $str => $v){
            $date = $v[$field];
            //$date = $v['activation_date'];

            if(trim($date) != ''){
                $d = explode('.', $date);
                if(!empty($d) && count($d) == 3){
                    $new_date = $d[2].'-'.$d[1].'-'.$d[0];
                    $q = Data::findOne($v['id']);
                    $q->$field = $new_date;
                    $q->save(false);
                }
            }
        }
    }

    // конвертация gender из м/ж -> 1/0
    public static function convertGender(){
        $arr = Data::find()->asArray()->all();

        foreach($arr as $str => $v){
            $gender = $v['gender'];
            $q = Data::findOne($v['id']);

            echo $gender;

            $gender == 'м' ? $q->gender = '1' : $q->gender = '0';

            $q->save(false);
        }
    }

    // получим возраст клиента по его дате рождения
    public static function getAge($birthday){
        $birthday_timestamp = strtotime($birthday);
        $age = date('Y') - date('Y', $birthday_timestamp);
        if (date('md', $birthday_timestamp) > date('md')) {
            $age--;
        }
        return $age;
    }
}
