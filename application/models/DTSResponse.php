<?php

class Application_Model_DTSResponse
{
public function getParticipantInfo($ParticipantID){
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$stmt = $db->prepare("call PARTICIPANT_ONE(?)");
	$stmt->execute(array($ParticipantID));
	$rs = $stmt->fetch();
	return $rs;
}

public function getDTSResponse($ShipmentID,$ParticipantID){
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$stmt = $db->prepare("call RESPONSE_DTS_ONE(?,?)");
	$stmt->execute(array($ParticipantID,$ShipmentID));
	$rowCount = $stmt->rowCount();
	if($rowCount > 0){
	$rs = $stmt->fetchAll();
	return $rs;
	}
	else return false;	
}
public function getDTSShipment($ShipmentID,$ParticipantID){
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$stmt = $db->prepare("call SHIPMENT_ONE(?,?)");
	$stmt->execute(array($ParticipantID,$ShipmentID));
	$rs = $stmt->fetch();
	return $rs;
}
public function IsgetDTSResponseEditable($evaluationStatus){
	return true;
}

public function getAllTestKit(){

	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$stmt = $db->prepare("call TESTKITNAME_ALL_DTS()");
	$stmt->execute();
	$stmt = $stmt->fetchAll();
	
	foreach($stmt as $kitName){
		$retval[$kitName['TESTKITNAMEID']] = $kitName['TESTKITNAME'];	
	}
	return $retval;
}

public function getPossibleResult($testCode,$subgroup){
	$db = Zend_Db_Table_Abstract::getDefaultAdapter();
	$stmt = $db->prepare("call POSSIBLE_TESTRESULT(?,?)");
			$stmt->execute(array($testCode,$subgroup));
			$stmt = $stmt->fetchAll();

			foreach($stmt as $var){
			$retval[$var['RESULT']] = $var['RES_VALUE'];
			}
			return $retval;
	}
	
