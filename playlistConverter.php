<?php
/*
 |------------------------------------------------------------------------------
 | config
 |------------------------------------------------------------------------------
 */
define("SRC_DIR_PATH" ,  dirname(__FILE__) . '/src/'); // src path
define("DIST_DIR_PATH" , dirname(__FILE__) . '/dist/'); // dist path
define("ITUNES_PATH" , '/Volumes/HDD/iTunes/iTunes Media/music/'); // itunes path
define("WALKMAN_MUSIC_PATH","/MUSIC/Music/"); // WALKMAN music dir path
define("INTERVAL" , 60 * 10); // 10min (60sec * 10min)

/*
 |------------------------------------------------------------------------------
 | function
 |------------------------------------------------------------------------------
 */
/**
 * INTERVAL以内に更新されたm3u拡張子のファイルを、対象ディレクトリにコピーする
 * 該当ファイルが無ければfalseを返す
 * 
 * @return array|bool
 */
function copy_playlist()
{
    $result = [];
    foreach (glob(SRC_DIR_PATH . "*.m3u") as $file_path) {
        $file_name   = basename($file_path);
        $update_time = filemtime ($file_path);

        if(time() - $update_time >= INTERVAL){
            continue;
        }
        copy(SRC_DIR_PATH . $file_name,DIST_DIR_PATH . $file_name);
        $result[] = $file_name;
    }
    if(empty($result)){
        return false;
    }else{
        return $result;
    }
}

/**
 * 対象ファイルをWALKMAN用のプレイリストに変換する
 * 対象ファイル：Macで作られたm3u拡張子のファイル
 * 
 * @param string 変換するファイルのパス
 */
function convert_playlist($file)
{
    $rows    = file($file);
    $fp      = fopen($file,'w');
    $pattern = '#' . ITUNES_PATH . '#';

    foreach ($rows as $key => $row) {
        $insert_data = iconv("UTF-8-MAC", "UTF-8", $row);
        $insert_data = preg_replace($pattern,WALKMAN_MUSIC_PATH,$insert_data);
        fwrite($fp,$insert_data);
    }
    fclose($fp);
    readfile($file);
}

/*
 |------------------------------------------------------------------------------
 | main
 |------------------------------------------------------------------------------
 */
echo('Converting...' . "\n");
$copy_list = copy_playlist();

if(!$copy_list){
    echo('There is no file to convert.' . "\n");
    exit();
}
foreach($copy_list as $file_name){
    convert_playlist(DIST_DIR_PATH . $file_name);
}

echo('success!' . "\n");
echo('Converted from ' . SRC_DIR_PATH . ' to ' . DIST_DIR_PATH . "\n");
exit();
?>