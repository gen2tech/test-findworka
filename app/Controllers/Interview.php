<?php

namespace App\Controllers;

use Inf\Router\RouterRequest;

class Interview extends Controller{

	function __construct(){
		parent::__construct();
	}
	
	
	public function getBooks(){
		$page = RouterRequest::getData('page');
		$perPage = RouterRequest::getData('perPage');

		$data =  $this->helper->getBooks($page,$perPage);
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

	
	public function getSortedCharacters($sort,$order = 'asc'){		
		$page = RouterRequest::getData('page');
		$perPage = RouterRequest::getData('perPage');

		$sortArr = ['name','age','gender'];	
		if(!in_array(strtolower($sort),$sortArr)){
			return $this->process(['error'=>'Please use keyword "name" or "age" or "gender" to sort characters'],203)->send();
		}	
		$data =  $this->helper->getCharacters($page,$perPage);

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
		$page = RouterRequest::getData('page');
		$perPage = RouterRequest::getData('perPage');

		$filterArr = ['Male','Female'];	
		if(!in_array(ucfirst($filter),$filterArr)){
			return $this->process(['error'=>'Please use keyword "male" or "female" to filter characters by gender'],203)->send();
		}	
		$data =  $this->helper->getCharacters($page,$perPage);

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


	public function saveBookComment(){
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
		$sql = "SELECT * FROM `comments` WHERE `book_id` =  $bookId ORDER BY `date` DESC ";
		$data = $this->db->fetchAllRows($sql);

		if($data){			
			return $this->process(['status' => 1, 'txt' => 'Comments are successfully fetched', 'comments' => $data ])->send();
		}else{
			return $this->process(['status' => 0, 'txt' => 'No comments for the book at the moment'],203)->send();
		}
	}








	
	public function getDocs(){
		echo <<<EOT

		<!DOCTYPE html>

		<html>
		<head>
			<meta charset='utf-8' />
			<meta name='viewport' content='width=device-width initial-scale=1.0' />
			<title>Documentation</title>
		
			
			
				<link rel='stylesheet' href='https://anapioficeandfire.com/css/site.min.css?v=eIS6NSLEwfxH5Mo7EENjrOw2OWwiez91uGTs-q0ErTY' />
				<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' />
		<meta name='x-stylesheet-fallback-test' content='' class='sr-only' />
			<style>code{color:blue}</style>
		</head>
		<body>
			<div data-sticky-container class='sticky-container'>
				<header id='site-header' class='sticky' style='position: fixed;'>
					<div class='row align-middle'>
						<div class='large-12 columns'>
							<div class='top-bar' id='nav-menu'>
							<h3>Rest API enpoints Documentation</h3>
		
							</div>
						</div>
					</div>
				</header>
			</div>
			<main id='main' style='margin-top: 82px;'>
				
		
		<div id='layout-main-container' class='row'>
			<div id='layout-content' class='large-12 columns'>
				<section id='content' class='row'>
					<div class='large-12 columns'>
						<article>
							<header id='page-header'>
								<h1 class='page-title'>Documentation</h1>
							</header>
							<div class='row'>
								
								<div class='small-12 medium-8 column'>
									<hr />
									<div>
										<a name='intro'></a>
										<h4>Introduction</h4>
										<p>This documentation is made on how to use the rest API enpoints created using "An API of Ice And Fire "https://anapioficeandfire.com" and how to consume the different resources that are available.</p>
									</div>
									<div>
										<a name='pagination'></a>
										<h4>Pagination</h4>
										<p>This rest API endpoint provides a lot of data gotten from "https://anapioficeandfire.com". Thus it also uses the paginate and size-per-page features of the data source (ie. "https://anapioficeandfire.com") </p>
		
										<h5>Things worth noting:</h5>
		
										<ol>
											<li>Page numbering is 1-based</li>
											<li>You can specify how many items you want to receive per page, the maximum is 50</li>
										</ol>
		
										<h5>Constructing a request with pagination</h5>
										<p>
											You specify which page you want to access with the <code>?page</code> parameter, if you don't provide the <code>?page</code> parameter the first page will be returned. You can also specify the size of the page with the <code>?perPage</code> parameter, if you don't provide the <code>?perPage</code> parameter the default size of 10 will be used.
										</p>
										
										<hr/>
		
										<h3>To Get Books Data:</h3>
										<p>In order to get Books Data, a request using GET method to any of the following API resource will get the response data in a json Format</p>
										
										<code>
											$ curl http://localhost/interview/get_books<br/>
											$ curl http://localhost/interview/get_books?page=2<br/>
											$ curl http://localhost/interview/get_books?perPage=5<br/>
											$ curl http://localhost/interview/get_books?page=2&perPage=5<br/>
										</code>
										
										<p>Book names in the book list endpoint are sorted by release date from earliest to newest and each book are listed along with authors </p>
										<p>Count of comments for each books are given provided that comment(s) has been previously saved into sqlite Database for the book using book ID </p>
										<p>Note: book ID is taken from the last segment of the book URL eg. in "https://www.anapioficeandfire.com/api/books/1" book ID is 1</p>
										<p>Error responses are returned in case of errors</p>
										
									</div>
									<h3>Resources</h3>
									<hr />
									<div>
		
											<h5>List all books:</h5>
											<code>$ curl 'http://localhost/interview/get_books?page=1&perPage=3'</code>
											<p>
												<strong>Example response:</strong>
											</p>
											<pre><code>[
			{
				"released": "2000-10-31 00:00:00",
				"id": "3",
				"name": "A Storm of Swords",
				"authors": [
					"George R. R. Martin"
				],
				"isbn": "978-0553106633",
				"numberOfPages": 992,
				"publisher": "Bantam Books",
				"country": "United States",
				"mediaType": "Hardcover",
				"comments_count": 1
			},
			{
				"released": "1999-02-02 00:00:00",
				"id": "2",
				"name": "A Clash of Kings",
				"authors": [
					"George R. R. Martin"
				],
				"isbn": "978-0553108033",
				"numberOfPages": 768,
				"publisher": "Bantam Books",
				"country": "United States",
				"mediaType": "Hardback",
				"comments_count": 2
			},
			{
				"released": "1996-07-31 23:00:00",
				"id": "1",
				"name": "A Game of Thrones",
				"authors": [
					"George R. R. Martin"
				],
				"isbn": "978-0553103540",
				"numberOfPages": 694,
				"publisher": "Bantam Books",
				"country": "United States",
				"mediaType": "Hardcover",
				"comments_count": 3
			}
		  ...
		 ]</code></pre>
		 
										<p></p>
										<hr/>
										<div>
										<h3>To Get Characters Data:</h3>
										<p>In order to get Characters Data, a request using GET method to any of the following API resource will get the response data in a json Format</p>
										<ol>
											<li>Endpoint accepts sort parameters which sort by one of name, gender or age both in ascending or descending order.</li>
		
											<li>Endpoint also accepts a filter parameter to filter by gender.</li>
		
											<li>The response also return metadata that contains the total number of characters with property 'character_count' it matches the criteria.</li>
		
											<li>Note: The total age of the characters that matches the criteria was not provided. The sole reason is because the property for the date of birth from 'https://anapioficeandfire.com' (which is 'born'), returns something like 'In 283 AC'. Which is before 1970. </li>
										</ol>
										
										<h4>For Sorted Characters</h4>
										<p>For Sorted Characters base API URL is http://localhost/interview/get_sorted_characters/{sortWith} Where sortWith is any of the keywords (name,gender,age). Note that the sortWith is required
										</p>
										<p>The next segment in MVC URL for getting sorted characters is the 'order' which can be any of 'asc' or 'desc'. Default asc</p>
										<p>Then they query strings as stated above defines how many characters per page? (ie.perPage) then what page?(ie.page)</p>
										<p>Note: character ID is taken from the last segment of the book URL eg. in "https://www.anapioficeandfire.com/api/character/1" book ID is 1</p>
										
										<h4>Example API URL</h4>
										<code>
											$ curl http://localhost/interview/get_sorted_characters/name?page=2<br/>
											$ curl http://localhost/interview/get_sorted_characters/name/desc?page=2<br/>
											$ curl http://localhost/interview/get_sorted_characters/gender/desc?page=2<br/>
											$ curl http://localhost/interview/get_sorted_characters/name?page=2&perPage=5<br/>
											$ curl http://localhost/interview/get_sorted_characters/name/desc?page=2&perPage=5<br/>
											$ curl http://localhost/interview/get_sorted_characters/gender/desc?page=2&perPage=5<br/>
										</code>
										
										
										<h4>For Filtered Characters</h4>
										<p>For Filtered Characters base API URL is http://localhost/interview/get_filtered_characters/{filterWith} Where filterWith is any of the keywords (male or famale). Note that the filterWith is required</p>
										<p>Then they query strings as stated above defines how many characters per page? (ie.perPage) then what page?(ie.page)</p>
										<h4>Example API URL</h4>
										<code>
											$ curl http://localhost/interview/get_filtered_characters/male?page=2<br/>
											$ curl http://localhost/interview/get_filtered_characters/female?page=2<br/>
											$ curl http://localhost/interview/get_filtered_characters/male?page=2&perPage=5<br/>
											$ curl http://localhost/interview/get_filtered_characters/female?page=2&perPage=5<br/>
										</code>
										
										</div> 
											<p></p>
											
											
											<hr/>
										<div>
										<h3>To Get Comments:</h3>
										<p>In order to get Comments or add comment, a request using GET method or POST method (for getting comments and adding comments repectively) to any of the following API resource will get the response data in a json Format</p>
										
										<p>For getting comments, a GET request is sent to <code>http://localhost/interview/get_book_comments/{book_id}</code> where 'book_id' is the ID of the book a commenter commented on</p>
										
										<h5>Notes on Comments Retrieval</h5>
										<p>
											Comment list are retrieved in reverse chronological order.<br/>
											Comments are retrieved along with the public IP address of the commenter with the property 'ip_address'.<br/>
											Comments are retrieved along with the UTC date&time they were stored with property 'date'.<br/>
											Comment length are limited to 500 characters
										</p>
										
										<p>For adding comments, a POST request is sent to <code>http://localhost/interview/save_book_comment</code> posting with the following parameters</p>
										
										<ol>
											<li>{book_id} => The ID of the book a commenter wants to comment on</li>
											<li>{commenter} => The Commenter's name</li>
											<li>{comment} => The comment on the book (Which should not be more than 500 characters)</li>
										</ol>
										</div> 
											<p></p>
											
										<hr/>
										<div>
										<h3>Features no done</h3>
										<p>In getting characters, the total age of the characters that matches the criteria was not provided. The sole reason is because the property for the date of birth from 'https://anapioficeandfire.com' (which is 'born'), returns something like 'In 283 AC'. Which is before 1970 </p>
										</div> 
											<p></p>
											
											
											
										<hr/>
										<div>
										<h3>Error 404</h3>
										<p>if it is ajax request, a json data stating page not found will be returned</p>
										<p>if its from Browser a simple page will show page not found</p>
										</div> 
											<p></p>
											
											
									</div>
								</div>
							</div>
						</article>
					</div>
				</section>
			</div>
		</div>
			</main>
			<div id='footer-container'>
				<footer id='site-footer' class='bg-gray'>
					<div class='row align-middle'>
						<div class='large-12 columns'>
							<div class='row'>
								<div class='small-12 medium-5 column credits'>
									<span class='copyright medium-12 column'>&copy; 2020 - By Ogunyemi Oludayo as a documentation to Interview Question</span>
								</div>
							</div>
						</div>
					</div>
		
				</footer>
			</div>
		</body>
		</html>
EOT;
	}
}