<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">  
<html xmlns="http://www.w3.org/1999/xhtml">  
<head>  
    <title>LBS UGC内容服务系统</title>   
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    

    
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <!-- <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script> -->
    <script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.js"></script>
    <script src="https://cdn.bootcss.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- 下拉动画 js 组件 --> 
    <script src="https://cdn.bootcss.com/iScroll/5.2.0/iscroll-infinite.js"></script>
   
    <!-- 将高德地图API引入，设置好key -->  
    <script type="text/javascript" src="http://webapi.amap.com/maps?v=1.4.4&key=8833b5f0222631479e3048375ec553bc&plugin=AMap.Geocoder"></script>
   
    
 
    <style>
        /*body {background-color: rgb(242,242,242);}*/
        /*html,body{height: 100%; overflow: hidden;}*/
        iframe {width: 100%;}
        #title {margin-top: 10px; margin-bottom: 5px; margin-left: 20px;}
        #r-result{
            margin-top: 10px;
            margin-bottom: 10px;
        }
    </style>
    <script>
        /*********************************************************************/
        /** lbs_ugc.php
         ** 最后修改：杨玉洲 (call:18813011762)
         ** 修改时间：2018.3.29
         */
        
        $(document).ready(function(){
        /*********************************************************************/
        /** 页面初始化相关操作:
         ** 地图插件初始化
         ** 全局变量声明：_map  ,_geoc ,_SearchArray ,_nowShowUl
         **               point ,_page ,_total_number 
         ** 页面大小js调整（原代码保留）
         */
            var _map = new AMap.Map("allmap");
            var _geoc = new AMap.Geocoder({   
                    radius: 1000,
                    extensions: "all"
                });                                                // 类 用于获取用户的地址解析  
            var markersArray = [];             
            var _SearchArray = [];                                 // 记录 查找到的点集，ul为单位
            var _nowShowUl   = 0;                                  // 记录目前显示的点集 ul下标
            var point = new AMap.LngLat(116.406496, 39.914326);  
            _map.center = point;                                   // 中心点
            _map.zoom = 16;                                        // 缩放等级
            _map.setZoomAndCenter(18, point);
            _map.scrollWheel = true;                               // 允许使用滚轮
            
            var _page          =  1;                               // 设置初始分页数
            var _total_number  =  10;                              // 设置分页查询总数
            
            autodivheight();                                       //浏览器窗口发生变
            window.onresize = autodivheight;                       //化时同时变化DIV高度
            
        /*********************************************************************/
        /** 左上 下拉
         ** 界面左上角功能区;下拉选择功能
         ** 设置页面的下拉选项 确定局部变量 以及显示模式
         ** 功能选项  ：实景图   |   GPS图片 |  签到图片  |  签到列表 |
            位置视频 |   直播    |  周边微博  |  三星 |
            搜索查看地图点
         ** 排序选项  ：时间排序 |  距离排序 |  热度排序
         */
            var select_json = '{"实景图":["时间排序","距离排序","热度排序"]'+
                                    ',"GPS图片":["时间排序","距离排序","热度排序"]'+
                                    ',"签到图片":["时间排序","距离排序","热度排序"]'+
                                    ',"签到列表":["时间排序","距离排序","热度排序"]'+
                                    ',"位置视频":["时间排序","距离排序","热度排序"]'+
                                    ',"直播":["时间排序","距离排序","热度排序"]'+
                                    ',"周边微博":["时间排序1","距离排序1","热度排序1"]'+
                                    ',"三星":["时间排序","距离排序","热度排序"]'+
                                    ',"搜索查看地图点":["综合排序","距离排序","热度排序"]}';
                                    
            var select_obj  = eval('('+select_json+')');
            var _count = 0;
            for (var key in select_obj)
            {
                $("#tags").append("<option value='"+ _count +"'>"+ key +"</option>");
                _count += 1;
            }
            document.getElementById("tags")[3].selected=true;    //设置默认被选中项

            $("#tags").on("change",function(){
                var tage_select = $("option:selected",this).text();
                $("#sort").html('');
                for(var k in select_obj[tage_select])
                {
                    var sort = select_obj[tage_select][k];
                    if( tage_select == "搜索查看地图点" && k == 2)     // 对 热度排序 值 进行特殊处理
                        k=3;
                    $("#sort").append('<option value="'+k+'">'+sort+'</option>');
                }
                
                if ($("option:selected",this).index() == 8) {
                    document.getElementById("test").style.display="none";
                    document.getElementById("grey_search").style.display="";
                    document.getElementById("out_div").style.display="";
                    document.getElementById("ifCheck_div").style.display="inline";
                    $("#ifCheck").prop("checked",false);
                    
                    _geoc.getAddress( point, function (status, result) {  
                        //alert(JSON.stringify(result));
                        if (status === 'complete' && result.info === 'OK') {
                            var address = result.regeocode.formattedAddress;
                            var content = "<b>"+address+"</b>";
                            $('#address').html(content);  
                        }
                    });  
                    
                    _page = 1;
                    _total_number = 10;
                    addLbsSearch( point );
                }
                else
                {
                    document.getElementById("test").style.display="";
                    document.getElementById("grey_search").style.display="none";
                    document.getElementById("out_div").style.display="none";
                    document.getElementById("ifCheck_div").style.display="none";
                    document.getElementById("search_div").style.display="none";
                    
                    mapOnclick(point);
                    addMarker(point);              // 地图加载后第一个点改到这里了
                    changeIframe();
                }
            });

            $("#sort").on("change",function(){
                if( $("#tags").val() == 8  )
                {
                    _myscroll.scrollTo(0, 0, 1000, IScroll.utils.ease.elastic);
                    _page = 1;
                    _SearchArray = [] ;             //  清空 找到点 的数组
                    _nowShowUl   = 0;
                    addLbsSearch(point);
                }
                else
                {
                    _page = 1;
                    _map.clearMap();
                    addMarker(point);
                    // 更新iframe地址
                    changeIframe();
                }
            });

            $("#ifCheck").change(function(){
                if( $("#ifCheck").prop("checked") )
                {
                    document.getElementById("search_div").style.display="inline";
                    _map.clearMap();
                    _SearchArray = [] ;             //  清空 找到点 的数组
                    _nowShowUl   = 0;
                    addMarker(point);
                    _map.setZoomAndCenter(18, point);
                }
                else
                {
                    document.getElementById("search_div").style.display="none";
                    
                    _SearchArray = [] ;             //  清空 找到点 的数组
                    _nowShowUl   = 0;
                    addLbsSearch(point);
                }
            });
            
            $("#ifOnlyCity").change(function(){
                $("#submit").click();
            })
            
            $("#ifPassCheck").change(function(){
                $("#submit").click();
            })
            
            $("#poiSource").change(function(){
                $("#submit").click();
            })
            
            //** 下拉功能8里 在页面按钮 "确定" 点击后
            //** 更新iframe div( "#tags"=8 ) 内容
            $("#submit").click(function(){
                var range = $("#radius").val();
                _myscroll.scrollTo(0, 0, 1000, IScroll.utils.ease.elastic);
                _SearchArray = [] ;             //  清空 找到点 的数组
                _nowShowUl   = 0;
                if (isNull(range)) {
                    range = 3000;
                }else{
                    range = range*1000;
                }
                if( $("#tags").val() != 8)
                {
                    changeIframe();
                }
                else
                {
                    _SearchArray = [] ;             //  清空 找到点 的数组
                    _nowShowUl   = 0;
                    _page = 1;
                    _total_number = 10;
                    if(!$("#grey_search").val())
                        alert("请输入查询内容");
                    else
                        addLbsSearch(point);
                }
            });
            
        /*********************************************************************/
        /** 地图点击
         ** 右侧地图 点击事件 相关功能
         ** 地图 点击、输入提示 、智能搜索(语义转坐标) 等回调设置
         */
            var _mapRealLocation;        //输入提示 变更 用到查询变量
            AMap.plugin(['AMap.ToolBar','AMap.Autocomplete','AMap.Geocoder','AMap.PlaceSearch'],
                function(){      
                    _map.addControl(new AMap.ToolBar());        // 创建一个缩放控件
                    
                    var autoOptions = {                        // 添加输入提示
                        city: "", //城市，默认全国
                        input:"suggestId"//使用联想输入的input的id
                    };
                    var autocomplete = new AMap.Autocomplete(autoOptions);
                    
                    // 设置在进行 输入提示 后点击下拉项发生的回调
                    AMap.event.addListener(autocomplete, "select", function(e){
                        _mapRealLocation = e.poi.name;
                        setPlace();
                    });  
                });
            _map.on('click', function(e) {point = e.lnglat;mapOnclick(e.lnglat);});  // 设置地图点击时 回调函数
            mapOnclick(point);
            addMarker(point);              // 地图加载后第一个点改到这里了
            changeIframe();
            
            //** 函数 ： mapOnclick(point)
            //** 功能 ： 在地图被点击后 对功能项 和地图进行功能显示（被点击点的信息显示）
            //** 参数 ： point[lat,lng]  被点击坐标 / 语义转化后的坐标
            //** 发生过程：地图被点击后 / 输入提示下拉项被点击
            function mapOnclick(point) {
                _geoc.getAddress( point, function (status, result) {  
                        //alert(JSON.stringify(result));
                        if (status === 'complete' && result.info === 'OK') {
                            var address = result.regeocode.formattedAddress;
                            var content = "<b>"+address+"</b>";
                            $('#address').html(content);  
                        }
                    });  
                    
                if( $("#tags").val() != 8  )
                {
                    _map.clearMap();
                    addMarker(point);
                    // 更新iframe地址
                    changeIframe();
                }
                else
                {    
                    if( $("#ifCheck").prop("checked") )
                    {
                        _map.clearMap();
                        _SearchArray = [];      //  清空 查找点 数组
                        _nowShowUl   = 0;
                        addMarker(point);
                        // 更新iframe地址
                        if( !$('#grey_search').val() )
                        {
                            alert("请输入查询内容");
                        }
                        else
                        {
                            _myscroll.scrollTo(0, 0, 1000, IScroll.utils.ease.elastic);
                            _SearchArray = [] ;             //  清空 找到点 的数组
                            _nowShowUl   = 0;
                            _total_number = 10;
                            addLbsSearch(point);
                        }
                        
                    }
                    else
                    {
                        _myscroll.scrollTo(0, 0, 1000, IScroll.utils.ease.elastic);
                        _SearchArray = [] ;             //  清空 找到点 的数组
                        _nowShowUl   = 0;
                        _page = 1;
                        _total_number = 10;
                        addLbsSearch(point);
                        addMarker(point);
                    }
                }
            }
        
             //** 输入提示 -> 智能搜索回调  setPlace()
             //** 功能 ： 使用高德的 智能搜索 功能 ，将语义地址转为坐标地址
             //** 发生过程：在 输入提示 后点击下拉项发生的回调
             //** 传出函数：mapOnclick()
             //** 传出内容：point[lat,lng]  一个包含经纬度的坐标
            function setPlace(){         // 在地图输入框中输入内容 并点击确定后
                _SearchArray = [] ;      //     清空 查找点 数组
                _nowShowUl   = 0 ;
                _map.clearMap();         //清除地图上所有覆盖物
                function myFun(SearchResult){
                    //alert(JSON.stringify(SearchResult));
                    _SearchArray = [] ;      //     清空 查找点 数组
                    _nowShowUl   = 0 ;
                    _page = 0;
                    point = SearchResult.poiList.pois[0].location;    //获取第一个智能搜索的结果
                    _map.setZoomAndCenter(18, point);
                    _map.add(new AMap.Marker({position:point}));    // 地图上 添加标注
                    mapOnclick(point);
                }
                var local = new AMap.PlaceSearch();   // 智能搜索    
                local.search(_mapRealLocation, function(status, result){ myFun(result) });   // 回调触发
            }
        /*********************************************************************/
        /** Ajax异步访问
         ** 地图 点击后 进行ajax查找回调、显示功能
         ** 功能模块：ajax 查找、  左侧div显示更新
         */
            //** 函数 ： addLbsSearch(point,icon_count_url)
            //** 功能 ： 显示查找内容,调用后为 test_div 添加一条查找到的内容
            //** 参数 ： point 通过接口返回的一条内容信息 / icon_count_url 显示序号的图标url
            //** 发生过程：在进行点查找之后  addLbsSearch(point)
            function addLbsSearch(point,clearDiv)
            {
                var sort = $("#sort").val();
                var q = $("#grey_search").val();
                if(isNull(clearDiv) || clearDiv != 1)
                    $('#test_div').html("");

                var lat,lng;
                lat = point.lat;
                lng = point.lng;
                var audit,ifOnlyCity,poiSource;
                if( !$("#ifCheck").prop("checked") )    // 不 进行关键词搜索
                {
                    q = "";
                }
                audit       = $("#ifPassCheck").prop("checked")?1:0;
                poiSource   = $("#poiSource").val(); 
                ifOnlyCity  = $("#ifOnlyCity").prop("checked")?1:0;

                if( isNull(_page) || _page == 1 )
                    $(".previous").addClass("disabled");
                else
                    $(".previous").removeClass("disabled");
                /*console.log("lat="+lat+"&lng="+ lng + "&sort=" 
                                + sort + "&q=" + q + "&page=" 
                                + _page + "&ifOnlyCity=" +ifOnlyCity 
                                + "&audit=" + audit + "&poiSource=" +poiSource);*/
                
                // 兼容 解决跨域后直接调用接口选项
                var url = "lbs_ugc-yibu-ajax.php";
                var get_json =
                    {
                        lat:lat,
                        lng:lng,
                        sort:sort,
                        q:q,
                        page:_page,
                        ifOnlyCity : ifOnlyCity,
                        audit: audit,
                        poiSource  : poiSource
                    };
                // 兼容 解决跨域后直接调用接口选项 注释
                /*
                    if( $("#ifCheck").prop("checked") )     //   进行关键词搜索
                    {
                        var url = "http://i.api.place.weibo.cn/place/pois/search.json";
                        var get_json =
                            {
                                keyword     :   q   ,
                                city        :       ,
                                category    :       ,
                                count       :   50  ,
                                page        :   _page,
                                audit       :       ,
                                long        :   lng ,
                                lat         :   lat ,
                                sort        :   sort
                            };
                    }
                    else                                     // 不进行关键词搜索
                    {
                        var url = "http://i.api.place.weibo.cn/place/nearby/pois.json";
                        var get_json =
                            {
                                offset      :   1    ,
                                long        :   lng  ,
                                lat         :   lat  ,
                                fakeclient  :   1    ,
                                page        :   _page ,
                                count       :   count,
                                sort        :   sort ,
                                range       :   2000 ,
                                q           :   q
                            };
                    }
                */
                $.getJSON(url,get_json,function(json){
                    _map.clearMap();
                    var markersArray_search = [];
                    var isReturnTwoPoint = 0;       // 标识符 用于判断结果的第一、二个点是否为新加点
                    
                    $('#test_div').append('<ul  style="padding:0;margin:0" id="u'+_SearchArray.length+'"></ul>');      
                                                    // div 添加 ul 为 hover回调准备
                    for(var i in json.pois)
                    {
                        var point1          = [ json.pois[i]['lon'], json.pois[i]['lat']];
                        var content        =  "<div style=\"height: 45px; background: url(&quot;http://i.api.place.weibo.cn/img/map_point.png&quot;) repeat scroll 0% 0% transparent; width: 29px; text-align: center; color: rgb(0, 0, 0); font-weight: bold;\" onclick=\"addMarker("+lng+","+lat+","+_page+")\">"+(parseInt(i)+1+(parseInt(_page)-1)*50)+"</div>";

                        if( _page == 1 && (i==0 || i==1) && !isNaN(parseInt(json.pois[i]['poiid'],10)) )        // 去除首次查询返回的两个基点
                        {
                            content          =  "<div style=\"height: 45px; background: url(&quot;http://i.api.place.weibo.cn/img/map_point.png&quot;) repeat scroll 0% 0% transparent; width: 29px; text-align: center; color: rgb(0, 0, 0); font-weight: bold;\" onclick=\"addMarker("+lng+","+lat+","+_page+")\"></div>";
                            isReturnTwoPoint = 1;
                        }
                        else if( isReturnTwoPoint == 1 )
                            content        =  "<div style=\"height: 45px; background: url(&quot;http://i.api.place.weibo.cn/img/map_point.png&quot;) repeat scroll 0% 0% transparent; width: 29px; text-align: center; color: rgb(0, 0, 0); font-weight: bold;\" onclick=\"addMarker("+lng+","+lat+","+_page+")\">"+(parseInt(i) - 1)+"</div>";
                        
                        var marker = new AMap.Marker({
                                    lng:lng,
                                    lat:lat,
                                    position:point1,
                                    content:content
                              });
                        marker.on('click', function(e) {
                            }); 
                        markersArray_search.push(marker);
                        _map.add(marker);  
                        addLbsSearchBox(json.pois[i],content,"u"+_SearchArray.length);
                    }
                    _total_number = json.total_number;
                    $("#tN").text(_total_number);
                    _myscroll.refresh();
                    _map.setFitView(markersArray_search)    ;   
                    _SearchArray.push(markersArray_search);
                    _map.add(new AMap.Marker({position:point}));
                }); 
            }
            
            //** 函数 ： addLbsSearchBox(point,icon_count_url,ul_id)
            //** 功能 ： 显示查找内容,调用后为 test_div 添加一条查找到的内容
            //** 参数 ： point 通过接口返回的一条内容信息 / icon_count_url 显示序号的图标url
            //** 发生过程：在进行点查找之后  addLbsSearch(point)
            function addLbsSearchBox(point,icon_count_url,ul_id)
            {
                if( point['distance'] == undefined )
                {
                    point['distance'] = 0;
                }                    
                var ShowDiv = "";
                ShowDiv += '';
                ShowDiv += '<li style="width:95%;height:15vh;margin-bottom:1vh;">';
                ShowDiv += '<div class="row" style="width:100%;height:100%;margin-left:2%;">';
                ShowDiv += '    <div style="width:95%;height:100%;margin-left:1vw;">';
                ShowDiv += '        <div  style="float:left;height:100%;">';
                ShowDiv += '            <img src="' + point['poi_pic'] + '" style="height:100%">';
                ShowDiv += '        </div>';
                ShowDiv += '        <div style="float:left;height:100%;">' + icon_count_url + '</div>';
                ShowDiv += '        <div  style="float:left;width:70%;height:100%;overflow:hidden;">';
                ShowDiv += '            <h3 class="m-text-cut" style="margin:0 auto;">' + point['title'] + '</h3>';
                ShowDiv += '            <h5 class="m-text-cut" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis">' + point['address'] + '</h4>';
                ShowDiv += '            <h5 class="m-text-cut" style="color: #939393;">' + point['checkin_user_num'] + ' 人  ' + point['distance'] + ' 米以内  </h4>';
                ShowDiv += '        </div>';
                ShowDiv += '    </div>';
                ShowDiv += '</div><hr style="margin:0;margin-bottom:1vh;" />';
                ShowDiv += '</li>';
                $('#'+ul_id+'').append(ShowDiv);
            }
            
        /*********************************************************************/
        /** 滚动插件
         ** 滚动插件加载以及相关回调
         ** 功能模块：下拉加载、滚动地图自刷新
         */
            //** 加载 IScroll.js 组件并初始化
            //** 设置 scroll    回调 ：在div 拖动时触发
            //** 设置 scrollEnd 回调 ：在拖动结束松开按键时触发
            //** 设置两个回调，目的是解决 scroll 多次触发不唯一问题
             var _myscroll = new IScroll("#wrapper",{
                 mouseWheel: true,
                 scrollbars: true,
                 probeType:2,
             });
             var _scrollFlag = 0;                       // 滚动加载标识，用于使下拉加载只执行一次
             _myscroll.on("scroll", function() { 
                    console.log( parseInt(  parseInt(this.y) / $("#u0").height()  )*(-1) );                //console.log(this.y);
                    if(this.y <= ( this.maxScrollY -20 )) { // 上拉加载更多       
                        _scrollFlag = 1 ;
                        $("#pullUp").text("正在载入");
                    } 
                    if(  parseInt(  parseInt(this.y) / $("#u0").height()  )*(-1)  !=  _nowShowUl )
                    {
                        _nowShowUl = parseInt(  parseInt(this.y) / $("#u0").height()  )*(-1);
                        ShowUl( _nowShowUl );
                    }
                    if(this.y > 40) { //下拉刷新操作
                        $("#totalNum").show();
                    }
                });
             _myscroll.on('scrollEnd', function () { 
                $("#totalNum").hide();
                //console.log("_scrollFlag : "+_scrollFlag);
                if(_scrollFlag == 1 &&  _page * 50 < _total_number  ){ 
                    _scrollFlag = 2;
                    //console.log(_page+" "+ _total_number);
                    _page += 1;
                    addLbsSearch(point,1);
                    _myscroll.refresh();
                    _scrollFlag = 0;
                    $("#pullUp").text("下拉加载更多");
                }
                if(_page * 50 >= _total_number )
                {
                    $("#pullUp").text("已无更多内容");
                }
             });
             
            //** 滚动时 切换地图 显示当前数组的点集
            function ShowUl(Ulcount) { 
                _map.clearMap();        
                var isReturnTwoPoint = 0;       // 标识符 用于判断结果的第一、二个点是否为新加点                
                _map.add(_SearchArray[Ulcount]);  
            }

        /*********************************************************************/
        /** 其他函数
         ** 影响不大的函数
         */
            //** 函数 ： changeIframe()
            //** 功能 ： 在页面按钮 "确定" 点击 / 地图被点击 后，更新iframe内容为最新查找内容
            //** 发生过程：在页面按钮 "确定" 点击 / 地图被点击 
            function changeIframe() {
                // 更新iframe地址
                var tag = $("#tags").val();
                var sort = $("#sort").val();
                //var url = document.getElementById('test').src;
                var url = "https://m.weibo.cn/p/2308580053"+point.lng+"_"+point.lat;;
                url = url.substring(0, 30);
                url = url+tag+point.lng+"_"+point.lat+"_"+3000+"_"+sort;
                document.getElementById('test').src = url;
            }  
            
            /*********************************************************************/
            /** 函数6： addMarker(point)
             ** 功能 ： 将一个点显示到地图上
             ** 参数 ： point[lat,lng]  需要在地图上显示的坐标
             ** 发生过程：地图被点击后 / 输入提示下拉项被点击
             */ 
            function addMarker(point) {
                var marker = new AMap.Marker({position:point});
                markersArray.push(marker);  
                clearOverlays(); 
                //_map.clearMap();
                _map.add(marker);  
            }
            
            /*********************************************************************/
            /** 函数7： clearOverlays()
             ** 功能 ： 清除 addMarker() 显示在地图上的点
             ** 发生过程：addMarker()
             */             
            function clearOverlays() {  
                if (markersArray) {  
                    for (i in markersArray) {  
                        _map.remove(markersArray[i])  
                    }  
                }  
                SearchResult = [] ;
            }
            
            /*********************************************************************/
            /** 函数8： autodivheight()
             **  功能： 根据浏览器窗口高度 自动调整div 高度
             */
            function autodivheight(){ //函数：获取尺寸
                //获取浏览器窗口高度
                var winHeight=0;
                if (window.innerHeight)
                    winHeight = window.innerHeight;
                else if ((document.body) && (document.body.clientHeight))
                    winHeight = document.body.clientHeight;
                //通过深入Document内部对body进行检测，获取浏览器窗口高度
                if (document.documentElement && document.documentElement.clientHeight)
                    winHeight = document.documentElement.clientHeight;
                //DIV高度为浏览器窗口的高度
                document.getElementById("test")    .style.height= (winHeight-45 - 30) +"px";
                document.getElementById("out_div").style.height=  (winHeight-45 - 30) +"px";
                document.getElementById("allmap")  .style.height= (winHeight-45 - 2) +"px";
            }
            
            /*********************************************************************/
            /** 函数9： isNull(a)
             ** 功能 ： 判断参数a  是否为空 / 未定义
             */  
            function isNull(a) {  
                return (a == '' || typeof(a) == 'undefined' || a == null) ? true : false;  
            }
            
        })
    </script>
