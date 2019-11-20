<?php

class Application_Model_Dictionary extends Muzyka_DataModel
{

    public function setDictionary()
    {
	     $polish = array();
		 $english = array();


	 $polish_arr = $this->_db->query(sprintf("select value FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE title='polish' and registry_id in (SELECT `id` FROM `registry` WHERE title='Dictionary'))"))->fetchAll(PDO::FETCH_ASSOC);
	     foreach ($polish_arr as $data)
	      {
        		array_push($polish, $data['value']);
	      }

	 $english_arr = $this->_db->query(sprintf("select value FROM registry_entries_entities_varchar where registry_entity_id in(select id from registry_entities WHERE title='english' and registry_id in (SELECT `id` FROM `registry` WHERE title='Dictionary'))"))->fetchAll(PDO::FETCH_ASSOC);
		foreach ($english_arr as $data)
		{
				array_push($english, $data['value']);
		}

	$Dictionary = array_combine($polish, $english);
	$content = '';
	foreach ($Dictionary as $pol => $eng) {
		$content .= $pol.' = "'.$eng.'"'.PHP_EOL;

	}
	 if(file_exists(ROOT_PATH . '/translations/data.ini')){
	 	fopen(ROOT_PATH . '/translations/data.ini', 'w') ;
	 }
	 if(file_exists(ROOT_PATH . '/translations/data2.ini')){
	 	fopen(ROOT_PATH . '/translations/data.ini2', 'w') ;
	 }


		//Save the JSON string to a text file.
		file_put_contents(ROOT_PATH . '/translations/data2.ini', $content);
		rename(ROOT_PATH . '/translations/data.ini', ROOT_PATH . '/translations/_data2.ini');
		rename(ROOT_PATH . '/translations/data2.ini', ROOT_PATH . '/translations/data.ini');
		rename(ROOT_PATH . '/translations/_data2.ini', ROOT_PATH . '/translations/data2.ini');


	//return $Dictionary;
    }

    public function getRegidByName($name)
    {
        $result = $this->_db->query(sprintf("SELECT id FROM `registry` WHERE `title`= '".$name."'"))->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]['id'];
    }
  
}
