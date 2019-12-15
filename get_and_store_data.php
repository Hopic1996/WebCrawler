<?php
session_start();
// function to create the snippet from the webpage link
 function create_snippet_from_webpage_address($webpage_id,$webpage_address,$parent_website,$feature_image,$brand_owner_id){
 // databse configuration file
  require 'database/snippet_db.php';
 if(!check_if_snippet_is_added($webpage_address)){
 // html content from the webpage
 $html = file_get_contents_curl($webpage_address);
 // start parsing with loading downloaded webpage into a html document
 $doc = new DOMDocument();
 @$doc->loadHTML($html);
 $titles = $doc->getElementsByTagName('title');
 $metas = $doc->getElementsByTagName('meta');
 $time = $doc->getElementsByTagName('time');
 $header_object=$doc->getElementsByTagName('h3');
 $p_object = $doc->getElementsByTagName('p');
 $link_object = $doc->getElementsByTagName('a');
 $image_object = $doc->getElementsByTagName('img');
 $divs=$doc->getElementsByTagName('div');
 $position = find_parent_div_tag($divs);
 $base_url=get_base_url($webpage_address,$parent_website);
 // save feature image to local directory
 $image_link = $feature_image;
 
   if(!empty($feature_image)){
 $feature_image = save_image_local_dir($image_link);
 }
 else{
  $article_feature_image  = get_feature_image($metas);
  $feature_image = save_image_local_dir($article_feature_image);
 }
    // find the main block of the webpage
    if($position){
      
      $main_content = get_main_content($divs,$position);
      $all_links = get_links_from_main_content($divs,$position,$base_url);
      $images = get_images($divs,$position,$base_url);
      $content = array_to_string(get_starting_content($main_content));
      $snippet_content = decode($content);
      $snippet_images = addslashes(array_to_string(get_images_at_begining($images)));
     }
     else{
      
      $main_content = get_all_content($p_object);
      $all_links = get_all_links($link_object,$base_url);
      $images = get_all_images($image_object,$base_url);
      $content = array_to_string(get_starting_content($main_content));
      $snippet_content = decode($content);
      $snippet_images = addslashes(array_to_string($images));
    }
     $snippet_title=decode(get_title($titles));
     $meta = get_meta($metas);
     $tags = array_to_string(get_webpage_tags($metas));
     $snippet_description = decode($meta['description']);
     //check if the meta description is empty
     if(empty($snippet_description)){
       
       $snippet_description = $snippet_title;
    }
    $date = get_published_date($metas,$time);
    
   if(empty($date)){
      $date = date("Y-m-d H:i:s");
    }
    // all other links and feature images found on the page
    $links_and_feature_images = get_links_and_feature_images($doc , $base_url);
    $keywords  = decode($tags);
    if(!empty($snippet_title)){
    //sql query to add the announcement details in database
    $sql="INSERT INTO snippets
          (snippet_id,owner_id,snippet_title,snippet_description,keywords,feature_image,webpage_address,snippet_date)
          VALUES(NULL,'$brand_owner_id','$snippet_title','$snippet_description','$keywords','$feature_image','$webpage_address','$date')";
    $result=mysqli_query($connection,$sql);
    if(!$result){
      echo mysqli_error($connection);
      $error="something went wrong , try again";
      return 0;
    }
    else{
        $snippet_id = mysqli_insert_id($connection);
        // call the function to store the snippet content
        store_into_snippet_content($snippet_id,$snippet_content);
        //update the total snipet count in brand_info table
        update_snippet_count($brand_owner_id,"increase");
        // update the state of the snippet as added
        update_the_snippet_state($webpage_id,"added");
        // store links and feature image
        store_links_and_feature_images($brand_owner_id,$base_url,$links_and_feature_images);
        // store the links found
        store_all_links($brand_owner_id,$base_url,$all_links);
        
        return 1;
       }
        //close the connection to the database
        mysqli_close($connection);
     }
     else{
       // update the state of the snippet as added
        update_the_snippet_state($webpage_id,"added");
     }
    }
  }
  
     // function to find the date
  function find_date_by_pattern($doc){
   $xpath = new DOMXPath($doc);
   $output = array();
  
   # find each img inside a link
   $date =  $xpath->query('//div[@id="updated-label_1-0"]');
   $date1 = $date->item(0)->nodeValue;
   if(strlen($date1)>10){
   $date1 = substr($date1, 9);
   }
   if(!empty($date1)){
        $date2 = date_create($date1);
        $date3 = date_format($date2,"Y-m-d H:i:s");
        return $date3;
      }
  }
  
   // function to get the date of the webpage
   function get_published_date($meta_object,$time){
    $date2 = "";
    
    for($i=0;$i<$meta_object->length;$i++){
    $meta = $meta_object->item($i);
    if($meta->getAttribute('property')=="article:published_time"){
        $date = $meta->getAttribute('content');
        if(!empty($date)){
        $date1 = date_create($date);
        $date2 = date_format($date1,"Y-m-d H:i:s");
        return $date2;
        break;
      }
   }
 }
        if(empty($date2)){
          $date = get_webpage_date($meta_object,$time);
          return $date;
        }
}
  // function to store the results in snippet content table
 function store_into_snippet_content($snippet_id,$snippet_content){
  require'database/snippet_content_db.php';
  $sql = "INSERT INTO snippet_content (snippet_id,snippet_content) VALUES('$snippet_id','$snippet_content')";
  $result = mysqli_query($connection,$sql);
  if(!$result){
    mysqli_error($connection);
  }
  // close the connection
  mysqli_close($connection);
 }
 // function to decode the string
 function decode($string){
  $string = utf8_decode($string);
  // decod it again in case it is encoded twice
  $string = utf8_decode($string);
  // remove the charecters inserted while decoding
  $string = str_ireplace("?", "'", $string);
  $string = addslashes(trim($string));
  return $string;
}
  // function to delete the snippet link
  function delete_snippet_link($webpage_id){
    require'database/snippet_links_db.php';
    $sql = "DELETE FROM snippet_links WHERE webpage_id='$webpage_id'";
    $result = mysqli_query($connection,$sql);
  }
