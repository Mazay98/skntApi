<?php
class Users
{
    /**
     * Обновление тарифов пользователя
     * @param object $db  подключениие к DB
     * @param integer $user_id int Id рользователя
     * @param integer $service_id int Id сервис
     * @return boolean
     */
    public static function updateTarif($db, $user_id, $service_id, $tarif_id)
    {
        $tarif = self::getTarif($db, $user_id, $service_id, $tarif_id);

        $sql = '
            UPDATE services 
            SET tarif_id = :tarif_id,  payday = :pay_day
            WHERE ID = :id AND user_id = :user_id
        ';
        $stmt = $db->prepare($sql);

        $date = new DateTime(date('Y-m-d').'-00'.date("O"));
        $date->modify('+'.$tarif['pay_period'].' month');
        $date = $date->format('Y-m-d');


        $stmt->execute([
            "id"=> $service_id,
            'tarif_id'=> $tarif['ID'],
            'user_id'=> $user_id,
            'pay_day'=> $date
        ]);

        return true? $stmt->rowCount() : false;

    }
    /**
     * Получить пользоватей
     * @param object $db  подключениие к DB
     * @param integer $user_id int Id рользователя
     * @param integer $service_id int Id сервис
     * @return mixed
     */
    public static function getTarifs($db, $user_id, $service_id)
    {
        $gropTarifId=self::getTarif($db, $user_id, $service_id);
        if ($gropTarifId){
            $sql= "
                SELECT tar.id,tar.title,tar.price,tar.pay_period,ser.payday,tar.speed,tar.link
                FROM tarifs tar
                INNER JOIN services ser ON ser.user_id = ? AND ser.id = ?
                WHERE tar.tarif_group_id = ?
             ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id, $service_id, $gropTarifId['tarif_group_id']]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
        }

        return null;
    }
    /**
     * Получить пользователя
     * @param object $db  подключениие к DB
     * @param integer $user_id int Id рользователя
     * @param integer $service_id int Id сервис
     * @param integer $tarif_id Id тарифа
     * @return mixed
     */
    public static function getTarif($db, $user_id, $service_id, $tarif_id='')
    {
        $sql= "
            SELECT tar.ID, tar.title, tar.link, tar.speed, tar.pay_period, tar.tarif_group_id
            FROM tarifs tar
            INNER JOIN services ser ON ser.user_id = ? AND ser.id = ?
            WHERE tar.ID = ser.tarif_id
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id, $service_id]);

        if(!empty($tarif_id)){
            $sql= "
                SELECT tar.ID, tar.title, tar.link, tar.speed, tar.pay_period, tar.tarif_group_id
                FROM tarifs tar
                INNER JOIN services ser ON ser.user_id = ? AND ser.id = ?
                WHERE tar.ID = ?
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user_id, $service_id, $tarif_id]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }

}