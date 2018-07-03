<?php



require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use App\Services\Connector;

date_default_timezone_set("Asia/Baku");
$dom = new Dom();
$servername = "localhost";
$username = "root";
$password = "mursel0552691525";
$dbname = "adatask";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$table = "CREATE TABLE IF NOT EXISTS `news` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `news_title` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `news_url` text NOT NULL,
    `news_date` datetime NOT NULL,
    `video_url` varchar(255) DEFAULT NULL,
    `category_name` varchar(50) NOT NULL,
    `img_Url` text NOT NULL,
    PRIMARY KEY (`id`)
   ) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8";
if ($conn->query($table) === TRUE) {
    echo "Table was created";
} else {
    echo "Error: " . $table . "<br>" . $conn->error;
}

// $sql = "INSERT INTO news (author, category, description , img , link , news_date , reader_count , title,video_link)
// VALUES ('MUrsel Murelov', 'Criminal', 'there is description text'
//  ,'image link here','Tnere is news link', '06.26.2018 | 01:15', '1234' , 'Cinayet Hadisesi bash verib', 'There is videoLink')";

// if ($conn->query($sql) === TRUE) {
//     echo "New record created successfully";
// } else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
// }



$catUrlParam = array("siyaset-2", "sosial-3", "iqtisadiyyat-4" ,"kriminal-5", "dunya-6", "qafqaz-7" , "idman-9" ,"sou-20");
$pages = array("?page=1","?page=2" , "?page=3","?page=4" ,"?page=5" , "?page=6");

ini_set('max_execution_time', 300); //300 seconds = 5 minutes

foreach ($catUrlParam as $type) {
    
    // echo 'Type: ' . $index . ' => ' . $type . '<br />';
    foreach ($pages as $page) {
        // echo 'Page: ' . $pageIndex . ' => ' . $page . '<br />';
        $dom->loadFromUrl('http://qafqazinfo.az/news/category/'.$type.$page);
        $contents = $dom->find('.search');
        foreach ($contents as $content) {
            $date = date('d-m-Y H:i');
            $oneWeek = date('d-m-Y H:i', time() - (7 * 24 * 60 * 60));
            $news_datetime = trim($content->find(".col-lg-9 div")->innerhtml);
            $news_d;


            if (strlen($news_datetime) < 16) {
                $news_d = DateTime::createFromFormat('H:i', $news_datetime);
            } else {
                $news_d = DateTime::createFromFormat('d.m.Y H:i', $news_datetime);
            }
            if (strtotime($oneWeek) < strtotime($news_d->format('Y-m-d'))) {
                $img = $content->find(".img-responsive")->getAttribute('src');
                $name = $content->find(".img-responsive")->getAttribute('title');
                $news_link = $content->find("a")->getAttribute('href');
                $innerDOM = new Dom();
                $innerDOM->loadFromUrl($news_link);

                $currentNews = $innerDOM->find('.panel-default');

                $news_content = $currentNews->find("p");
                $news_title = $currentNews->find("a")->getAttribute('title');
                $news_category  =$currentNews->find(".col-lg-3 a")->getAttribute("title");
                $news_videoLink =  $currentNews->find("img.vid-card_img");

                if (!count($news_videoLink)) {
                    $news_videoLink = null;
                } else {
                    $news_videoLink = $news_videoLink->getAttribute('src');
                }
                $dd = $news_d->format('Y-m-d H:i:s');
                $sqlNews = "INSERT INTO news (news_title, description, news_url, news_date, video_url, category_name, img_Url) 
                VALUES ('$news_title', '$news_content', '$news_link', '$dd', '$news_videoLink', '$news_category', '$img')";

                if ($conn->query($sqlNews) === TRUE) {
                    echo "New record created successfully";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }






            } else {
                break;
            }
        }
    }
}