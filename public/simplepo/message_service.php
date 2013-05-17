<?php
// A really simple json RPC interface
require_once 'env_fix.php';
require_once 'config.php';
require_once 'DB.php';

$rpc = new JSON_RPC(new MessageService());
echo $rpc->getResponse($_POST["request"]);

class JSON_RPC {
	
	protected $service;
	
	function __construct($obj) {
		$this->service = $obj;
	}
	function getResponse($request_string) {
		$request = json_decode($request_string,true);
		$response = array('error'=>null);

		if($request['id'])
			$response['id'] = $request['id'];

		if(method_exists($this->service,$request['method'])) {
			try {
				$r = call_user_func_array(array($this->service, $request['method']), $request['params']);
				$response['result'] = $r;
			} catch (Exception $e) {
				$response['error'] = array('code' => -31000,'message' => $e->getMessage());
			}
		} else {
			$response['error'] = array('code' => -32601,'message' => 'Procedure not found.');
		}

		return json_encode($response);
	}
}

class MessageService {
	function __construct() {
	}
	
	/**
	 * setSuggestionTitle
	 * 
	 * @author Dominik Matt <dma@massiveart.com>
	 */
	function getSuggestionLanguage()
	{
		return MaConfig::getSuggestionLanguage();
	}
	
	function getMessages($id = null) {
	  if($id == null) {
	  	$id = MaConfig::getRefCatalogueId();
	  }
      $q = new Query();
      $messages = $q->sql("SELECT * 
        FROM {messages} 
        WHERE catalogue_id=? AND is_header <> 1 
        ORDER BY msgstr != '', flags != 'fuzzy' ", $id)
        ->fetchAll();
			
			foreach($messages as &$m) {
				$m['fuzzy'] = strpos($m['flags'],'fuzzy') !== FALSE;
				$m['is_obsolete'] = !!$m['is_obsolete'];
			}
			return $messages;
	}
    function getCatalogues(){
      $q = new Query();
      return $q->sql("SELECT c.name,c.id,COUNT(*) as message_count, 
														 SUM(LENGTH(m.msgstr) >0) as translated_count
														 
											FROM {catalogues} c
											LEFT JOIN {messages} m ON m.catalogue_id=c.id
											GROUP BY c.id")->fetchAll();
    }
    function updateMessage($id, $comments, $msgstr, $fuzzy){
      $q = new Query();
			$flags = $fuzzy ? 'fuzzy' : '';
      $q->sql("UPDATE {messages} SET comments=?, msgstr=?, flags=? WHERE id=?", $comments, $msgstr, $flags, $id)->execute();
      echo "true";
    }
	function makeError() {
		throw new Exception("This is an error");
	}
}

