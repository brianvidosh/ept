<?php

class Application_Model_DbTable_ShipmentParticipantMap extends Zend_Db_Table_Abstract
{

    protected $_name = 'shipment_participant_map';
    protected $_primary = 'map_id';

    public function shipItNow($params){
        try{
            $this->getAdapter()->beginTransaction();
            $authNameSpace = new Zend_Session_Namespace('Zend_Auth');
            foreach($params['participants'] as $participant){
                $data = array('shipment_id'=>$params['shipmentId'],
                              'participant_id'=>$participant,
                              'created_by_admin' => $authNameSpace->admin_id,
                              "created_on_admin"=>new Zend_Db_Expr('now()'));
                $this->insert($data);
            }
            $this->getAdapter()->commit();
            return true;            
        }catch(Exception $e){
            $this->getAdapter()->rollBack();
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return false;
        }
    }

}

