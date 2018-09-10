<?php
set_time_limit(0);
/*****连接数据库start*******/
$dbhost = "localhost";
$username = "root";
$userpass = "root";
$dbdatabase = "eoews";
$db_con = mysqli_connect($dbhost,$username,$userpass) or die("Unable to connect to the MySQL!");
//选择一个需要操作的数据库
mysqli_select_db($db_con,$dbdatabase);
/***********连接数据库****end******/
$page_size    =    10000; //每页条数
//1w个地址生成一个子地图，判断需要生成几个？
$countQuery = mysqli_query($db_con,"select count(eoe_id) from apps where status = 1 ");
$count = implode(',' , mysqli_fetch_row($countQuery));
$appsCountPage = ceil($count/$page_size);  //分几个文件

$articleCountQuery = mysqli_query($db_con,"select count(id) from article where status = 1 ");

$articleCount = implode(',' , mysqli_fetch_row($articleCountQuery));

$articleCountPage = ceil($articleCount/$page_size);  //分几个文件



www_create_index($appsCountPage,$articleCountPage);

www_create_child($db_con,$appsCountPage,$articleCountPage,$page_size);

//百度生成主sitemap  www
function www_create_index($page_count,$articleCountPage) {

    $content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
    $content .= "<sitemapindex>";
    for($i=1;$i<=$page_count;$i++) {

        $content .="<sitemap>";
        $content .= "<loc>https://www.eoews.com/sitemap/sitemapApps$i.xml</loc>";
        $content .= "<lastmod>".date('Y-m-d')."</lastmod>";
        $content .= "</sitemap>";
    }

    for($i=1;$i<=$articleCountPage;$i++) {

        $content .="<sitemap>";
        $content .= "<loc>https://www.eoews.com/sitemap/sitemapArticle$i.xml</loc>";
        $content .= "<lastmod>".date('Y-m-d')."</lastmod>";
        $content .= "</sitemap>";
    }

    $content .= "</sitemapindex>";
    file_put_contents("www_sitemap.xml",$content);
}

//百度生成子sitemap  www
function www_create_child($db_con,$appsCountPage,$articleCountPage,$page_size) {
    for($i=0;$i<$appsCountPage;$i++) {

        $count = $i * $page_size;

        $Appsresult = mysqli_query($db_con,"SELECT eoe_id,updated_time FROM apps where status = 1 ORDER BY updated_time desc limit $count,$page_size");//
//提取数据
        if($Appsresult){
            $str = '<?xml version="1.0" encoding="utf-8"?>';
            $str .= '<urlset>';
            while($row = mysqli_fetch_array($Appsresult,MYSQLI_ASSOC))  {
                $str .= '<url>';
                $str .= "<loc>https://www.eoews.com/soft/{$row["eoe_id"]}.html</loc>";
                $str .= "<lastmod>" . date('Y-m-d',strtotime($row["updated_time"])) . "</lastmod>";
                $str .= "<changefreq>daily</changefreq>";
                $str .= "<priority>0.9</priority>";
                $str .= '</url>';
            }
            $str .= '</urlset>';
            file_put_contents('www_sitemap/sitemapApps'.($i+1).".xml" ,$str);
        }else{
            die("fetch data failed!");
        }
    }
    for($i=0;$i<$articleCountPage;$i++) {

        $count = $i * $page_size;

        $Articleresult = mysqli_query($db_con,"SELECT id,updated_time FROM article where status = 1 ORDER BY updated_time desc limit $count,$page_size");//
//提取数据
        if($Articleresult){
            $str = '<?xml version="1.0" encoding="utf-8"?>';
            $str .= '<urlset>';
            while($row = mysqli_fetch_array($Articleresult,MYSQLI_ASSOC))  {
                $str .= '<url>';
                $str .= "<loc>https://www.eoews.com/article/{$row["id"]}.html</loc>";
                $str .= "<lastmod>" . date('Y-m-d',strtotime($row["updated_time"])) . "</lastmod>";
                $str .= "<changefreq>daily</changefreq>";
                $str .= "<priority>0.9</priority>";
                $str .= '</url>';
            }
            $str .= '</urlset>';

            file_put_contents('www_sitemap/sitemapArticle'.($i+1).".xml" ,$str);
        }else{
            die("fetch data failed!");
        }
    }
}

