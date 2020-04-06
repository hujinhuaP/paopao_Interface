<?php
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$json_str = '';
if($method == 'POST'){
    $json_str = $_POST['json_str'] ?? '';
    if($json_str){
        $arr = json_decode($json_str,true);
        if(!$arr){
            echo 'json数据错误';
        }else{
            if(!isset($arr['d'])){
                echo '找不到参数d';
            }else{
                foreach ($arr['d'] as $key=>$item){
                    $type = 'String';
                    if(is_array($item)){
                        if(count($item) == count($item,1)){
                            $type = 'object';
                        }else{
                            $type = 'object[]';
                        }
                    }elseif(is_numeric($item)){
                        $type = 'number';
                    }
                    printf('     * @apiSuccess {%s} d.%s  <br/>',$type,$key);
                    if($type == 'object'){
                        foreach ($item as $key2=>$item2){
                            $type = 'String';
                            if(is_array($item2)){
                                $type = 'object';
                            }elseif(is_numeric($item2)){
                                $type = 'number';
                            }
                            printf('     * @apiSuccess {%s} d.%s  <br/>',$type,$key . '.' .$key2);
                            if($type == 'object'){
                                foreach ($item2 as $key3=>$item3){
                                    $type = 'String';
                                    if(is_array($item3)){
                                        $type = 'object';
                                    }elseif(is_numeric($item3)){
                                        $type = 'number';
                                    }
                                    printf('     * @apiSuccess {%s} d.%s  <br/>',$type,$key . '.' .$key2 . '.' .$key3);
                                }
                            }elseif($type == 'object[]'){
                                foreach ($item[0] as $key3=>$item3){
                                    $type = 'String';
                                    if(is_array($item3)){
                                        $type = 'object';
                                    }elseif(is_numeric($item3)){
                                        $type = 'number';
                                    }
                                    printf('     * @apiSuccess {%s} d.%s  <br/>',$type,$key . '.' .$key2 . '.' .$key3);
                                }
                            }
                        }
                    }elseif($type == 'object[]'){
                        foreach ($item[0] as $key3=>$item3){
                            $type = 'String';
                            if(is_array($item3)){
                                $type = 'object';
                            }elseif(is_numeric($item3)){
                                $type = 'number';
                            }
                            printf('     * @apiSuccess {%s} d.%s  <br/>',$type,$key . '.' .$key3);
                        }
                    }
                }
            }
        }
    }else{
        echo '参数错误';
    }
}
?>
<form method="post" action="">
<textarea cols="200" rows="20" name="json_str"><?php echo $json_str ?></textarea>
    <input type="submit" value="提交" />
</form>

