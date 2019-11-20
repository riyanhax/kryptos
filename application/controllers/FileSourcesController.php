<?php

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Hug\Ftp\Ftp as Ftp;

class FileSourcesController extends Muzyka_Admin
{
    /** @var Application_Model_FileSources */
    protected $fileSources;
    
    protected $baseUrl = '/file-sources';

    public function init() {
        parent::init();
        $this->view->section = 'Dysk online';
        $this->fileSources = Application_Service_Utilities::getModel('FileSources');

        Zend_Layout::getMvcInstance()->assign('section', 'Dysk online');
    }

    public static function getPermissionsSettings() {
        $baseIssetCheck = [
            'function' => 'issetAccess',
            'params' => ['id','type'],
            'permissions' => [
                1 => ['perm/file-sources/update'],
                2 => ['perm/file-sources/remove']
            ],
        ];

        $settings = [
            'modules' => [
                'file-sources' => [
                    'label' => 'Dysk online',
                    'permissions' => [
                        [
                            'id' => 'remove',
                            'label' => 'Usuwanie wpisów',
                        ],
                        [
                            'id' => 'update',
                            'label' => 'Dodawanie wpisów',
                        ]
                    ],
                ],
            ],
            'nodes' => [
                'file-sources' => [
                    '_default' => [
                        'permissions' => ['user/superadmin'],
                    ],
                    'index' => [
                        'permissions' => ['perm/file-sources'],
                    ],
                    'remove' => [
                        'permissions' => ['perm/file-sources/remove'],
                    ],
                    'update' => [
                        'getPermissions' => array($baseIssetCheck),
                    ]
                ],
            ]
        ];

        return $settings;
    }

    public function indexAction() {
        $paginator = $this->fileSources->getList();
        $this->view->paginator = $paginator;
    }
    
    public function listAction($sourceid = ""){
    	$req = $this->getRequest();
    	$sourceid = $req->getParam('sourceid', 0);
        $sources = $this->fileSources->getList();
        $filelists = array();
        $all = array(
			'id' => 'all',
			'name' => 'All'
		);
		
        foreach($sources as $key => $source){
        	$config = json_decode($source['config'], true);
        	$filelist = array();
        	
        	if($sourceid === 0){
				if($key != 0)  continue;
				else $nowsource = $source;
			}else if($sourceid != 0 && $sourceid != "all"){
				if($source['id'] != $sourceid) continue;
				else $nowsource = $source;
			}else $nowsource = $all;
        	
			if($source['type'] == Application_Model_FileSources::TYPE_FTP){
				$host = $config['host'];
		       	$login = $config['user'];
		       	$password = $config['password'];
				$path = $config['path'];
				
				$ftp = new \FtpClient\FtpClient();
				$ftp->connect($host);
				$ftp->login($login, $password);
				$items = $ftp->scanDir($path);
				$ftp->close();
				
		        foreach ($items as $item){
					$name = explode('.', $item['name']);
					$info['name'] = $name[0];
					$info['ext'] = $name[1];
					$type = $item['type'];
					$info['realname'] = $item['name'];
					if($type == 'file') $info['type'] = 'File'; else $info['type'] = 'Folder';
					$info['date'] = $item['month'].' '.$item['day'].', 2018 '.$item['time'];
					$info['storage'] = $source['name'];
					$info['storage_id'] = $source['id']; 
					$filelist[] = $info;
					reset($info);
				}
				array_push($filelists, $filelist);	
			}
			
			if($source['type'] == Application_Model_FileSources::TYPE_DB){
				$client_id = $config['app_key'];
				$client_secret = $config['app_secret'];
				$access_token = $config['access_token'];
				
				$app = new DropboxApp($client_id, $client_secret, $access_token);
				$dropbox = new Dropbox($app);
				$listFolderContents = $dropbox->listFolder("/");
				$items = $listFolderContents->getItems();
				
				foreach($items as $item){
					$name = explode('.', $item->name);
					$info['name'] = $name[0];
					$info['ext'] = $name[1];
					$info['realname'] = $item->name;
					$time = $item->server_modified;
					$info['date'] = substr($time, 0 ,10).' '.substr($time,11,8);
					if(empty($time)) $info['type'] = 'Folder'; else $info['type'] = 'File';
					$info['storage'] = $source['name'];	
					$info['storage_id'] = $source['id']; 
					$filelist[] = $info;
					reset($info);
				}
				array_push($filelists, $filelist);
			}	
		}	
		array_push($sources,$all);
		$sources = array_reverse($sources);
		$this->view->nowsource = $nowsource;
		$this->view->sources = $sources;
        $this->view->filelists = $filelists;   
    }
    
