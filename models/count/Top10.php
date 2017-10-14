<?php
/**
 * Top10.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/8/31
 */

namespace app\models\count;

use app\models\BaseModel;
use app\models\order\Item;
use app\models\Category;
use renk\yiipal\helpers\FileHelper;
use app\models\product\Product;
use app\helpers\CommonHelper;

class Top10 extends BaseModel{

    //要统计的magento分类
    public $select_magento_cid = [
            7 => 'All Website',//美国站
            27 => 'Wedding Set',//bridal ring sets
            26 => 'Engagement',//engagement rings
            51 => "Women's Wedding Band",//bands/for her
            52 => "Men's Wedding Band",//bands/for him
            42 => 'Changeable',//bridal ring sets/interchangeable rings
            29 => 'Jeulia-design',//jeulia design
        ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'order_item';
    }
    
    public function getTop10($params){
        $query = Item::find()
        ->select('order_item.*,order.source,product.magento_cid')
        ->leftJoin('order','order_item.order_id = order.id')
        ->leftJoin('product','order_item.product_id = product.id')
        ->orderBy("created_at ASC");
        
        if(isset($params['Order']['created_at']) && !empty($params['Order']['created_at'])){
            $dateRange = explode("/", $params['Order']['created_at']);
            $start = strtotime($dateRange[0] ." 00:00:00");
            $end = strtotime($dateRange[1] ." 23:59:59");
        }else{
            $start  = strtotime(date('Y-m-d',time()) ." 00:00:00");
            $end  = strtotime(date('Y-m-d',time())  ." 23:59:59");
        }
        
        $query->andWhere(['>=','order.created_at',$start])
        ->andWhere(['<=','order.created_at',$end]);
        
        if(isset($params['source']) && !empty($params['source'])){
            $query->andWhere(['order.source'=>$params['source']]);
        }
        
        $results = $query->asArray()->all();
        return $this->formateData($results);
    }
    
    private function formateData($items){
        //要统计的magento分类
        $select_magento_cid = $this->select_magento_cid;
        
        //获取当前分类下的所有子ID
        $select_magento_cid_children = [];
        $category = new Category;
        foreach($select_magento_cid as $k_magento_cid => $magento_cid_name){
            //magento_cid应该包含自己
            $select_magento_cid_children[$k_magento_cid][] = $k_magento_cid;
            $childrens = $category->get_children($k_magento_cid,true);
            foreach($childrens as $children){
                $select_magento_cid_children[$k_magento_cid][] = $children['id'];
            }
        }
        
        //统计数据
        $data = [];
        foreach($items as $item){
            foreach($select_magento_cid_children as $k_magento_cid => $magento_cids){
                if(in_array($item['magento_cid'], $magento_cids)){
                    if(!isset($data[$k_magento_cid][$item['product_id']]['id'])){
                        $data[$k_magento_cid][$item['product_id']]['id'] = $item['product_id'];
                        $data[$k_magento_cid][$item['product_id']]['sku'] = $item['sku'];
                        $data[$k_magento_cid][$item['product_id']]['count'] = 1;
                    } else {
                        $data[$k_magento_cid][$item['product_id']]['count']++;
                    }
                }
            }
        }
        
        //排序，取出top10
        $top10 = [];
        
        //总销量
        $data_total = [];
        
        //top10合计
        $data_total_top10 = [];
        
        foreach($data as $k_magento_cid => $product_counts){
            //排序
            $count = [];
            foreach($product_counts as $product_id => $product_count){
                $count[$product_id] = $product_count['count'];
                if(isset($data_total[$k_magento_cid])){
                    $data_total[$k_magento_cid] += $product_count['count'];
                } else {
                    $data_total[$k_magento_cid] = $product_count['count'];
                }
            }
            array_multisort($count, SORT_DESC, $product_counts);
            
            //top10
            $i = 0;
            foreach($product_counts as $product_count){
                $i++;
                if($i<=10){
                    $top10[$k_magento_cid][] = $product_count;
                    
                    if(isset($data_total_top10[$k_magento_cid])){
                        $data_total_top10[$k_magento_cid] += $product_count['count'];
                    } else {
                        $data_total_top10[$k_magento_cid] = $product_count['count'];
                    }
                } else {
                    break;
                }
            }
        }
        
        //取得产品图片
        foreach($top10 as $k_magento_cid => $products){
            foreach($products as $k=>$product_counts){
                $product = Product::findOne($product_counts['id']);
                $files = $product->getFiles();
                //$filePath = FileHelper::getThumbnailPath($files[0]->file_path, '300x300');
                $filePath = $files[0]->file_path;
                $filePath = str_replace(urlencode("#"),"#",$filePath);
                $top10[$k_magento_cid][$k]['image'] = $filePath;
            }
        }
        
        //格式化输出
        $output = [];
        for($i=0;$i<10;$i++){
            $output[$i]['top'] = 'top'.($i+1);
            
            $j = 0;
            foreach($select_magento_cid as $magento_cid =>$magento_cid_name ){
                if(isset($top10[$magento_cid][$i])){
                    $output[$i]['sku'.$j] = $top10[$magento_cid][$i]['sku'];
                    $output[$i]['image'.$j] = $top10[$magento_cid][$i]['image'];
                    $output[$i]['count'.$j] = $top10[$magento_cid][$i]['count'];
                } else {
                    $output[$i]['sku'.$j] = '';
                    $output[$i]['image'.$j] = '';
                    $output[$i]['count'.$j] = 0;
                }
                $j++;
            }
            
            if($i == 9){
                $i++;
                //top10合计
                $output[$i]['top'] = '合计';
                $j = 0;
                foreach($select_magento_cid as $magento_cid =>$magento_cid_name ){
                    $output[$i]['sku'.$j] = '';
                    $output[$i]['image'.$j] = '';
                    $output[$i]['count'.$j] = isset($data_total_top10[$magento_cid]) ? $data_total_top10[$magento_cid] : 0;
                    $j++;
                }
                
                $i++;
                //网站总销量
                $output[$i]['top'] = '网站总销量';
                $j = 0;
                foreach($select_magento_cid as $magento_cid =>$magento_cid_name ){
                    $output[$i]['sku'.$j] = '';
                    $output[$i]['image'.$j] = '';
                    $output[$i]['count'.$j] = isset($data_total[$magento_cid]) ? $data_total[$magento_cid] : 0;
                    $j++;
                }
                
                $i++;
                //计算类目占比
                $output[$i]['top'] = '各类目销量占比';
                $j = 0;
                foreach($select_magento_cid as $magento_cid =>$magento_cid_name ){
                    $output[$i]['sku'.$j] = '';
                    $output[$i]['image'.$j] = '';
                    if($j == 0){
                        $output[$i]['count'.$j] = ($output[$i-1]['count'.$j] == 0 ) ?  0 : CommonHelper::number2Percent($output[$i-2]['count'.$j] / $output[$i-1]['count'.$j]);
                    } else {
                        $output[$i]['count'.$j] = (!isset($data_total[7]) || $data_total[7]== 0) ? 0 : CommonHelper::number2Percent($data_total[$magento_cid]/$data_total[7]);
                    }
                    $j++;
                }
            }
        }
        return $output;
    }
}