<?php
namespace App;

use Inf\Router\RouterRequest;
use App\Sqlite;
class Helper{

	private $db;
	
	public function __construct(){
		$this->db = Sqlite::getInstance(DB_FILE);
	}

	public function getDB(){
		return $this->db;
	}

   /**
     *
     *
     * This Method is used to get the Api Data from https://anapioficeandfire.com/api/
     *
     * @param array $middlewares
     *
     * @return void
     */		
   public function getApiData($endPoint){
		$baseUrl = "https://anapioficeandfire.com/api/";
		$url = $baseUrl.$endPoint;   
		$apiGet = file_get_contents($url);
		$data = json_decode($apiGet,true);
		return $data;
   }


	public function getBooks($page=null, $perPage=50,$addCharacters=false){
		$endpont = 'books';
		if(!empty($page)){
			$endpont .= "?page=$page&pageSize=$perPage";
		}else{
			$endpont .= "?pageSize=$perPage";
		}
		$list = $this->getApiData($endpont);

		$data = [];
		foreach($list AS $book){
			$date = new \DateTime($book['released']);
			$date->setTimezone(new \DateTimeZone('Africa/Lagos')); // +01
			$released = $date->format('Y-m-d H:i:s'); // 2012-07-15 05:00:00 
			$key = strtotime($released);

			$data[$key]['released'] = $released;

			$urlArr = explode('/',$book['url']);
			$bookId = end($urlArr); 
			$data[$key]['id'] = $bookId;
			$data[$key]['name'] = $book['name'];
			$data[$key]['authors'] = $book['authors'];
			$data[$key]['isbn'] = $book['isbn'];
			$data[$key]['numberOfPages'] = $book['numberOfPages'];
			$data[$key]['publisher'] = $book['publisher'];
			$data[$key]['country'] = $book['country'];
			$data[$key]['mediaType'] = $book['mediaType'];
			$data[$key]['comments_count'] = $this->getBookCommentCount($bookId);
			if(True === $addCharacters){
				$data[$key]['characters'] = $book['characters'];
			}
			 
		}

		krsort($data);

		//return $list;
		return array_values($data);
	}


	public function getBook($bookId, $addCharacters=false){
		$book = $this->getApiData('books/'.$bookId);

		$date = new \DateTime($book['released']);
		$date->setTimezone(new \DateTimeZone('Africa/Lagos')); // +01
		$released = $date->format('Y-m-d H:i:s'); // 2012-07-15 05:00:00

		$data['released'] = $released;

		$urlArr = explode('/',$book['url']);
		$bookId = end($urlArr); 
		$data['id'] = $bookId;
		$data['name'] = $book['name'];
		$data['authors'] = $book['authors'];
		$data['isbn'] = $book['isbn'];
		$data['numberOfPages'] = $book['numberOfPages'];
		$data['publisher'] = $book['publisher'];
		$data['country'] = $book['country'];
		$data['mediaType'] = $book['mediaType'];
		if(True === $addCharacters){
			$data['characters'] = $book['characters'];
		}
		$data['comments_count'] = $this->getBookCommentCount($bookId);
		//$data['comments'] = $this->getComments($bookId);
		return $data;
	}


	


	public function getCharacters($page=null, $perPage=50){
		$endpont = 'characters';
		if(!empty($page)){
			$endpont .= "?page=$page&pageSize=$perPage";
		}else{
			$endpont .= "?pageSize=$perPage";
		}
		$list = $this->getApiData($endpont);
		
		$info = $data = [];
		$info['character_count'] = count($list);
		foreach($list AS $character){
			$urlArr = explode('/',$character['url']);
			$characterId = end($urlArr); 
			$char['id'] = $characterId;
			$char['name'] = $character['name'];
			$char['age'] = $character['born'];
			$char['gender'] = $character['gender'];
			$data[] = $char;
		}
		$info['characters'] = $data;
		//return $list;
		return $info;
	}




	public function arrayFilterByValue($array, $index, $value)
    { 
		$new_array = [];
        if(is_array($array) && count($array)>0)  
        { 
			$temp = [];
            foreach(array_keys($array) as $key){ 
                $temp[$key] = $array[$key][$index]; 
                 
                if ($temp[$key] == $value){ 
                    $new_array[$key] = $array[$key]; 
                } 
            } 
          } 
      return $new_array; 
	} 
	

	

	public function getBookCommentCount($bookId){
		$sql = "SELECT COUNT(*) AS `count` FROM `comments` WHERE `book_id` =  $bookId ORDER BY `date` DESC ";
		$data = $this->db->fetchOneRow($sql);
		return $data['count'];
	}



}