<?php

use http\Client\Curl\User;

require_once 'App/api/api.php';
require_once 'App/core/db.php';
require_once 'App/api/model/UserModel.php';

class UsersApi extends Api
{
    public $apiName = 'users';


    /**
     * Метод GET
     * http://ДОМЕН/users/{user_id}/services/{service_id}/tarifs
     * @return string
     */
    public function viewAction()
    {
        $params = $this->getParamsId();

        if ($this->requestUri[1] != 'services' || $this->requestUri[3] != 'tarifs' ) {
            $this->response(404);
            throw new RuntimeException("Naming error");
        }

        $db = (new Db())->getConnect();
        $curentTarif = Users::getTarif($db, $params->user_id, $params->service_id);

        if (!$curentTarif) {
            $this->response(404);
            throw new RuntimeException('Current Tarif NotFound');
        }

        try{
            $tarifs = Users::getTarifs($db, $params->user_id, $params->service_id);
            $tarifsJSON = [];

            foreach ($tarifs as  $tarif) {

                $date = new DateTime(date('Y-m-d').'-00');
                $date->modify('+'.$tarif['pay_period'].' month');
                $date = (string)strtotime($date->format('Y-m-d-H'));
                $date = $date.date("O");

                $tarifsJSON[]=[
                    'ID'=>$tarif['id'],
                    'title'=>$tarif['title'],
                    'price'=>$tarif['price'],
                    'pay_period'=>$tarif['pay_period'],
                    'new_payday'=>$date,
                    'speed'=>$tarif['speed']
                ];

            }

            $data = [
                "result"=>"ok",
                "tarifs"=>[
                    "title"=>$curentTarif['title'],
                    "link"=>$curentTarif['link'],
                    "speed"=>$curentTarif['speed'],
                    "tarifs"=>$tarifsJSON
                ]
            ];

            return $this->response(200,$data);
        }catch (Exception $e){
            $this->response(404);
            throw new RuntimeException($e);
        }


    }

    /**
     * Метод PUT
     * http://ДОМЕН/users/{user_id}/services/{service_id}/tarif
     * @return string
     */
    public function updateAction()
    {
        $params = $this->getParamsId();

        if ($this->requestUri[1] != 'services' || $this->requestUri[3] != 'tarif' ) {
            $this->response(404);
            throw new RuntimeException("Naming error");
        }

        $db = (new Db())->getConnect();
        if (Users::updateTarif($db, $params->user_id, $params->service_id)){
            return $this->response(20,["result"=> "ok"]);
        } else {
            $this->response(404);
            throw new RuntimeException();
        }
    }

    /**
     * Получить id параметров с их валидацией
     * @return object
    */
    private function getParamsId()
    {
        if (count($this->requestUri) != 4){
            $this->response(404);
            throw new RuntimeException('Invalid Number Arguments');
        }


        $user_id = (int)$this->requestUri[0];
        $service_id = (int)$this->requestUri[2];

        if (!is_int($user_id) || !is_int($service_id)) {
            $this->response(404);
            throw new RuntimeException('Type Error');
        }

        if(!$user_id || !$service_id){
             $this->response( 404);
            throw new RuntimeException('Empty or Zero Arguments');
        }

        return (object)['user_id'=>$user_id,'service_id'=>$service_id];
    }

}