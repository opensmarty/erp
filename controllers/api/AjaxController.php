<?php

/**
tomConroller.php
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/5/23
 */

namespace app\controllers\api;

use app\controllers\BaseController;
use app\models\File;
use app\models\product\Product;
use app\models\product\ProductTemplate;
use app\models\product\ProductTemplateAttributes;
use app\models\supplies\Material;

class AjaxController extends BaseController
{
    public $enableCsrfValidation = false;

    /**
     * 获取产品信息
     * @param $sku
     * @return array
     */
    public function actionGetProductInfo()
    {
        $sku = $this->post('sku');
        $product = Product::find()->where(['sku' => trim($sku)])->one();
        return $this->json_output(['data' => $product]);
    }

    /**
     * 删除产品图片
     * @param $id
     * @return array
     */
    public function actionRemoveFile()
    {
        $pid = $this->post('pid', '');
        $fid = $this->post('fid', '');
        $product = Product::findOne($pid);
        if (empty($product)) {
            return $this->json_output(['status' => '0', 'msg' => '处理失败', 'command' => '']);
        }
        $fids = explode(",", $product->fids);

        $key = array_search($fid, $fids);
        unset($fids[$key]);
        $product->fids = join(",", $fids);
        $product->save();

        $file = File::findOne($fid);
        if ($file) {
            $file->delete();
        }
        return $this->json_output();
    }

    /**
     * 删除开板图片
     * @param $id
     * @return array
     */
    public function actionRemoveTemplateFile()
    {
        $pid = $this->post('pid', '');
        $fid = $this->post('fid', '');
        $productTpl = ProductTemplate::findOne($pid);
        if (empty($productTpl)) {
            return $this->json_output(['status' => '0', 'msg' => '处理失败', 'command' => '']);
        }
        $fids = explode(",", $productTpl->fids);

        $key = array_search($fid, $fids);
        unset($fids[$key]);
        $productTpl->fids = join(",", $fids);
        $productTpl->save();
        return $this->json_output();
    }

    /**
     * 删除耗材图片
     * @param $id
     * @return array
     */
    public function actionRemoveAttachedFile()
    {
        $pid = $this->post('pid', '');
        $fid = $this->post('fid', '');
        $model = Material::findOne($pid);
        if (empty($model)) {
            return $this->json_output(['status' => '0', 'msg' => '处理失败', 'command' => '']);
        }
        $fids = explode(",", $model->fids);

        $key = array_search($fid, $fids);
        unset($fids[$key]);
        $model->fids = join(",", $fids);
        $model->save();

        $file = File::findOne($fid);
        if ($file) {
            $file->delete();
        }
        return $this->json_output();
    }

    /**
     * 获取模板
     * @return array
     */
    public function actionGetTemplate()
    {
        $sku = $this->post('based_sku', '');
        $basedTemplate = Product::findOne(['sku' => $sku]);
        if (empty($basedTemplate)) {
            return $this->json_output(['status' => 0, 'msg' => '母版SKU不存在！']);
        }

        $fids = $basedTemplate->fids;
        if (!empty($fids)) {
            $fids = explode(",", $fids);
            $files = File::find()->where(['in', 'id', $fids])->asArray()->all();

            if ($files) {
                foreach ($files as &$file) {
                    $file['file_path'] = str_replace("#", urlencode("#"), $file['file_path']);
                }
            }
            return $this->json_output(['data' => ['files' => $files, 'template_no' => $basedTemplate->template_no]]);
        } else {
            return $this->json_output();
        }
    }

    /**
     * 获取模板信息
     * @return array
     */
    public function actionGetTemplateInfo()
    {
        $templateNo = $this->post('templateNo', '');
        $templateNo = trim($templateNo);
        $template = ProductTemplate::findOne(['template_no' => $templateNo]);
        if (empty($template)) {
            return $this->json_output();
        }
        $templateAttributes = ProductTemplateAttributes::find()->where(['tpl_id' => $template->id])->asArray()->one();
        if (empty($templateAttributes)) {
            return $this->json_output();
        }

        return $this->json_output(['data' => $templateAttributes]);
    }

    /**
     * 获取色卡
     */
    public function actionGetColorCard()
    {
        return $this->renderAjax("//fragment/color-card.php");
    }
}
