<?php

namespace app\live\controller\video;

use app\live\model\live\AppList;
use app\live\model\live\VideoCategory;
use think\Exception;
use app\common\controller\Backend;
use app\live\model\live\UserVideo;

/**
 * 小视频管理
 *
 */
class App extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $sSort    = $this->request->param('sort');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserVideo::where("uv.watch_type='free'");
            $oTotalQuery  = UserVideo::where("uv.watch_type='free'");

            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('uv.user_id', $sKeyword);
                    $oTotalQuery->where('uv.user_id', $sKeyword);
                }
            }

            if ( $aFilter ) {
                foreach ( $aFilter as $key => $value ) {
                    if ( stripos($aOp[$key], 'LIKE') !== FALSE ) {
                        $value     = str_replace([
                            'LIKE ',
                            '...'
                        ], [
                            '',
                            $value
                        ], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ( $key ) {
                        case 'id':
                            $oSelectQuery->where('uv.id', $aOp[$key], $value);
                            $oTotalQuery->where('uv.id', $aOp[$key], $value);
                            break;
                        case 'user_id':
                            $oSelectQuery->where('uv.user_id', $aOp[$key], $value);
                            $oTotalQuery->where('uv.user_id', $aOp[$key], $value);
                            break;
                        case 'create_time':
                            $oSelectQuery->where('uv.create_time', $aOp[$key], $value);
                            $oTotalQuery->where('uv.create_time', $aOp[$key], $value);
                            break;
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }


            $total  = $oTotalQuery->alias('uv')->join("user u", "u.user_id = uv.user_id")->count();
            $list   = $oSelectQuery->alias('uv')
                ->join("user u", "u.user_id = uv.user_id")
                ->join('video_category vc', 'uv.type=vc.id', 'LEFT')
                ->order('uv. ' . $sSort . ' ' . $sOrder)
                ->field("uv.*,vc.name as category_name,u.user_nickname,u.user_avatar,u.user_level")
                ->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $oVideoCategory = VideoCategory::all();
        $this->view->assign("video_category", $oVideoCategory);
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
            $app_arr[$item->id] = $item->app_name;
        }
        return $app_arr;
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        UserVideo::where('id', 'in', $ids)->delete();
        $this->success();
    }

    public function hot($ids = '')
    {
        $row = UserVideo::where('id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 'N':
                    $row->hot_time = 0;
                    break;
                case 'Y':
                default:
                    $row->hot_time = time();
                    break;
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    public function play($ids = '')
    {
        $row = UserVideo::where('id', $ids)->find();
        $this->view->assign('url', $row->play_url);
        $this->view->assign('cover', $row->cover);
        return $this->view->fetch();
    }

    public function show($ids = '')
    {
        $row = UserVideo::where('id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 'N':
                    $row->is_show = 0;
                    break;
                case 'Y':
                default:
                    $row->is_show = 1;
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
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '')
    {
        $row = UserVideo::where('id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params       = $this->request->param('params');
            $params       = explode('=', $params);
            $allow_params = [
                'video_is_examine'
            ];
            if ( !in_array($params[0], $allow_params) ) {
                $this->error('无权修改');
            }
            $row[$params[0]] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
    }

    /**
     * 审核设置
     */
    public function examine($ids = 0)
    {
        $row = UserVideo::where('id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        $app_arr = $this->_getExamineArr();
        if ( $this->request->isAjax() ) {
            $params = $this->request->param('row/a');
            if ( isset($params['video_is_examine']) && array_key_exists($params['video_is_examine'], $app_arr) ) {
                $row->video_is_examine = $params['video_is_examine'];
                $row->save();
            }
            $this->success();
        }
        $this->view->assign("row_app_list", $app_arr);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    public function add()
    {
        if ( $this->request->isAjax() ) {
            $params            = $this->request->param('row/a');
            $row               = new UserVideo();
            $row->user_id      = $params['user_id'];
            $row->type         = $params['type'];
            $row->title        = $params['title'];
            $row->cover        = $params['cover'];
            $row->play_url     = $params['play_url'];
            $row->duration     = $params['duration'];
            $row->check_status = 'Y';
            $row->city         = '深圳市';
            $row->validate(
                [
                    'user_id'  => 'require',
                    'type'     => 'require',
                    'title'    => 'require',
                    'play_url' => 'require',
                    'duration' => 'require',
                ],
                [
                    'app_name.require'                    => __('Parameter %s can not be empty', [ '用户' ]),
                    'app_flg.require'                     => __('Parameter %s can not be empty', [ '类型' ]),
                    'company_name.require'                => __('Parameter %s can not be empty', [ '标题' ]),
                    'recharge_apple_goods_prefix.require' => __('Parameter %s can not be empty', [ '视频' ]),
                    'anchor_prefix.require'               => __('Parameter %s can not be empty', [ '时长' ]),
                ]
            );
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
        $upload_url = config('upload.uploadurl_video');
        $this->view->assign("upload_url", $upload_url);
        $oVideoCategory = VideoCategory::all();
        $this->view->assign("video_category", $oVideoCategory);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $row = UserVideo::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isAjax() ) {
            $params            = $this->request->param('row/a');
            $row->user_id      = $params['user_id'];
            $row->type         = $params['type'];
            $row->title        = $params['title'];
            $row->cover        = $params['cover'];
//            $row->play_url     = $params['play_url'];
            $row->duration     = $params['duration'];
            $row->check_status = 'Y';
            $row->city         = '深圳市';
            $row->validate(
                [
                    'user_id'  => 'require',
                    'type'     => 'require',
                    'title'    => 'require',
//                    'play_url' => 'require',
                    'duration' => 'require',
                ],
                [
                    'app_name.require'                    => __('Parameter %s can not be empty', [ '用户' ]),
                    'app_flg.require'                     => __('Parameter %s can not be empty', [ '类型' ]),
                    'company_name.require'                => __('Parameter %s can not be empty', [ '标题' ]),
                    'recharge_apple_goods_prefix.require' => __('Parameter %s can not be empty', [ '视频' ]),
                    'anchor_prefix.require'               => __('Parameter %s can not be empty', [ '时长' ]),
                ]
            );
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
        $upload_url = config('upload.uploadurl_video');
        $this->view->assign("upload_url", $upload_url);
        $oVideoCategory = VideoCategory::all();
        $this->view->assign("video_category", $oVideoCategory);
        $this->view->assign('row',$row);
        return $this->view->fetch();
    }
}