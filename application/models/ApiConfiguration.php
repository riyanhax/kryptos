<?php 

class Application_Model_ApiConfiguration extends Muzyka_DataModel
{

	protected $_name  = "apiconfiguration";


   public function saveAction($data)
   {
   		
   	       if(isset($data['hiddenval']))
            {
                $row = $this->getFull([
                      'id' => $data['hiddenval']
                    ], true);
                echo 'first';
            }
            else
            {
               $row = $this->createRow();
               
            }

        if($data['hiddenval'] =='1')
        {
	   		$row->apiurl = $data['emailapiurl'];
	   		$row->accesskey = $data['emailapi'];
	   		$row->username = $data['emailusername'];
	   		$row->password = $data['emailpassword'];
	   		
        }
        else
        {
   		$row->apiurl = $data['smsapiurl'];
   		$row->accesskey = $data['smsapi'];
   		$row->username = $data['smsusername'];
   		$row->password = $data['smspassword'];
   		
   	    }
   	    // print_r($row);
   	    // die();
         return $row->save();
   }

   public function getApiconfigAction($id){

   	$data = "SELECT * FROM `apiconfiguration` where id= $id ";
	$result = $this->_db->query($data)->fetchAll(PDO::FETCH_ASSOC);
	return $result;
   	  
   }

    public function getApidataAction()
    {
    	$apidata = "SELECT * FROM `apiconfiguration`";
		$result = $this->_db->query($apidata)->fetchAll(PDO::FETCH_ASSOC);
		return $result;
    }
}



?>