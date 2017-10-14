<?php

namespace app\controllers;

use app\controllers\BaseController;
use app\models\Category;
use app\models\Comment;
use app\models\product\Product as ProductModel;
use app\models\User;
use mdm\admin\models\AuthItem;
use renk\yiipal\components\ExportData;
use renk\yiipal\helpers\FileHelper;
use Yii;
use yii\rbac\Item;

class CommentController extends BaseController
{

    /**
     * 添加备注
     * @return string
     */
    public function actionCreate()
    {
        $model = new Comment();
        if($this->post()){
            $model->load($this->post());
            $model->load($this->get(),'');
            $model->validate();
            $errors = $model->getErrors();
            if(empty($errors)){
                $model->save(false);
                return $this->json_output();
            }else{
                $error = reset($errors);
                return $this->json_output(['status'=>0,'msg'=>reset($error)]);
            }
        }

        $commentStyle = $this->get("comment_style",'');
        //版图管理的备注
        if($commentStyle == 'template'){
            $availableRoles = ['admin','Backend-Manage','studio-manager','market','factory-template-member','rendering-member','factory-manger'];
            $availableUids = '';
            $user = new User();
            $roles = $user->getRolesWithUsers();
            foreach($roles as $role){
                if(in_array($role['role_name'],$availableRoles)){
                    $availableUids .= ",".$role['user_id'];
                }
            }
            return $this->renderAjax('update_template', [
                'model' => $model,
                'availableUids'=>$availableUids
            ]);
        }

        $user = new User();
        $roles = $user->getRolesWithUsers();
        $tree = [];
        foreach($roles as $role){
            $tree[$role['role_name']]['text']= $role['role_label'];
            $tree[$role['role_name']]['value']= $role['role_name'];
            $tree[$role['role_name']]['state']= ['expanded'=>false];
            $tree[$role['role_name']]['type']= 'role';
            $userArray = new \stdClass();
            $userArray->text = $role['username'];
            $userArray->user_id= $role['user_id'];
            $userArray->type= 'user';
            $tree[$role['role_name']]['nodes'][] = $userArray;
        }
        $defaultRoles = ['admin','Backend-Manage','distribution-permission','order-manager','service','service-manger'];
        $defaultRolesIds = [];
        $index = 0;
        foreach($tree as $role_name=>$row){
            if(in_array($role_name,$defaultRoles)){
                $defaultRolesIds[] = $index;
            }
            $index++;
            $index += count($tree[$role_name]['nodes']);
        }

        $userTree = json_encode(array_values($tree));
        return $this->renderAjax('update', [
            'model' => $model,
            'userTree' =>$userTree,
            'defaultRolesIds' =>json_encode($defaultRolesIds)
        ]);
    }

    /**
     * 备注列表
     * @param $target_id
     * @param $type
     * @return string
     */
    public function actionList($target_id, $type,$group=false){
        $query = Comment::find()->with('user')
            ->where(['target_id'=>$target_id,'type'=>$type])
            ->orderBy(["comment.created_at"=>SORT_DESC])
            ;
        if($group == 'confirm'){
            $subject = [
                Comment::COMMENT_TYPE_CHANGE_PRODUCT,
                Comment::COMMENT_TYPE_CHANGE_PRODUCT_ENGRAVINGS,
                Comment::COMMENT_TYPE_CHANGE_PRODUCT_SIZE,
                Comment::COMMENT_TYPE_CHANGE_PRODUCT_NUMBER,
                Comment::COMMENT_TYPE_CHANGE_ADDRESS,
                Comment::COMMENT_TYPE_CHANGE_SHIPPING_METHOD,
            ];
            $query->andWhere(["in","subject", $subject]);
        }
        $comments = $query->all();
        foreach($comments as $comment){
            $this->markAsRead($comment);
        }
        return $this->renderAjax('list', [
            'comments' => $comments,
        ]);
    }

    /**
     * 标记备注已读
     * @param $comment
     */
    private function markAsRead($comment){
        $currentUid = $this->getCurrentUid();
        $readUids = $comment->read_uids;
        $readUids = explode(",",$readUids);
        if(!in_array($currentUid,$readUids)){
            $comment->read_uids .= ','.$currentUid;
            $comment->read_uids = trim($comment->read_uids,',');
            $comment->save();
        }
    }
}