	public function saveResponse($data){
		date_default_timezone_set('America/New_York');
		// Save all Shipment Information
		// Shipment what we have ?
		//Receipt Date, Testing Date, Rehydration Date
		// Supervisor Review / NAme
		// Comments
		//echo $data['receipt_date']; 
		//echo "<>br";
		
		// Get all Shipment variables 
		
		//Zend_Debug::dump($data);
		$shipId = $data['hdshipId'];
		$participantId = $data['hdparticipantId'];
		
		$date = DateTime::createFromFormat('j-M-yy', $data['receipt_date']);
		$receiptDate = $date->format('Y-m-d');
		
		$date = DateTime::createFromFormat('j-M-yy', $data['hdLastDate']);
		$lastDate = $date->format('Y-m-d');
		
		
		
		$date = DateTime::createFromFormat('j-M-yy', $data['test_date']);
		$testDate = $date->format('Y-m-d');
		
		$date = DateTime::createFromFormat('j-M-yy', $data['reh_date']);
		$rehydrationDate = $date->format('Y-m-d');
		
		$supervisorApproval =$data['supervisorApproval'];
		$participantSupervisor = $data['participantSupervisor'];
		$userCommnets = $data['userCommnets'];
		
		$evaStatus1 = $data['hdeID'];
		
		$evaStatus = $this->newEvalStatus($evaStatus1,$lastDate,'1');
		
			
		$authNameSpace = new Zend_Session_Namespace('Zend_Auth');
		$userId = $authNameSpace->UserID;
		
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$db->beginTransaction();
		try{

			$dtsResponseDb = new Application_Model_DTSResponse();
			$rec = $dtsResponseDb->getDTSResponse($shipId,$participantId);
			//echo "No of Rec = " . count($rec); echo "<br>";
			$noOfSamples = count($rec);
					
			
			$stmt = $db->prepare("call SHIPMENT_UPDATE_DTS(?,?,?,?,?,?,?,?,?,?)");
			/*
			echo 'Save Shipment<br>';
			//echo $shipId . ' ' . $participantId . ' ' . $receiptDate . ' ' . $testDate . ' ' . $rehydrationDate . ' ' . $supervisorApproval . ' ' . $participantSupervisor . ' ' . $userCommnets . ' ' . $userId;
			print_r(array($participantId, $shipId, $evaStatus,$receiptDate,$testDate,$rehydrationDate,$supervisorApproval,$participantSupervisor,$userCommnets,$userId));
			*/
			$stmt->execute(array($participantId, $shipId, $evaStatus,$receiptDate,$testDate,$rehydrationDate,$supervisorApproval,$participantSupervisor,$userCommnets,$userId));
			
			//Now Save Shipment Results
	
			for($i=1; $i<= $noOfSamples;$i++){

			
			//echo "Counter=" . $i;
			//echo "<br>"; 
				
			$DTSSampleID = $data[$i . '_hdSampleId'];
			
			// Test 1
			$testKit1 = $data['testkit1'];
			$lotNo1 = $data['lot_no1'];
			
			$date = DateTime::createFromFormat('j-M-yy', $data['exp_date1']);
			$expDate1 = $date->format('Y-m-d');
			
			$testResult1 = $data[$i . '_testresult1'];
			
			
			// Test 2
			$testKit2 = $data['testkit2'];
			$lotNo2 = $data['lot_no2'];
			
			$date = DateTime::createFromFormat('j-M-yy', $data['exp_date2']);
			$expDate2 = $date->format('Y-m-d');
			
			
			$testResult2 = $data[$i . '_testresult2'];
				
			// Test 2
			$testKit3 = $data['testkit3'];
			$lotNo3 = $data['lot_no3'];
			
			$date = DateTime::createFromFormat('j-M-yy', $data['exp_date3']);
			$expDate3 = $date->format('Y-m-d');
				
			
			$testResult3 = $data[$i . '_testresult3'];
			
			$rptResult =  $data['testresultf_' . $i];
			
			//$userId = $userId;
			
			$stmtSample = $db->prepare("call RESPONSE_RESULT_DTS_UPDATE(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
			/*
			print_r(array($participantId,$shipId,$DTSSampleID,$testKit1,$lotNo1,$expDate1,$testResult1, $testKit2,$lotNo2,$expDate2,$testResult2,	$testKit3,$lotNo3,$expDate3,$testResult3,
					$rptResult,$userId));
			*/
			
			$stmtSample->execute(array(
					$participantId,$shipId,$DTSSampleID,$testKit1,$lotNo1,$expDate1,$testResult1, $testKit2,$lotNo2,$expDate2,$testResult2,	$testKit3,$lotNo3,$expDate3,$testResult3,
					$rptResult,$userId ));
					
			
		}
		$db->commit();
		}
		catch (exception $e) {
			echo "error";
			echo $e->getMessage();
			$db->rollBack();
			die;
			return false;
		}
		//$date = DateTime::createFromFormat('j-M-Y', '1 5-Feb-2009');
		//echo $date->format('Y-m-d');
		
		//if ($retvalue ==false) return false; else return true; 
		//Zend_Debug::dump($data);
		return true;
	}	
	
	private function newEvalStatus($currEvalStatus,$lastDate,$updateUser){
		/*
		 * When Saving				
	Save as	
	C	1		
	D	Check last date and update it.	1 = TIMELY 2 LATE	
	E	Update based as Webuser		
	F	1		
    A 0
    B 1
    C 2
    D 3
    E 4
    F 5
    G 6
		 */
		

	$out = substr_replace($currEvalStatus,'1',2,1); // C = 1 - Reported
	if 	($lastDate <= time()){
		$out = substr_replace($out,'1',3,1); // on time  D = 1 
		$out = substr_replace($out,'1',5,1); // Valid for evaluation F = 1
	}
		else {
			$out = substr_replace($out,'2',3,1); // Late response D = 2
			$out = substr_replace($out,'9',5,1); // Not avalaiuble for response F = 9
		}
		return $out;
	} 
}