//php function to store all the links scraped from the webpage
function store_all_links($owner_id,$base_url,$link_array){
	//database configuration
	require 'database/snippet_links_db.php';
	for($count=0;$count<count($link_array);$count++){
		$webpage_address = $link_array[$count];
    if(!check_if_link_is_added($webpage_address)){
		//store link into database
		$sql = "INSERT INTO snippet_links(webpage_id,owner_id,parent_website,webpage_address,date_added)
		        VALUES(NULL,'$owner_id','$base_url','$webpage_address',NOW())";
		$result = mysqli_query($connection,$sql);
		if(!$result){
		}
		else{
		}
  }
}
	//close the connection
	mysqli_close($connection);
}
// function to store link, thumbnail_image and image title 
function store_links_and_feature_images($owner_id,$base_url,$link_array){
	//database configuration
	require 'database/snippet_links_db.php';
	for($count=0;$count<count($link_array);$count++){
		$webpage_address = $link_array[$count]['link'];
    if(!check_if_link_is_added($webpage_address)){
		$feature_image = $link_array[$count]['img_link'];
		$image_title = $link_array[$count]['img_title'];
		//store link into database
		$sql = "INSERT INTO snippet_links
		        (webpage_id,owner_id,parent_website,webpage_address,feature_image,date_added)
		        VALUES(NULL,'$owner_id','$base_url','$webpage_address','$feature_image',NOW())";
		$result = mysqli_query($connection,$sql);
		if(!$result){
		}
		else{
		}
  }
}
	//close the connection
	mysqli_close($connection);
}
// function to save the image from url
 function save_image_local_dir($image_url){
  $day = date('d');
  $month_m = date('M');
  $year = date('Y');
  $pathname = 'snippet-images/'.$year.'/'.$month_m.'/'.$month_m.'-'.$day;
  $ext = get_file_extension($image_url);
  if(!is_dir($pathname)){
  mkdir($pathname);
  }
    $rand_num = md5(mt_rand());
    $rand = md5($image_url);
    
    @$raw_image = file_get_contents($image_url);
    $path = $pathname. '/' .$rand_num .'.'. $ext;
    $dest = $pathname. '/'. $rand . '.'.$ext;
    
    if($raw_image)
     {
     file_put_contents($path,$raw_image);
     $file = check_and_compress($path,$dest);
     $image_address = "snippets/".$file;
     return $image_address;
     }
    else
     {
      return $image_url;
     }
   }
// function to get image extension
   function get_file_extension($filename){
    if(strpos($filename, "jpeg")){
      $ext = "jpeg";
    }
    else if(strpos($filename, "png")){
      $ext = "png";
    }
    else if(strpos($filename, "gif")){
      $ext = "gif";
    }
    else{
      $ext = "jpg";
    }
    return $ext;
   }
