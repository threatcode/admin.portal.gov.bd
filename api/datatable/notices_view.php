<?php
    if (!$_GET['domain_id'])
    exit;
	//echo $domain_info['id'];
    $domain_id = $_GET['domain_id'];
    // $q = $_GET['categoryName'];
    $lang = $_GET['lang'];
    $subdomain = $_GET['subdomain'];
    $domainName = $_SERVER['HTTP_HOST'];
    $contentType = $_GET['content_type'];
    $contentTableName = 'npfministryadmin.npf_content_'.$contentType;
    // echo $domain_id.'--'.$q.'--'.$lang.'--'.$subdomain.'--'.$contentType;
    // exit;
    
    // $contentType = $_POST['content_type'];
    // $designation_id = $_POST['designation_id'];
    // echo "<pre>";
    // print_r($designation_id);exit;
    $primaryKey = 'id';
    // $lang = isset($_POST['lang']) ? $_POST['lang'] : 'bn';
    // $lang = 'bn';
    //print_r($columns);exit;

    // SET TIME ZONE
    date_default_timezone_set("Asia/Dhaka");
    // echo date_default_timezone_get();
    $currentDate  = date("Y-m-d");

    // Function
    function engToBngNum($num){
        // Number
        $num =str_replace('0','০',$num);
        $num =str_replace('1','১',$num);
        $num =str_replace('2','২',$num);
        $num =str_replace('3','৩',$num);
        $num =str_replace('4','৪',$num);
        $num =str_replace('5','৫',$num);
        $num =str_replace('6','৬',$num);
        $num =str_replace('7','৭',$num);
        $num =str_replace('8','৮',$num);
        $num =str_replace('9','৯',$num);
        // Mpnth
        $num = str_replace("January", "জানুয়ারি", $num);
        $num = str_replace("February", "ফেব্রুয়ারি", $num);
        $num = str_replace("March", "মার্চ", $num);
        $num = str_replace("April", "এপ্রিল", $num);
        $num = str_replace("May", "মে", $num);
        $num = str_replace("June", "জুন", $num);
        $num = str_replace("July", "জুলাই", $num);
        $num = str_replace("August", "আগস্ট", $num);
        $num = str_replace("September", "সেপ্টেম্বর", $num);
        $num = str_replace("October", "অক্টোবর", $num);
        $num = str_replace("November", "নভেম্বর", $num);
        $num = str_replace("December", "ডিসেম্বর", $num);

        return $num;
        
    }

    // MYSQL server connection information
    include('../../dbconnect.php');
    // $sql_details = array(
    //     'user' => $username,
    //     'pass' => $password,
    //     'db' => $dbphlcn,
    //     'host' => $servername
    // );

    $dbHost   = $servername;        
    $dbName     = $dbphlcn; 
    $dbUser = $username;             
    $dbPassword = $password; 
    $dbCharSet = 'utf8';

    $dsn = 'mysql:host='.$dbHost.';dbname='.$dbName.';charset='.$dbCharSet;
    $db = new \PDO($dsn,$dbUser,$dbPassword);
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    $db->exec("set names ".$dbCharSet);

    if(isset($_POST['iDisplayStart']) && $_POST['iDisplayStart']>0)
        $from  = $_POST['iDisplayStart'];
    else
        $from = 0;

    if(isset($_POST['iDisplayLength']) && $_POST['iDisplayLength']>0)
        $length  = $_POST['iDisplayLength'];
    else
        $length = 20;

    if(isset($_POST['sEcho']) && $_POST['sEcho']>0)
        $sEcho = $_POST['sEcho'];
    else
        $sEcho = 1;

    if(isset($_POST['sSearch']) && $_POST['sSearch']!='')
        $search = $_POST['sSearch'];
    else
        $search = null;


    //$suggestionPosition = null;
    //if(!empty($search)) {
    //    $suggestionPosition = $this->Position->getPositionNameSuggestion($search);
    //    $suggestionPosition = array_keys($suggestionPosition);
    //}

    /*Custom Query*/
    if($search!=null)
        $query = "SELECT COUNT(*) as num
                    FROM $contentTableName pmspc
                    WHERE   
                        pmspc.active=1 AND 
                        pmspc.publish=1 AND  
                        pmspc.domain_id=$domain_id AND
                        ('$currentDate' < DATE(pmspc.`archivedate`) OR pmspc.`archivedate` is NULL) AND 
                        ('$currentDate' >= DATE(pmspc.`pubdate`) OR pmspc.`pubdate` is NULL)  AND
                        (pmspc.title_bn like '%".$search."%' OR pmspc.title_en like '%".$search."%')
                    ORDER BY pmspc.`pubdate` DESC, pmspc.`created` DESC";
    else
        $query = "SELECT COUNT(*) as num
                    FROM $contentTableName pmspc
                    WHERE   
                        pmspc.active=1 AND 
                        pmspc.publish=1 AND  
                        pmspc.domain_id=$domain_id AND 
                        ('$currentDate' < DATE(pmspc.`archivedate`) OR pmspc.`archivedate` is NULL) AND 
                        ('$currentDate' >= DATE(pmspc.`pubdate`) OR pmspc.`pubdate` is NULL)
                    ORDER BY pmspc.`pubdate` DESC, pmspc.`created` DESC";

    $dbs = $db->prepare($query);
    $dbs->execute();
    $rows = $dbs->fetchAll(PDO::FETCH_ASSOC);
    $dbs->closeCursor();
    $countUser = $rows[0]['num'];

    /*Custom Query*/
    //$query = "SELECT id, name, designation, serviceNo, divisionId, atypeId, sectorId, gadget, cstatus FROM joddha WHERE atypeId IN ( 'বীরশ্রেষ্ঠ', 'বীর উত্তম', 'বীর বিক্রম', 'বীর প্রতীক' ) ORDER BY gadget ASC limit ".$from.", ".$length."";
    if($search!=null)
        $query = "SELECT
                    pmspc.id, 
                    pmspc.title_bn title_bn,
                    pmspc.title_en title_en,
                    pmspc.pubdate, 
                    pmspc.attachments, 
                    pmspc.uploadpath,
                    pmspc.lastmodified,
                    pmspc.created, 
                    pmspc.archivedate
                FROM $contentTableName pmspc
                WHERE   
                    pmspc.active=1 AND 
                    pmspc.publish=1 AND  
                    pmspc.domain_id=$domain_id AND 
                    ('$currentDate' < DATE(pmspc.`archivedate`) OR pmspc.`archivedate` is NULL) AND 
                    ('$currentDate' >= DATE(pmspc.`pubdate`) OR pmspc.`pubdate` is NULL) AND 
                    (pmspc.title_bn like '%".$search."%' OR pmspc.title_en like '%".$search."%')
                ORDER BY pmspc.`pubdate` DESC, pmspc.`created` DESC
                LIMIT ".$from.", ".$length;
    else
        $query = "SELECT
                    pmspc.id, 
                    pmspc.title_bn title_bn,
                    pmspc.title_en title_en,
                    pmspc.pubdate, 
                    pmspc.attachments, 
                    pmspc.uploadpath,
                    pmspc.lastmodified,
                    pmspc.created, 
                    pmspc.archivedate 
                FROM $contentTableName pmspc
                WHERE   
                    pmspc.active=1 AND 
                    pmspc.publish=1 AND  
                    pmspc.domain_id=$domain_id AND 
                    ('$currentDate' < DATE(pmspc.`archivedate`) OR pmspc.`archivedate` is NULL) AND 
                    ('$currentDate' >= DATE(pmspc.`pubdate`) OR pmspc.`pubdate` is NULL)
                ORDER BY pmspc.`pubdate` DESC, pmspc.`created` DESC
                LIMIT ".$from.", ".$length."";

