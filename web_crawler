<?php
// function to find the fucking error in scraping
function file_get_contents_curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_ENCODING,  '');
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    if(curl_error($ch)){
      return 0;
    }
    else{
        $data = curl_exec($ch);
        
        curl_close($ch);
        
        return $data;
    }
}
  // function ot get the links with the feature images
   function get_links_and_feature_images($doc,$base_url){
   $xpath = new DOMXPath($doc);
   $output = array();
  
   # find each img inside a link
   foreach ($xpath->query('//a[@href]//img') as $img) {
    // find the link searching <a> element
    for ($hyperlink = $img; $hyperlink->tagName !== 'a'; $hyperlink = $hyperlink->parentNode);
      $link = $hyperlink->getAttribute('href');
      $image = $img->getAttribute('src');
    if(!stristr($image, "gif")){
      if((strcmp("http://",substr($image, 0,7)) && strcmp("https://",substr($image, 0,8))) && 
         (strcmp($base_url , substr($image, 0 , strlen($base_url))) && !empty($image))){
      if($image[0] == '/'){
        $image = $base_url.$image;
        }
        else{
        $image = $base_url.'/'.$image;
      }
    }
      $link = analyse_link($link,$base_url);
    if(!empty($link)){
    $slug_length = get_url_slug_length($link);
  
    //check if the link redirects to same website or other
    if(!in_array($link, $output) && !is_link_image($link) && $slug_length>10 && 
       !(strpos($link, "?")) && !(strpos($link, "#")) && !empty($image)){
            $output[] = array('link' => $link,
                                'img_link'  => $image,
                                'img_title'  => $img->getAttribute('alt'));
         }
       }
     }
   } 
          return $output;
}
function analyse_link($link,$parent_website){
  $link_scheme = $link_host = $link_path = $link_base_website = "";
  
  $parent_website_info = parse_url($parent_website);
  $base_scheme = $parent_website_info['scheme'];
  $base_host  = $parent_website_info['host'];
  $base_url = $base_scheme . '://' . $base_host;
  $link_info = parse_url($link);
  if(!empty($link_info['scheme']))
  $link_scheme = $link_info['scheme'];
  if(!empty($link_info['host']))
  $link_host  = $link_info['host'];
  
  if(!empty($link_info['scheme']) && !empty($link_info['host']))
  $link_base_website = $link_scheme . '://' . $link_host;
  
  if(!empty($link_info['path']))
  $link_path = $link_info['path'];
  
    if(!strcmp($base_url, $link_base_website)){
        return $link;
    }
    else if(empty($link_scheme) && empty($link_host)){
      if($link[0] == '/'){
        $link = $base_url.$link_path;
      }
      else{
        $link = $base_url . '/' . $link;
      }
        return $link;
    }
 }
 // function to get the url slug and length
 function get_url_slug_length($link){
  
  $len = strlen($link);
  if($link[$len] = '/'){
    $new_link = substr($link, 0 , $len-1);
  }
  else{
    $new_link = $link;
  }
  $last_pos = strrpos($new_link, "/");
  $slug = substr($new_link, $last_pos+1 , strlen($new_link));
  return strlen($slug);
 }
  // check if link is image
  function is_image($url){
    
    if (preg_match("/(\.)(jpg$)/i", $url) || preg_match("/(\.)(jpeg$)/i", $url) || preg_match("/(\.)(png$)/i", $url)) {
     return 1;
    } else {
     return 0;
     }
  }
   // check if link is image
  function is_link_image($url){
    
    if (preg_match("/(\.)(jpg$)/i", $url) || preg_match("/(\.)(jpeg$)/i", $url) || preg_match("/(\.)(png$)/i", $url) || 
        preg_match("/(\.)(gif$)/i", $url)) {
     return 1;
    } else {
     return 0;
     }
  }
 // function to ge the base url
  function get_base_url($link,$base_url){
    $pos=strpos($link, $base_url);
    $length=$pos+strlen($base_url);
    $base_url = substr($link, 0 , $length);
    return $base_url;
  }
 // title of the blog
 function get_title($title){
 $title = $title->item(0)->nodeValue;
 return $title;
 }
 
 //function to get the main headers
 function get_header($headers){
 $header_array=array();
 $count = 0;
 for($i=0;$i<$headers->length;$i++){
    
    $header_value = $headers->item($i)->nodeValue;
    if(!empty($header_value)){
    $header_array[$count]= $header_value;
    $count++;
  }
}
    return $header_array;
 }
 // find the main div block in the blog
 function find_parent_div_tag($divs){
   for($i=0;$i<$divs->length;$i++){
    $div2=$divs->item($i);
    
    $div=$divs->item($i)->nodeValue;
    $p=$div2->getElementsByTagName('p');
    if($p->length>5){
    return $i;
    break;
   }
  }
 }
 // get the main content of the blog ( decided by most <p> in div tag)
 function get_main_content($object,$position){
    $content_array=array();
    $div2=$object->item($position);
    
    $div=$object->item($position)->nodeValue;
    
    $p=$div2->getElementsByTagName('p');
    for($i=0;$i<$p->length;$i++){
    
     $content_array[$i]=$p->item($i)->nodeValue;
    }
    return $content_array;
    
}
// function to get all the content from the blog
 function get_all_content($object){
    $content_array=array();
    for($i=0;$i<$object->length;$i++){
    
     $content_array[$i]=$object->item($i)->nodeValue;
    }
    return $content_array;
    
}
// get links from the main content block
function get_links_from_main_content($object,$position,$base_url){
  $links=array();
  $count=0;
  $content=$object->item($position);
  $hyperlinks= $content->getElementsByTagName('a');
  for($i=0;$i<$hyperlinks->length;$i++){
     $hyperlink = $hyperlinks->item($i)->getAttribute('href');
     $link = analyse_link($hyperlink,$base_url);
     if(!empty($link)){
     $slug_length = get_url_slug_length($link);
     //check if the link redirects to same website or other
     if(!in_array($link, $links) && !is_link_image($link) && $slug_length> 30 && !strpos($link, "?") && !strpos($link, "#")){
      $links[$count] = $link;
      $count++;
     }
    }
  }
     return $links;
}
// function to get all links
 function get_all_links($object,$base_url){
   $links = array();
  $count=0;
  for($i=0;$i<$object->length;$i++){
    $hyperlink = $object->item($i)->getAttribute('href');
    $link = analyse_link($hyperlink,$base_url);
    if(!empty($link)){
    $slug_length = get_url_slug_length($link);
    //check if the link redirects to same website or other
    if(!in_array($link, $links) && !is_link_image($link) && $slug_length > 25 && !stristr($link, "?") && !stristr($link, "#")){
      $links[$count] = $link;
      $count++;
      }
     }
   } 
    return $links;     
 }
 // collect all links to deep scrape
 function collect_all_links($object,$base_url){
  $links = array();
  $count = 0;
  for($i=0;$i<$object->length;$i++){
    $hyperlink = $object->item($i)->getAttribute('href');
    $link = analayse_link_found($hyperlink,$base_url);
    //check if the link redirects to same website or other
    if(!in_array($link , $links) && !is_link_image($link) && !strpos($link, "#")){
       $links[$count] = $link;
       $count++;
      }
    } 
    return $links;  
 }
 // images from the main div block of the webpage
  function get_images($object,$position,$base_url){
  
   $images_array=array();
   $count=0;
   $div2=$object->item($position);
   $images=$div2->getElementsByTagName('img');
   for($i = 0; $i < $images->length; $i++){
    $image = $images->item($i);
    $img = $image->getAttribute('src');
    if( strcmp("http://",substr($img, 0,7)) && strcmp("https://",substr($img, 0,8)) && !empty($img)){
      
      $img_link = $base_url.$img;
      if(!in_array($img_link, $images_array)){
      $images_array[$count] = $base_url.$img;
      $count++;
      }
    }
    else
      if( !strcmp($base_url , substr($img, 0 , strlen($base_url))) && !empty($img) && !in_array($img, $images_array)){
        $images_array[$count] = $img;
        $count++;
      }
    }  
 
    return $images_array;
}
// get all images
 function get_all_images($object,$base_url){
  $images_array=array();
  $count=0;
  for($i=0;$i<$object->length;$i++){
    $image = $object->item($i);
    $image_link = $image->getAttribute('src');
        if(!strcmp($base_url, substr($image_link, 0,strlen($base_url))) && !empty($image_link) && !in_array($image_link, $images_array)){
        $images_array[$count] = $image_link;
        $count++;
    }
  }
  return $images_array;
 }
