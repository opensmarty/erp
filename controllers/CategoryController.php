<?php
/**
 * CategoryController.php
 *
 * @Author: renk(renk@yiipal.com)
 * @mail: 359876077@qq.com
 * @Wechat: renk03
 * @Date: 2016/5/17
 */

namespace app\controllers;


use app\models\Category;

class CategoryController extends BaseController{
    /**
     * 分类列表
     */
    public function actionIndex(){
        $model = new Category();
        $model->getFullTree(1);
        return $this->render('index');
    }

    public function actionOperation($operation){
        $fs = new Category();
        try {
            $rslt = null;
            switch($operation) {
                case 'analyze':
                    var_dump($fs->analyze(true));
                    die();
                    break;
                case 'get_node':
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
                    $temp = $fs->get_children($node);
                    $rslt = array();
                    foreach($temp as $v) {
                        $rslt[] = array('id' => $v['id'], 'text' => $v['name'], 'children' => ($v['rgt'] - $v['lft'] > 1));
                    }
                    break;
                case 'get_children':
                    $node = isset($_GET['parent_id']) && $_GET['id'] == '#' ? (int)$_GET['parent_id'] : (int)$_GET['id'];
                    $temp = $fs->get_children($node);
                    $rslt = array();
                    foreach($temp as $v) {
                        $rslt[] = array('id' => $v['id'], 'text' => $v['name'], 'state'=>['opened'=>true], 'children' => ($v['rgt'] - $v['lft'] > 1));
                    }
                    break;
                case "get_content":
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? $_GET['id'] : 0;
                    $node = explode(':', $node);
                    if(count($node) > 1) {
                        $rslt = array('content' => 'Multiple selected');
                    }
                    else {
                        $temp = $fs->get_node((int)$node[0], array('with_path' => true));
                        $rslt = array('content' => 'Selected: /' . implode('/',array_map(function ($v) { return $v['name']; }, $temp['path'])). '/'.$temp['name']);
                    }
                    break;
                case 'create_node':
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
                    $temp = $fs->mk($node, isset($_GET['position']) ? (int)$_GET['position'] : 0, array('name' => isset($_GET['text']) ? $_GET['text'] : 'New node'));
                    $rslt = array('id' => $temp);
                    break;
                case 'rename_node':
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
                    $rslt = $fs->rn($node, array('name' => isset($_GET['text']) ? $_GET['text'] : 'Renamed node'));
                    break;
                case 'delete_node':
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
                    $rslt = $fs->rm($node);
                    break;
                case 'move_node':
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
                    $parn = isset($_GET['parent']) && $_GET['parent'] !== '#' ? (int)$_GET['parent'] : 0;
                    $rslt = $fs->mv($node, $parn, isset($_GET['position']) ? (int)$_GET['position'] : 0);
                    break;
                case 'copy_node':
                    $node = isset($_GET['id']) && $_GET['id'] !== '#' ? (int)$_GET['id'] : 0;
                    $parn = isset($_GET['parent']) && $_GET['parent'] !== '#' ? (int)$_GET['parent'] : 0;
                    $rslt = $fs->cp($node, $parn, isset($_GET['position']) ? (int)$_GET['position'] : 0);
                    break;
                default:
                    throw new \Exception('Unsupported operation: ' . $_GET['operation']);
                    break;
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $rslt;
        }
        catch (Exception $e) {
            header($_SERVER["SERVER_PROTOCOL"] . ' 500 Server Error');
            header('Status:  500 Server Error');
            echo $e->getMessage();
        }
    }

    public function actionAttachAttributes(){
        $model = new Category();
        return $this->render('attach-attributes',[
            'model' => $model,
        ]);
    }
}