<?php

$dir = './';
$ignore_file = ".gitignore";

//ignoreの定義ファイルの配列化
$ignore_array = ignore_file_parse($ignore_file);

$check_dirs = [$dir];
$count = 0;
$ignore_count = 0;

while( $check_dirs ) {
    $dir_path = $check_dirs[0] ;
    $handle = opendir($dir_path);

    if( is_dir($dir_path) && $handle ) {
        while( ( $file = readdir( $handle ) ) ) {
            if( in_array( $file, [ ".", ".." ] ) ){
                continue;
            }
            $path = rtrim ( $dir_path, "/" ) . "/" . $file ;
            if ( filetype($path) === "dir" ) {
                $check_dirs[] = $path;
            } else {
                if ( ignore_check($path) ) {
                    $ignore_count += 1;
                } else {
                    $count += 1;
                }
            }
        }
    }
    array_shift( $check_dirs ) ;
}

print("file:".$count."\n");
print("ignore_file:".$ignore_count."\n");

/**
 * ignoreファイルを解析して配列化する
 *
 * @param string $file
 * @return array
 */
function ignore_file_parse($file){
    $array = explode("\n", file_get_contents($file));
    $array = array_map('trim', $array);
    $array = preg_replace('/#.*$/', '', $array); //コメントアウトを削除する
    $array = array_filter($array, 'strlen');
    $array = array_values($array);
    return $array;
}

/**
 * ignoreファイルにより無視されるpathを判別しbooleanで返す
 *  無視される: true
 *  無視されない: false
 *
 * @param string $path
 * @return boolean
 */
function ignore_check($path){
    global $ignore_array;
    $path = preg_replace('/^\.\//','', $path);
    $is_ignore = false;

    foreach($ignore_array as $v) {
        $pattern = preg_replace('/^\//','', $v);
        if ( preg_match('/\/$/', $pattern)){
            $pattern = $pattern."*";
        }
        if ( fnmatch($pattern, $path)){
            $is_ignore = true;
        }

        // ignoreの条件に!が付いていた場合の処理
        if ( preg_match('/^!/', $v) && $is_ignore) {
            $pattern2 = preg_replace('/^!/', '', $v);
            if ( fnmatch($pattern2, $path) ) {
                $is_ignore = false;
                break;
            }
        }
    }
    if ( $is_ignore ) {
        print('x: '.$path."\n");
    } else {
        print('o: '.$path."\n");
    }
    return $is_ignore;
}
