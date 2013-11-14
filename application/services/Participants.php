<?php

class Application_Service_Participants {
	
	public function getUsersParticipants($userSystemId = null){
		if($userSystemId == null){
			$authNameSpace = new Zend_Session_Namespace('datamanagers');
			$userSystemId = $authNameSpace->dm_id;
		}
		
		$participantDb = new Application_Model_DbTable_Participants();
		return $participantDb->getParticipantsByUserSystemId($userSystemId);
		
	}
	
	public function getParticipantDetails($partSysId){
		
		$participantDb = new Application_Model_DbTable_Participants();
		return $participantDb->getParticipant($partSysId);
		
	}
	
	public function addParticipant($params){
		$participantDb = new Application_Model_DbTable_Participants();
		return $participantDb->addParticipant($params);
    }
	public function updateParticipant($params){
		$participantDb = new Application_Model_DbTable_Participants();
		return $participantDb->updateParticipant($params);
	}
	public function getAllParticipants($params){
		$participantDb = new Application_Model_DbTable_Participants();
		return $participantDb->getAllParticipants($params);
	}
	
	public function getAllEnrollments($params){
		$enrollments = new Application_Model_DbTable_Enrollments();
		return $enrollments->getAllEnrollments($params);
	}
	public function getEnrollmentDetails($pid,$sid){
	    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
	    $sql = $db->select()->from(array('p'=>'participant'))
				  ->joinLeft(array('sp'=>'shipment_participant_map'),'p.participant_id=sp.participant_id')
				  ->joinLeft(array('s'=>'shipment'),'s.shipment_id=sp.shipment_id')
				  ->where("p.participant_id=".$pid);
	    return $db->fetchAll($sql);
	}
	public function getUnEnrolled($scheme){
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$subSql = $db->select()->from(array('e'=>'enrollments'), 'participant_id')->where("scheme_id = ?", $scheme);
		$sql = $db->select()->from(array('p'=>'participant'))->where("participant_id NOT IN ?", $subSql);
		return $db->fetchAll($sql);
	}
	public function getEnrolledBySchemeCode($scheme){
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$sql = $db->select()->from(array('e'=>'enrollments'), array())
							   ->join(array('p'=>'participant'),"p.participant_id=e.participant_id")->where("scheme_id = ?", $scheme);
		return $db->fetchAll($sql);
	}
	public function getEnrolledByShipmentId($shipmentId){
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$sql = $db->select()->from(array('p'=>'participant'))
				       ->join(array('sp'=>'shipment_participant_map'),'sp.participant_id=p.participant_id',array())
					   ->join(array('s'=>'shipment'),'sp.shipment_id=s.shipment_id',array())
				       ->where("s.shipment_id = ?", $shipmentId);

		return $db->fetchAll($sql);
	}
	public function getUnEnrolledByShipmentId($shipmentId){
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$subSql = $db->select()->from(array('p'=>'participant'),array('participant_id'))
				       ->join(array('sp'=>'shipment_participant_map'),'sp.participant_id=p.participant_id',array())
					   ->join(array('s'=>'shipment'),'sp.shipment_id=s.shipment_id',array())
				       ->where("s.shipment_id = ?", $shipmentId);
		$sql = $db->select()->from(array('p'=>'participant'))->where("participant_id NOT IN ?", $subSql);
		return $db->fetchAll($sql);
	}
	
	public function enrollParticipants($params){
		$enrollments = new Application_Model_DbTable_Enrollments();
		return $enrollments->enrollParticipants($params);
	}

}
