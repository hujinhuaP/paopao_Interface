<?php

namespace app\live\controller\posts;

use app\live\model\live\AppList;
use app\live\model\live\ShortPosts;
use app\live\model\live\ShortPostsDelete;
use app\live\model\live\ShortPostsMessage;
use app\live\model\live\User;
use app\live\model\live\Kv;
use think\Config;
use think\Exception;
use app\common\controller\Backend;

/**
 * 动态管理
 *
 */
class Posts extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.short_posts');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            foreach ( $list as $v ) {
                $v['short_posts_word'] = mb_strlen($v['short_posts_word']) >= 20 ? (mb_substr($v['short_posts_word'], 0, 20) . ' ... ') : $v['short_posts_word'];
            }
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        $app_arr = $this->_getExamineArr();
        $this->view->assign("row_app_list", $app_arr);
        return $this->view->fetch();
    }


    private function _getExamineArr()
    {
        $oAppList = AppList::all();
        $app_arr  = [
            0 => '无设置'
        ];
        foreach ( $oAppList as $item ) {
            $app_arr[ $item->id ] = $item->app_name;
        }
        return $app_arr;
    }

    /**
     * @param string $ids
     * 动态详情
     * 获取动态的用户信息 和动态内容
     */
    public function detail( $ids = '' )
    {
        $row = ShortPosts::where('short_posts_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        $oUser = User::get($row->short_posts_user_id);

        $this->view->assign([
            'row'         => $row,
            'videoSource' => $row->short_posts_video,
            'imageSource' => explode(',', $row->short_posts_images),
            'oUser'       => $oUser
        ]);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     */
    public function edit( $ids = '' )
    {
        $row = ShortPosts::where('short_posts_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        // 获取收费动态价格列表
        $kvData    = Kv::many([
            Kv::POSTS_MAX_PRICE,
            Kv::POSTS_MIN_PRICE,
            Kv::POSTS_INTERVAL_PRICE
        ]);
        $priceList = [];
        for ( $i = $kvData[ Kv::POSTS_MIN_PRICE ]; $i <= $kvData[ Kv::POSTS_MAX_PRICE ]; $i += $kvData[ Kv::POSTS_INTERVAL_PRICE ] ) {
            $priceList[]
                = (string)$i;
        }
        if ( $this->request->isPost() ) {

            $params                        = $this->request->param('row/a');

            if ( $row->short_posts_type == 'exhibition' ) {
                $params['short_posts_pay_type'] = ShortPosts::PAY_TYPE_PAY;
                if ( $params['short_posts_price'] <= 0 ) {
                    $this->error(__('作品只能付费，请设置价格'));
                }
            }

            $row->short_posts_get_user_id  = $params['short_posts_get_user_id'];
            $row->short_posts_word         = $params['short_posts_word'];
            $row->short_posts_status       = $params['short_posts_status'];
            $row->short_posts_check_remark = $params['short_posts_check_remark'];
            $row->short_posts_check_time   = time();
            $row->short_posts_update_time  = time();
            $row->short_posts_price        = $params['short_posts_price'];
            $row->short_posts_pay_type     = $row->short_posts_price == 0 ? ShortPosts::PAY_TYPE_FREE : $params['short_posts_pay_type'];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }

        $this->view->assign([
            'row' => $row,
        ]);
        $this->view->assign("priceList", $priceList);
        return $this->view->fetch();
    }


    /**
     * @return string
     * @throws Exception
     * 添加动态
     */
    public function add()
    {
        // 获取收费动态价格列表
        $kvData    = Kv::many([
            Kv::POSTS_MAX_PRICE,
            Kv::POSTS_MIN_PRICE,
            Kv::POSTS_INTERVAL_PRICE
        ]);
        $priceList = [];
        for ( $i = $kvData[ Kv::POSTS_MIN_PRICE ]; $i <= $kvData[ Kv::POSTS_MAX_PRICE ]; $i += $kvData[ Kv::POSTS_INTERVAL_PRICE ] ) {
            $priceList[]
                = (string)$i;
        }
        if ( $this->request->isPost() ) {

            $params = $this->request->param('row/a');
            if ( !in_array($params['short_posts_pay_type'], [
                ShortPosts::PAY_TYPE_FREE,
                ShortPosts::PAY_TYPE_PART_FREE,
                ShortPosts::PAY_TYPE_PAY
            ]) ) {
                $this->error('类型参数错误');
            }
            $sPostsPayPrice = $params['short_posts_price'] ?? 0;
            if ( $params['short_posts_pay_type'] != ShortPosts::PAY_TYPE_FREE ) {
                if ( empty($sPostsPayPrice) ) {
                    $this->error('付费类型，价格不能为空');
                }
                if ( !in_array($params['short_posts_price'], $priceList) ) {
                    $this->error('付费价格设置错误');
                }
            }
            $row = new ShortPosts();;
            $row->short_posts_get_user_id = $params['short_posts_get_user_id'];
            $row->short_posts_word        = $params['short_posts_word'];
            $row->short_posts_user_id     = $params['short_posts_user_id'];
            $row->short_posts_price       = $params['short_posts_price'];
            $row->short_posts_update_time = time();
            $fieldRule                    = [
                'short_posts_word'    => 'require',
                'short_posts_user_id' => 'require',
            ];
            $ruleDetail                   = [
                'short_posts_word.require'    => __('Parameter %s can not be empty', [ __('文字内容') ]),
                'short_posts_user_id.require' => __('Parameter %s can not be empty', [ __('Username') ]),
            ];
            switch ( $params['short_posts_type'] ) {
                case 'image':
                case 'exhibition':
//                    $row->short_posts_images                  = isset($params['img_url']) && is_array($params['img_url']) ? implode(',', $params['img_url']) : '';
                    $row->short_posts_images                  = $params['short_posts_images'];
                    $fieldRule['short_posts_images']          = 'require';
                    $ruleDetail['short_posts_images.require'] = __('Parameter %s can not be empty', [ __('图片') ]);
                    break;
                case 'video':
                    $row->short_posts_images                  = $params['cover'];
                    $row->short_posts_video                   = $params['short_posts_video'];
                    $fieldRule['short_posts_images']          = 'require';
                    $fieldRule['short_posts_video']           = 'require';
                    $ruleDetail['short_posts_images.require'] = __('Parameter %s can not be empty', [ __('封面') ]);
                    $ruleDetail['short_posts_video.require']  = __('Parameter %s can not be empty', [ __('视频') ]);
                    break;
                case 'word':
                    break;
                default:
                    $this->error('类型错误');
            }
            if ( $params['short_posts_type'] == 'exhibition' ) {
                // 作品只能是付费
                $params['short_posts_pay_type'] = ShortPosts::PAY_TYPE_PAY;
                if ( $params['short_posts_price'] <= 0 ) {
                    $this->error(__('作品只能付费，请设置价格'));
                }
            }
            $row->short_posts_pay_type     = $row->short_posts_price == 0 ? ShortPosts::PAY_TYPE_FREE : $params['short_posts_pay_type'];
            $row->short_posts_type         = $params['short_posts_type'];
            $row->short_posts_status       = 'Y';
            $row->short_posts_check_remark = '后台发布';
            $row->short_posts_check_time   = time();
            $row->short_posts_update_time  = time();
            $row->short_posts_show_width  = $params['short_posts_show_width'] ?? 0;
            $row->short_posts_show_height  = $params['short_posts_show_height'] ?? 0;
            $row->validate($fieldRule, $ruleDetail);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }

        $this->view->assign("priceList", $priceList);
        $upload_url = config('upload.uploadurl_video');
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();
    }


    /**
     * delete 删除
     * 将数据删除 放入删除表
     *
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
        $row          = ShortPosts::where('short_posts_id', $ids)->find();
        $deleteRemark = $this->request->post('remark');
        if ( !$row )
            $this->error(__('No Results were found'));
        $deleteRow = new ShortPostsDelete();
        $deleteRow->startTrans();
        $saveData                             = $row->toArray();
        $saveData['short_posts_check_time']   = time();
        $saveData['short_posts_check_remark'] = $deleteRemark;
        if ( !$deleteRow->save($saveData) ) {
            $deleteRow->rollback();
            $this->error($deleteRow->getError());
        }
        if ( !$row->delete() ) {
            $deleteRow->rollback();
            $this->error($row->getError());
        }
        // 发送动态消息
        $oShortPostsMessage                       = new ShortPostsMessage();
        $oShortPostsMessage->short_posts_id       = $row->short_posts_id;
        $oShortPostsMessage->message_type         = ShortPostsMessage::MESSAGE_TYPE_POSTS_DELETE;
        $oShortPostsMessage->user_id              = $row->short_posts_user_id;
        $oShortPostsMessage->message_content      = sprintf('你在社区的动态帖子中违反规则 【%s】,相关信息已被清除，请遵守规则，屡次违反规则系统将会作出相应惩罚、封号等措施', $deleteRemark);
        $oShortPostsMessage->message_target_extra = serialize([
            'extra_content' => $row->short_posts_word,
            'extra_time'    => $row->short_posts_check_time,
        ]);
        $oShortPostsMessage->save();
        file_get_contents(sprintf('%s/im/notify?%s', Config::get('api_url'), http_build_query([
            'uid'  => $oShortPostsMessage->user_id,
            'rid'  => 0,
            'type' => 'posts_message',
            'msg'  => json_encode([
                'type' => 'posts_message',
                'data' => (object)[],
            ], JSON_UNESCAPED_UNICODE)
        ])));

        $deleteRow->commit();
        $this->success();
    }

    public function hot( $ids = '' )
    {
        $row = ShortPosts::where('short_posts_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 'N':
                    $row->short_posts_selection_time = 0;
                    $row->short_posts_is_selection   = 'N';
                    $row->short_posts_is_top         = 'N';
                    $row->short_posts_top_time       = 0;
                    break;
                case 'Y':
                default:
                    $row->short_posts_selection_time = time();
                    $row->short_posts_is_selection   = 'Y';
                    break;
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    /**
     * @param string $ids
     * 置顶
     */
    public function istop( $ids = '' )
    {
        $row = ShortPosts::where('short_posts_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 'N':
                    $row->short_posts_is_top   = 'N';
                    $row->short_posts_top_time = 0;
                    break;
                case 'Y':
                default:
                    $row->short_posts_is_top         = 'Y';
                    $row->short_posts_top_time       = time();
                    $row->short_posts_selection_time = time();
                    $row->short_posts_is_selection   = 'Y';
                    break;
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }


    /**
     * 审核设置
     */
    public function examine( $ids = 0 )
    {
        $row = ShortPosts::where('short_posts_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        $app_arr = $this->_getExamineArr();
        if ( $this->request->isAjax() ) {
            $params = $this->request->param('row/a');
            if ( isset($params['short_posts_examine']) && array_key_exists($params['short_posts_examine'], $app_arr) ) {
                $row->short_posts_examine = $params['short_posts_examine'];
                $row->save();
            }
            $this->success();
        }
        $this->view->assign("row_app_list", $app_arr);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}