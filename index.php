<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
require 'vendor/autoload.php';
require 'config.php';
use Inf\Router;

$dbFile = __DIR__.'/db/sqlite.db';
if(!file_exists($dbFile)){
    touch($dbFile);
}
define('DB_FILE', $dbFile); //display db errors?

$router = new Router([
  'paths' => [
      'controllers' => 'app/Controllers',
  ],
  'namespaces' => [
      'controllers' => 'App\Controllers',
  ],
]);



$router->get('/', 'Interview@getDocs');

// eg. http://localhost/interview/get_books
// http://localhost/interview/get_books?page=2               // Adding Page Number 
// http://localhost/interview/get_books?page=2&perPage=5     // Adding Page Number and Page Size
$router->get('/get_books', 'Interview@getBooks');

// eg. http://localhost/interview/get_book/1
$router->get('/get_book/:id', 'Interview@getBook');

// eg. http://localhost/interview/save_book_comment
$router->post('/save_book_comment', 'Interview@saveBookComment');

// eg. http://localhost/interview/get_book_comments/1
$router->get('/get_book_comments/:id', 'Interview@getBookComments');


// eg. 
// http://localhost/interview/get_sorted_characters/name?page=2 ,
// http://localhost/interview/get_sorted_characters/name/desc?page=2 ,
// http://localhost/interview/get_sorted_characters/gender/desc?page=2 
// http://localhost/interview/get_sorted_characters/name?page=2&perPage=5 ,
// http://localhost/interview/get_sorted_characters/name/desc?page=2&perPage=5 ,
// http://localhost/interview/get_sorted_characters/gender/desc?page=2&perPage=5 
$router->get('/get_sorted_characters/:string/:string?', 'Interview@getSortedCharacters');

// eg. http://localhost/interview/get_filtered_characters/male?page=2 ,
// http://localhost/interview/get_filtered_characters/female?page=2 
// http://localhost/interview/get_filtered_characters/male?page=2&perPage=5,
// http://localhost/interview/get_filtered_characters/female?page=2&perPage=5 
$router->get('/get_filtered_characters/:string', 'Interview@getFilteredCharacters');


// This is just a test for adding a book comment
$router->get('/add_book_comment', function(){
    global $apiUrl;
    echo "<form action='$apiUrl/save_book_comment' method='post' >
              <label for='book_id'>Book ID:</label><br>
              <input type='text' id='book_id' name='book_id' value='1'><br>
              <label for='commenter'>Commenter:</label><br>
              <input type='text' id='commenter' name='commenter' value='John Snown'><br>
              <label for='comment'>Comment:</label><br>
              <textarea id='comment' name='comment'></textarea><br>
              <input type='submit' value='Submit'>
          </form>";
  });


  $router->error(function() {
    // if it is ajax request
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'){
        return json_encode(['error'=>'The page you are requesting can not be found']);
    }else{
        echo "<style>
        *{
            transition: all 0.6s;
        }
        
        html {
            height: 100%;
        }
        
        body{
            font-family: 'Lato', sans-serif;
            color: #888;
            margin: 0;
        }
        
        #main{
            display: table;
            width: 100%;
            height: 100vh;
            text-align: center;
        }
        
        .fof{
              display: table-cell;
              vertical-align: middle;
        }
        
        .fof h1{
              font-size: 50px;
              display: inline-block;
              padding-right: 12px;
              animation: type .5s alternate infinite;
        }
        
        @keyframes type{
              from{box-shadow: inset -3px 0px 0px #888;}
              to{box-shadow: inset -3px 0px 0px transparent;}
        }
        </style>
        <div id='main'>
    	    <div class='fof'>
        		<h1>Error 404</h1>
        		<h5>The page you are requesting can not be found</h5>
            </div>
        </div>
        ";
    }

  });

$router->run();