// echo $query;
// die();
    $dbs = $db->prepare($query);
    $dbs->execute();
    $result = $dbs->fetchAll(PDO::FETCH_ASSOC);
    $dbs->closeCursor();

$response = array(
    "sEcho" => $sEcho,
    "iTotalRecords" => $countUser,
    "iTotalDisplayRecords" => $countUser,
    "data" => array()
);

foreach ($result as $value) {
    $entry = array(
        engToBngNum($countUser),
        ($lang == 'bn') ? str_replace('"', '', str_replace("/", "-", trim(strip_tags($value['title_bn'])))) : str_replace('"', '', str_replace("/", "-", trim(strip_tags($value['title_en'])))),
        ($lang == 'bn') ? engToBngNum($value['pubdate']) : $value['pubdate'],
    );

    if (!empty($value['uploadpath'])) {
        $uploadpath = str_replace("-", "_", $value['uploadpath']);
        $fileBasePath = "//{$domainName}/sites/default/files/files/{$subdomain}/{$contentType}/{$uploadpath}";
        $fileArray = unserialize($value['attachments']);

        $fileLinks = array();
        foreach ($fileArray as $file) {
            $fileName = $file['name'];
            if ($fileName != '') {
                $filePath = "{$fileBasePath}/{$fileName}";
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileIconBasePath = "//{$domainName}/themes/responsive_npf/img/file-icons/32px/";
                $fileIconPath = "{$fileIconBasePath}{$fileExtension}.png";
                $fileLinks[] = "<a href=\"{$filePath}\" target=\"_blank\" download><img src=\"{$fileIconPath}\" alt=\"{$fileExtension}\" class=\"file-icon\"></a>";
            }
            else{
                if ($file['name'] == '' && !empty($file['link'])) {
                    $link = $file['link'];
                    if ($lang == 'bn') {
                        $fileLinks[] = "<a class=\"btn\" href=\"$link\">বিস্তারিত</a>";
                    }
                    else{
                        $fileLinks[] = "<a class=\"btn\" href=\"$link\">Details</a>";
                    }
                }
                else
                {
                    $fileLinks[] = "<a href=\"javascript:void(0)\">.</a>";
                }
            }
        }

        $entry[] = implode(' ', $fileLinks);
    }

    $response["data"][] = $entry;
    $countUser--;
}

   header('Content-Type: application/json');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
die();

?>