</head>  
<body>
    <div class="row" style="width:100%">
        <div class="col-md-7" style="padding:0px;"> 
            <div class="form-group form-inline" id="title" style="height:10vh;">
                <label for="location">当前位置：</label>
                <span id="address"></span>&nbsp;&nbsp;
                    <div id="ifCheck_div" style="display:none;">
                        <input type="checkbox" id="ifCheck" >搜索关键词
                    </div>
                <br>
                <select class="form-control input-sm" id="tags"></select>
                <select class="form-control input-sm" id="sort">
                    <option value="0" selected="selected">时间排序</option>
                    <option value="1">距离排序</option>
                    <option value="2">热度排序</option>
                </select>
                <div id="search_div" style="display:none;">
                    <input type="text" id="grey_search" size="20" placeholder="选择当前位置后搜索关键词" style="width:15vw;display:none;"/>
                    <div id="NotCity" style="display:inline">
                        <select class="form-control input-sm" id="poiSource">
                            <option value="0">全部</option>
                            <option value="1">携程</option>
                            <option value="2">点评</option>
                        </select>
                        <input type="checkbox" id="ifPassCheck"  checked="checked">只看通过审核
                    </div>
                    <input type="checkbox" id="ifOnlyCity" >只搜同城
                    <button class="btn btn-sm" id="submit">确定</button>
                </div>
            </div>
            <div style="padding:0;">
                <iframe id="test" src="" ></iframe> 
                <div id="out_div" style="border-style: solid; border-width: 0.5vh;OVERFLOW: hidden;display:none;">
                    <div  id="wrapper"  style="height:90%;margin:0 ">
                     <div class="Scrollbar" style="transition-property: transform; transform-origin: 0px 0px 0px; transform: translate(0px, -7487px) scale(1) translateZ(0px);">
                           <div id="totalNum" class="pulldown-tips" style="text-align:center;display:none">共查到 <p id="tN" style="display:inline;padding:0;margin:0;">X</p> 条记录</div>  
                           <div id="test_div" >
                                <ul  style="padding:0;"></ul>
                            </div>
                       <br><br>
                       <div id="pullUp" style="text-align:center;margin-top:2vh;">
                            <div class="pullUpLabel">下拉加载更多</div> 
                       </div>
                     </div>
                   </div>

                </div>
            </div>  
        </div>
        <div class="col-md-5" style="padding:0px">
            <div id="r-result">请输入:<input type="text" id="suggestId" size="20"  style="width:300px;" /></div>
            <div id="searchResultPanel" style="border:1px solid #C0C0C0;width:150px;height:50px; display:none;"></div>
            <div id="allmap"></div>
        </div>
    </div>
</body>  
</html>    