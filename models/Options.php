<?php
/**
 * Options.php 
 *
 * @Author: renk
 * @mail:359876077@qq.com
 * @Wechat:renk03
 * @Date: 2016/11/4
 */

namespace app\models;


class Options extends BaseModel{

    protected $default = [
        'structure_table'	=> 'options_struct',
        'data_table'		=> 'options',
        'data2structure'	=> 'id',
        'structure'			=> [
            'id'			=> 'id',
            'left'			=> 'lft',
            'right'			=> 'rgt',
            'level'			=> 'lvl',
            'parent_id'		=> 'pid',
            'position'		=> 'pos'
        ],
        'data'				=> ['name']
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'options';
    }

    public function get_node($id, $options = array()) {
        $node = $this->db->one("
			SELECT
				s.".implode(", s.", $this->options['structure']).",
				d.".implode(", d.", $this->options['data'])."
			FROM
				".$this->options['structure_table']." s,
				".$this->options['data_table']." d
			WHERE
				s.".$this->options['structure']['id']." = d.".$this->options['data2structure']." AND
				s.".$this->options['structure']['id']." = ".(int)$id
        );
        if(!$node) {
            throw new Exception('Node does not exist');
        }
        if(isset($options['with_children'])) {
            $node['children'] = $this->get_children($id, isset($options['deep_children']));
        }
        if(isset($options['with_path'])) {
            $node['path'] = $this->get_path($id);
        }
        return $node;
    }
}