// function check if the link is already added to the table
function check_if_link_is_added($page_address){
	//database configuration
	require'database/snippet_links_db.php';
	$sql = "SELECT webpage_id FROM snippet_links WHERE webpage_address='$page_address'";
	$result = mysqli_query($connection,$sql);
	if(mysqli_num_rows($result)<=0){
		return 0;
	}
	else{
        return 1;
	}
	//close the connection
	mysqli_free_result($result);
	mysqli_close($connection);
}
// function to check if the page snippet is added
function check_if_snippet_is_added($page_address){
	//database configuration
	require'database1.php';
    
    $sql = "SELECT snippet_id FROM snippets WHERE webpage_address='$page_address'";
	  $result = mysqli_query($connection,$sql);
	if(mysqli_num_rows($result)<=0){
		return 0;
	}
	else{
        return 1;
	}
	//close the connection
	mysqli_free_result($result);
	mysqli_close($connection);
}
// update the state of the "is_added" on the snippet link
 function update_the_snippet_state($webpage_id,$action){
    // database configuration file
    require 'database/snippet_links_db.php';
 	switch ($action) {
 		case 'added':
 			  
 			  $sql = "UPDATE snippet_links
 			          SET
 			          is_added = 1
 			          WHERE webpage_id = '$webpage_id'";
 			  $result = mysqli_query($connection,$sql);
 			  if(!$result){
 			  	echo mysqli_error($connection);
 			  }
 			break;
 		case 'deleted':
 		      $sql = "UPDATE snippet_links
 			          SET
 			          is_added = 0
 			          WHERE webpage_id = '$webpage_id'";
 			  $result = mysqli_query($connection,$sql);
 			  if(!$result){
 			  	echo mysqli_error($connection);
 			  }
 		
 		default:
 			echo 'something went wrong , fucking check what it is';
 			break;
 	}
 }
 // function to store whole main content
  function store_whole_content($snippet_id,$webpage_address,$images,$content){
  	// database configuration file
  	require 'database1.php';
    $main_content = addslashes(array_to_string($content));
    $all_images = addslashes(array_to_string($images));
  	$sql = "INSERT INTO all_snippet_content
  	        (content_id,snippet_id,webpage_address,images,content)
  	        VALUES(NULL,'$snippet_id','$webpage_address','$all_images','$main_content')";
  	$result = mysqli_query($connection,$sql);
  	if(!$result){
  		echo mysqli_error($connection);
  		return 0;
  	}
  	else{
        return 1;
  	}
  	// close the connection
  	mysqli_close($connection);
  }
// function to convert the arry into comma seperated string
 function array_to_string($array){
  $joined_string="";
  for($count=0;$count<count($array);$count++){
        
        if($count!=count($array)-1){
        $joined_string.= $array[$count].'|';
        }
        else{
        $joined_string.=$array[$count];
        }
  }
  return $joined_string;
 }
 
 // function to convert the content array into '/' seperated string
 function content_array_to_string($array){
  $joined_string="";
  for($count=0;$count<count($array);$count++){
        
        if($count!=count($array)-1){
        $joined_string.= $array[$count].'/';
        }
        else{
        $joined_string.=$array[$count];
        }
  }
  return $joined_string;
 }
 // get begining 4 paragraphs of the content
  function get_starting_content($content){
    $starting_content = array();
    $count=0;
    $len = 0;
    for($i=0;$i<count($content);$i++){
     $par = trim($content[$i]);
     $len+=strlen($par);
     if(!empty($par) && strlen($par)>100){
     $starting_content[$count] = $par;
     $count++;
    }
    
    if($count>=4 && $len>250)
      break;
 }
   return $starting_content;
  }
  // function to analyse the content on the website
  function analyse_content($content){
    $starting_content = array();
    $count=0;
    for($i=1;$i<count($content);$i++){
     if(!empty($content[$i]) && strlen($content[$i])>250){
     $paragraph = trim($content[$i]);
     $paragraph = str_ireplace("\r\n","", $paragraph);
     $starting_content[$count] = $paragraph;
     $count++;
    }
    if($count>=2)
      break;
  }
    return $starting_content;
}
  // function to get the images at the begining
   function get_images_at_begining($image_array){
    $images = array();
    if(count($image_array) > 7 ){
      for($i=0;$i<7;$i++){
        $images[$i] = $image_array[$i];
      }
      return $images;
     }
     else{
      return $image_array;
     }
   }
?>