// function to get the meta tags
 function get_meta($metas){
   
  $meta_tags=array();
  for ($i = 0; $i < $metas->length; $i++)
   {
    $meta = $metas->item($i);
    if($meta->getAttribute('name') == 'description' || $meta->getAttribute('property') == 'og:description')
        $meta_tags['description'] = $meta->getAttribute('content');
    if($meta->getAttribute('name') == 'keywords')
        $meta_tags['keywords'] = $meta->getAttribute('content');
   }
   return $meta_tags;
 }
// function to get the date of the webpage
   function get_webpage_date($meta_object,$time){
    $date2 = "";
    
    for($i=0;$i<$meta_object->length;$i++){
    $meta = $meta_object->item($i);
    if(stristr($meta->getAttribute('property'), "time") || stristr($meta->getAttribute('property'), "date")
       || stristr($meta->getAttribute('property'), "datetime") || stristr($meta->getAttribute('name'), "time")
       || stristr($meta->getAttribute('name'), "date"))
        $date = $meta->getAttribute('content');
        if(!empty($date)){
        $date1 = date_create($date);
        $date2 = date_format($date1,"Y-m-d H:i:s");
        return $date2;
        break;
      }
   }
       if(empty($date2) && $time->length>0){
        $date = $time->item(0)->getAttribute('datetime');
        $date1 = date_create($date);
        $date2 = date_format($date1,"Y-m-d H:i:s");
        return $date2;
  }
}
// function to get the date from date tag
function get_the_date($object){
  $date = $object->item(0)->getAttribute('datetime');
        $date1 = date_create($date);
        $date2 = date_format($date1,"Y-m-d H:i:s");
        return $date2;
}
// function to get the meta tags
   function get_webpage_tags($meta_object){
    
    $keywords = array();
    $count = 0;
    for($i=0;$i<$meta_object->length;$i++){
    $meta = $meta_object->item($i);
    if($meta->getAttribute('property') == "article:tag" || stristr($meta->getAttribute('property'), "tag") || 
       stristr($meta->getAttribute('property'), "keyword") || stristr($meta->getAttribute('name'), "tag") || 
       stristr($meta->getAttribute('name'), "keyword"))
      
        $tag = $meta->getAttribute('content');
        if(!empty($tag) && !in_array($tag, $keywords)){
        $keywords[$count] = $tag;
        $count++;
      }
   }
     return $keywords;
}
// function to get the feature image of the article
 function get_feature_image($meta_object){
   
    for($i=0;$i<$meta_object->length;$i++){
    $meta = $meta_object->item($i);
    if($meta->getAttribute('property') == "og:image" || $meta->getAttribute('property') == "twitter:image")
        $image = $meta->getAttribute('content');
   }
    
    return $image;
 }
?>
