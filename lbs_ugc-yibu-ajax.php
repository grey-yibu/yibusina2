<?php
    
    if(isset($_GET['lng']) && isset($_GET['lat']) && $_GET['lng'] !="" && $_GET['lat'] != "")
    {
        $lng = $_GET['lng'];
        $lat = $_GET['lat'];
    }
    else
    {
        $lng = '';
        $lat = '';
    }
    if( isset($_GET['page']) )
        $page = $_GET['page'];
    else
        $page = 1;
    
    if( (isset($_GET['ifOnlyCity']) && $_GET['ifOnlyCity'] ) )
    {
        $same_city = 1;
    }
    else
        $same_city = 0;

    if( (!isset($_GET['q']) || $_GET['q'] == "" ) )
    {
        $url    =   "http://i.api.place.weibo.cn/place/nearby/pois.json?offset=1&";
        $long   =   'long=' . $lng . '&';
        $lat   =    'lat=' . $lat . '&';
        $source =   'fakeclient=' . '1'. '&';
        $page   =   'page=' . $page. '&';
        $count  =   'count=' . '50'. '&';
        $sort   =   'sort=' . $_GET['sort'] . '&';
        $range  =   'range=' . '2000'. '&';
        $q      =   'q=' .  $_GET['q'];  
        $url    =   $url . $long . $lat . $source . $page . $count . $sort . $range . $q;
    }
    else
    {
        if($_GET['poiSource'] == "0")
            $_GET['poiSource'] = "";
        $url      =   "http://i.api.place.weibo.cn/place/pois/search.json?";
        $keyword  =   "keyword="   .  $_GET['q'] .       '&';  
        $city     =   "city="      .                     '&';  
        $category =   "category="  .                     '&';  
        $count    =   "count="     .  '50'       .       '&'; 
        $page     =   'page='      .  $page      .       '&';  
        $audit    =   'audit='     .  $_GET['audit'] .   '&';
        $long     =   'long='      .  $lng       .       '&';
        $lat      =   'lat='       .  $lat       .       '&';
        $sort     =   'sort='      .  $_GET['sort'].     '&';
        $poiSource=   'poi_source='.  $_GET['poiSource'].'&';
        $same_city=   'same_city=' .  $same_city;
        $url      =   $url . $keyword . $city . $category . $count . $page . $audit . $long . $lat . $sort.$poiSource.$same_city;
        
    }
    $html   =   file_get_contents($url);  
    echo $html; 

    
    // http://i.api.place.weibo.cn/place/nearby/pois.json?long=116.30987&lat=39.98437&page=1&count=50&sort=0&range=2000&q=新浪大厦&fakeclient=1
    // http://i.api.place.weibo.cn/place/pois/search.json?keyword=大厦&city=&category=&count=20&page=1&audit=&long=116.30987&lat=39.98437&sort=1

    // vi poi_search.php
    // vi nearby
?>
