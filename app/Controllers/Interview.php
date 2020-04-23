<?php

namespace App\Controllers;

use Inf\Router\RouterRequest;

class Interview extends Controller{

	function __construct(){
		parent::__construct();
	}
	
	
	public function getBooks(){
		$data =  $this->helper->getBooks();
		if($data){
			return $this->process($data)->send();
		}else{
			return $this->process($data,204,'text')->send();
		}
	}

	
	public function getBook($bookId){		
		$data =  $this->helper->getBook($bookId);
		if($data){
			return $this->process($data)->send();
		}else{
			return $this->process($data,204,'text')->send();
		}
	}

	
	public function getCharacters($sort,$order = 'asc'){
		$sortArr = ['name','age','gender'];	
		if(!in_array(strtolower($sort),$sortArr)){
			return $this->process(['error'=>'Please use keyword "name" or "age" or "gender" to sort characters'],203)->send();
		}	
		$data =  $this->helper->getCharacters();

		// work on the charachers
		$characters = $data['characters'];

		// Perform sort
		if(in_array(strtolower($sort),$sortArr)){
			usort($characters, function ($x, $y) use($sort) {
				return strcasecmp($x[strtolower($sort)], $y[strtolower($sort)]);
			});
		}

		// Perform Ordering if Descending
		$characters = array_values($characters);
		if(\strtolower($order)==='desc'){
			krsort($characters);
			$characters = array_values($characters);
		}

		$data['characters'] = $characters;

		if($data){
			return $this->process($data)->send();
		}else{
			return $this->process($data,204,'text')->send();
		}
	}

	public function getFilteredCharacters($filter){
		$filterArr = ['Male','Female'];	
		if(!in_array(ucfirst($filter),$filterArr)){
			return $this->process(['error'=>'Please use keyword "male" or "female" to filter characters by gender'],203)->send();
		}	
		$data =  $this->helper->getCharacters();

		// work on the charachers
		$characters = $data['characters'];
		// Perform filter
		if(in_array(ucfirst($filter),$filterArr)){
			$characters = $this->helper->arrayFilterByValue($characters,'gender',ucfirst($filter));
		}
		$data['character_count'] = count($characters);
		$data['characters'] = $characters;

		if($data){
			return $this->process($data)->send();
		}else{
			return $this->process($data,204,'text')->send();
		}
	}


	public function addBookComment(){
		$bookId = RouterRequest::postData('book_id');
		$commenter = RouterRequest::postData('commenter', true);
		$comment = RouterRequest::postData('comment', true);
		$error = [];
		if(empty($bookId)){
			$error[] = "Please Select the book You are writting comment on";
		}else if(empty($comment)){
			$error[] = "Comment cannot be emptied";
		}else if(strlen($comment) > 500){
			$error[] = "Comment should not be more than 500 characters";
		}

		if(!empty($error)){
			return $this->process(['errors'=>$error],203)->send();
		}else{
			date_default_timezone_set("UTC");
			$data = [
				'book_id'=>$bookId,
				'commenter'=>$commenter,
				'comment'=>$comment,
				'ip_address'=>RouterRequest::ip_address(),
				'date'=>date("Y-d-mTG:i:sz", time()),
			];
			date_default_timezone_get();
			$submit = $this->db->insert('comments', $data );
			if($submit){			
				return $this->process(['status' => 1, 'txt' => 'A comment has been added to book','newId'=>$this->db->lastid()])->send();
			}else{
				return $this->process(['status' => 0, 'txt' => 'Unable to add comment at the moment'],203)->send();
			}
		}
	}


	public function getBookComments($bookId){
		$sql = "SELECT * FROM `comments` WHERE `book_id` =  $bookId ";
		$data = $this->db->fetchAllRows($sql);

		if($data){			
			return $this->process(['status' => 1, 'txt' => 'Comments are successfully fetched', 'comments' => $data ])->send();
		}else{
			return $this->process(['status' => 0, 'txt' => 'No comments for the book at the moment'],203)->send();
		}
	}
}