    //functions that delete folder
    public static function deleteDir($dirPath) {
	    if (! is_dir($dirPath)) {
	        throw new InvalidArgumentException("$dirPath must be a directory");
	    }
	    if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
	        $dirPath .= '/';
	    }
	    $files = glob($dirPath . '*', GLOB_MARK);
	    foreach ($files as $file) {
	        if (is_dir($file)) {
	            self::deleteDir($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($dirPath);
	}
    
    // if you click edit button
    public function storageUpdateAction(){
		$this->setDialogAction();
		$storage_id = $this->getParam('storage_id');
		$filename = $this->getParam('filename');
		$ext = $this->getParam('ext');
		$type = $this->getParam('type');
		
		$storage = $this->fileSources->get($storage_id);
		$sources = $this->fileSources->getList();
		$this->view->sources = $sources;
		$this->view->storage = $storage;
		if(!empty($ext))  $filename = $filename.".".$ext;
		$this->view->filename = $filename;
		$this->view->type = $type;
	}
    
    //if you click move button
    public function moveAction(){	
    	$storage_id = $this->getParam('storageid');
    	$destination_id = $this->getParam('sourceid');
    	$filename = $this->getParam('filename');
    	$type = $this->getParam('type');
    	$download_dir = __DIR__.'/../../web/uploads/';
    	
    	if($storage_id == $destination_id) {
			$this->outputJson([
                'status' => false,
                'api' => [
                    'notification' => 'same',
                ],
            ]);
            return;
		}
    	
		$storage = $this->fileSources->get($storage_id);
		$destination = $this->fileSources->get($destination_id);
		
		$config_sto = json_decode($storage['config'], true);
		$config_des = json_decode($destination['config'], true);
		if($storage['type'] == Application_Model_FileSources::TYPE_DB){
			$client_id = $config_sto['app_key'];
			$client_secret = $config_sto['app_secret'];
			$access_token = $config_sto['access_token'];
			$app = new DropboxApp($client_id, $client_secret, $access_token);
			$dropbox = new Dropbox($app);
			
			if($type == 'File'){
				$file = $dropbox->download("/".$filename, $download_dir.$filename);
			}else{
				$file = $dropbox->download_dir("/".$filename, $download_dir.$filename);
			}
			
			$deletedFolder = $dropbox->delete("/".$filename);

			if($destination['type'] == Application_Model_FileSources::TYPE_FTP){
				$host = $config_des['host'];
		       	$login = $config_des['user'];
		       	$password = $config_des['password'];
				$path = $config_des['path'];
				if($type == 'File'){
					Ftp::upload($host, $login, $password, $download_dir.$filename, $remote_file = $path.'/'.$filename, $port = 21);
					unlink($download_dir.$filename);
				}else{
					$ftp = new \FtpClient\FtpClient();
					$ftp->connect($host);
					$ftp->login($login, $password);
					$ftp->mkdir($path.'/'.$filename);
					$ftp->putAll($download_dir.$filename, $path.'/'.$filename);
					$ftp->close();
					$this->deleteDir($download_dir.$filename);
				}
			}
		}
		if($storage['type'] == Application_Model_FileSources::TYPE_FTP){
			$host = $config_sto['host'];
	       	$login = $config_sto['user'];
	       	$password = $config_sto['password'];
			$path = $config_sto['path'];
			
			if($type == 'File'){
				Ftp::download($host, $login, $password, $path.'/'.$filename, $download_dir.$filename, $port = 21);	
				Ftp::delete($host, $login, $password, $path.'/'.$filename, $port = 21);
			}else {
				$localdir = substr($download_dir,0,strlen($download_dir)-1);
				Ftp::download_dir($host, $login, $password, $path.'/'.$filename, $localdir, $port = 21);
				Ftp::rmdir($host, $login, $password, $path.'/'.$filename, $port = 21);
			}
			
			if($destination['type'] == Application_Model_FileSources::TYPE_DB){
				$client_id = $config_des['app_key'];
				$client_secret = $config_des['app_secret'];
				$access_token = $config_des['access_token'];
				$app = new DropboxApp($client_id, $client_secret, $access_token);
				$dropbox = new Dropbox($app);
				if($type == 'File'){
					$file = $dropbox->upload($download_dir.$filename, "/".$filename, ['autorename' => true]);
					unlink($download_dir.$filename);
				}else{
					$file = $dropbox->upload_dir($download_dir.$filename, "/".$filename, ['autorename' => true]);
					$this->deleteDir($download_dir.$filename);
				}
			}
		}
		if ($this->getRequest()->isXmlHttpRequest()) {
            $this->outputJson([
                'status' => true,
                'api' => [
                    'notification' => 'success',
                ],
            ]);
            return;
        } else {
			$this->redirect($this->baseUrl.'/list');
        }
		
	}
    
    public function removeAction() {
        $req = $this->getRequest();
        if ($id = $req->getParam('id', 0)) {
            $this->fileSources->remove($id);
            $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Wpis usunięto poprawnie.'));
        }

        $this->_redirect($this->baseUrl);
    }
    
    public function updateAction() {
        //echo get_class($this->view); exit;
        Zend_Layout::getMvcInstance()->assign('sectionDetailed', 'Dodaj połączenie');
        $data = array();
        $data['id'] = '';        
        $data['role'] = 3;        
        $data['type'] = $this->getRequest()->getParam('type');        
        $data['host'] = '';
        $data['user'] = '';
        $data['password'] = '';
        $data['path'] = '';
        $this->view->data = $data;
    }
    
    public function saveAction() {
        try {
            $req = $this->getRequest();
            $params = $req->getParams();
            $this->fileSources->save($params);
            $this->_helper->getHelper('flashMessenger')->addMessage($this->showMessage('Zapisano'));
            $this->_redirect('/file-sources');
        } catch (Exception $e) {
            throw new Exception('Proba zapisu danych nie powiodla sie', 500, $e);
        }
    } 
}