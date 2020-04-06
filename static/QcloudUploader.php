<?php 

require('cos-php-sdk-v4/include.php'); 

use Qcloud\Cos\Api;
use Qcloud\Cos\Auth;

/**
* 腾讯云OSS
*
* @anchors yeah_lsj@yeah.net
*/
class QcloudUploader 
{

	/** @var string APP ID */
	protected $appId;
	/** @var string SECRET ID*/
	protected $secretId;
	/** @var string SECRET KEY*/
	protected $secretKey;
	/** @var string REGION*/
	protected $region;
	/** @var int    TIMEOUT*/
	protected $timeout = 60;
	/** @var string Bucket 名称 */
	protected $bucketName;
    /** @var array 上传成功返回的结果 */
    protected $uploadResult = [];

	private $fileField; //文件域名
    private $file; //文件上传对象
    private $base64; //文件上传对象
    private $config; //配置信息
    private $oriName; //原始文件名
    private $fileName; //新文件名
    private $fullName; //完整文件名,即从当前配置目录开始的URL
    private $filePath; //完整文件名,即从当前配置目录开始的URL
    private $fileSize; //文件大小
    private $fileType; //文件类型
    private $stateInfo; //上传状态信息,
    private $stateMap = array( //上传状态映射表，国际化用户需考虑此处数据的国际化
        "SUCCESS", //上传成功标记，在UEditor中内不可改变，否则flash判断会出错
        "文件大小超出 upload_max_filesize 限制",
        "文件大小超出 MAX_FILE_SIZE 限制",
        "文件未被完整上传",
        "没有文件被上传",
        "上传文件为空",
        "ERROR_TMP_FILE" => "临时文件错误",
        "ERROR_TMP_FILE_NOT_FOUND" => "找不到临时文件",
        "ERROR_SIZE_EXCEED" => "文件大小超出网站限制",
        "ERROR_TYPE_NOT_ALLOWED" => "文件类型不允许",
        "ERROR_CREATE_DIR" => "目录创建失败",
        "ERROR_DIR_NOT_WRITEABLE" => "目录没有写权限",
        "ERROR_FILE_MOVE" => "文件保存时出错",
        "ERROR_FILE_NOT_FOUND" => "找不到上传文件",
        "ERROR_WRITE_CONTENT" => "写入文件内容错误",
        "ERROR_UNKNOWN" => "未知错误",
        "ERROR_DEAD_LINK" => "链接不可用",
        "ERROR_HTTP_LINK" => "链接不是http链接",
        "ERROR_HTTP_CONTENTTYPE" => "链接contentType不正确",
        "INVALID_URL" => "非法 URL",
        "INVALID_IP" => "非法 IP"
    );

    /**
     * 构造函数
     * @param string $fileField 表单名称
     * @param array $config 配置项
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

	private function upload($srcPath, $dstPath)
	{
		// Bucket 名称
		$bucketName = $this->config['qcloud']['bucketName'];
		// 文件属性，业务端维护
		$bizAttr = '';
		// 文件分片大小，当文件大于 20M 时，SDK 内部会通过多次分片的方式进行上传；默认分片大小为 1M，支持的最大分片大小为 3M
		$slicesize	 = 3 * 1024 * 1024;
		// 同名文件是否进行覆盖。0：覆盖；1：不覆盖
		$insertOnly	 = 0;
		$config = array(
			'app_id'     => $this->config['qcloud']['appId'],
			'secret_id'  => $this->config['qcloud']['secretId'],
			'secret_key' => $this->config['qcloud']['secretKey'],
			'region'     => $this->config['qcloud']['region'],
			'timeout'    => $this->config['qcloud']['timeout'],
		);
		$api = new Api($config);
		$this->uploadResult = $api->upload($bucketName, $srcPath, $dstPath, 
			$bizAttr, $slicesize, $insertOnly);
        return $this->uploadResult;
	}

	private function uploadBuffer($content, $dstPath)
	{
		// Bucket 名称
		$bucketName = $this->config['qcloud']['bucketName'];
		// 文件属性，业务端维护
		$bizAttr = '';
		// 文件分片大小，当文件大于 20M 时，SDK 内部会通过多次分片的方式进行上传；默认分片大小为 1M，支持的最大分片大小为 3M
		$slicesize	 = 3 * 1024 * 1024;
		// 同名文件是否进行覆盖。0：覆盖；1：不覆盖
		$insertOnly	 = 0;
		$config = array(
			'app_id'     => $this->config['qcloud']['appId'],
			'secret_id'  => $this->config['qcloud']['secretId'],
			'secret_key' => $this->config['qcloud']['secretKey'],
			'region'     => $this->config['qcloud']['region'],
			'timeout'    => $this->config['qcloud']['timeout'],
		);

		$api = new Api($config);
		$this->uploadResult = $api->uploadBuffer($bucketName, $content, $dstPath,
        $bizAttr, $insertOnly);
        return $this->uploadResult;
	}

    /**
     * 上传文件的主处理方法
     * @return mixed
     */
    private function upFile()
    {
        $file = $this->file = $_FILES[$this->fileField];
        if (!$file) {
            $this->stateInfo = $this->getStateInfo("ERROR_FILE_NOT_FOUND");
            return;
        }
        if ($this->file['error']) {
            $this->stateInfo = $this->getStateInfo($file['error']);
            return;
        } else if (!file_exists($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMP_FILE_NOT_FOUND");
            return;
        } else if (!is_uploaded_file($file['tmp_name'])) {
            $this->stateInfo = $this->getStateInfo("ERROR_TMPFILE");
            return;
        }

        $this->oriName = $file['name'];
        $this->fileSize = $file['size'];
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        //检查是否不允许的文件格式
        if (!$this->checkType()) {
            $this->stateInfo = $this->getStateInfo("ERROR_TYPE_NOT_ALLOWED");
            return;
        }

        $result = $this->upload($file["tmp_name"], $this->filePath);

        if ($result['code'] != 0) {
        	$this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
        } else {
        	$this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64()
    {
        $base64Data = $_POST[$this->fileField];
        $img = base64_decode($base64Data);

        $this->oriName = $this->config['oriName'];
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        $result = $this->uploadBuffer($img, $this->filePath);

        if ($result['code'] != 0) {
        	$this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
        } else {
        	$this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 拉取远程图片
     * @return mixed
     */
    private function saveRemote()
    {
        $imgUrl = htmlspecialchars($this->fileField);
        $imgUrl = str_replace("&amp;", "&", $imgUrl);

        //http开头验证
        if (strpos($imgUrl, "http") !== 0) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_LINK");
            return;
        }

        preg_match('/(^https*:\/\/[^:\/]+)/', $imgUrl, $matches);
        $host_with_protocol = count($matches) > 1 ? $matches[1] : '';

        // 判断是否是合法 url
        if (!filter_var($host_with_protocol, FILTER_VALIDATE_URL)) {
            $this->stateInfo = $this->getStateInfo("INVALID_URL");
            return;
        }

        preg_match('/^https*:\/\/(.+)/', $host_with_protocol, $matches);
        $host_without_protocol = count($matches) > 1 ? $matches[1] : '';

        // 此时提取出来的可能是 ip 也有可能是域名，先获取 ip
        $ip = gethostbyname($host_without_protocol);
        // 判断是否是私有 ip
        if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
            $this->stateInfo = $this->getStateInfo("INVALID_IP");
            return;
        }

        //获取请求头并检测死链
        $heads = get_headers($imgUrl, 1);
        if (!(stristr($heads[0], "200") && stristr($heads[0], "OK"))) {
            $this->stateInfo = $this->getStateInfo("ERROR_DEAD_LINK");
            return;
        }
        //格式验证(扩展名验证和Content-Type验证)
        $fileType = strtolower(strrchr($imgUrl, '.'));
        if (!in_array($fileType, $this->config['allowFiles']) || !isset($heads['Content-Type']) || !stristr($heads['Content-Type'], "image")) {
            $this->stateInfo = $this->getStateInfo("ERROR_HTTP_CONTENTTYPE");
            return;
        }

        //打开输出缓冲区并获取远程图片
        ob_start();
        $context = stream_context_create(
            array('http' => array(
                'follow_location' => false // don't follow redirects
            ))
        );
        readfile($imgUrl, false, $context);
        $img = ob_get_contents();
        ob_end_clean();
        preg_match("/[\/]([^\/]*)[\.]?[^\.\/]*$/", $imgUrl, $m);

        $this->oriName = $m ? $m[1]:"";
        $this->fileSize = strlen($img);
        $this->fileType = $this->getFileExt();
        $this->fullName = $this->getFullName();
        $this->filePath = $this->getFilePath();
        $this->fileName = $this->getFileName();
        $dirname = dirname($this->filePath);

        //检查文件大小是否超出限制
        if (!$this->checkSize()) {
            $this->stateInfo = $this->getStateInfo("ERROR_SIZE_EXCEED");
            return;
        }

        $result = $this->uploadBuffer($img, $this->filePath);

        if ($result['code'] != 0) {
        	$this->stateInfo = $this->getStateInfo("ERROR_FILE_MOVE");
        } else {
        	$this->stateInfo = $this->stateMap[0];
        }

    }

    /**
     * 上传错误检查
     * @param $errCode
     * @return string
     */
    private function getStateInfo($errCode)
    {
        return !$this->stateMap[$errCode] ? $this->stateMap["ERROR_UNKNOWN"] : $this->stateMap[$errCode];
    }

    /**
     * 获取文件扩展名
     * @return string
     */
    private function getFileExt()
    {
        return strtolower(strrchr($this->oriName, '.'));
    }

    /**
     * 重命名文件
     * @return string
     */
    private function getFullName()
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $this->config["pathFormat"];
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        $format = str_replace("{time}", $t, $format);

        //过滤文件名的非法自负,并替换文件名
        $oriName = substr($this->oriName, 0, strrpos($this->oriName, '.'));
        $oriName = preg_replace("/[\|\?\"\<\>\/\*\\\\]+/", '', $oriName);
        $format = str_replace("{filename}", $oriName, $format);

        //替换随机字符串
        $randNum = rand(1, 10000000000) . rand(1, 10000000000);
        if (preg_match("/\{rand\:([\d]*)\}/i", $format, $matches)) {
            $format = preg_replace("/\{rand\:[\d]*\}/i", substr($randNum, 0, $matches[1]), $format);
        }

        $ext = $this->getFileExt();
        return $format . $ext;
    }

    /**
     * 获取文件名
     * @return string
     */
    private function getFileName () {
        return substr($this->filePath, strrpos($this->filePath, '/') + 1);
    }

    /**
     * 获取文件完整路径
     * @return string
     */
    private function getFilePath()
    {
        $fullname = $this->fullName;

        if (substr($fullname, 0, 1) != '/') {
            $fullname = '/' . $fullname;
        }

        return $fullname;
    }

    /**
     * 文件类型检测
     * @return bool
     */
    private function checkType()
    {
        return in_array($this->getFileExt(), $this->config["allowFiles"]);
    }

    /**
     * 文件大小检测
     * @return bool
     */
    private function  checkSize()
    {
        return $this->fileSize <= ($this->config["maxSize"]);
    }

    /**
     * 获取当前上传成功文件的各项信息
     * @return array
     */
    public function getFileInfo()
    {
        return array(
            "state" => $this->stateInfo,
            // "url" => $this->fullName,
            "url" => $this->uploadResult['access_url'],
            "dstPath" => $this->fullName,
            "title" => $this->fileName,
            "original" => $this->oriName,
            "type" => $this->fileType,
            "size" => $this->fileSize
        );
    }

    public function json($data)
    {
        $result = json_encode($data, JSON_UNESCAPED_UNICODE);

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ), JSON_UNESCAPED_UNICODE);
            }
        } else {
            if ((!isset($_SERVER['HTTP_ORIGIN']) || $_SERVER['HTTP_ORIGIN'] == 'null')) {
                if (isset($_GET["__url"])) {
                    header(sprintf('Location:%s%s', $_GET["__url"], $result));
                } elseif (isset($this->config['adminurl'])) {
                    header(sprintf('Location:%s/live/ajax/json?json=%s', $this->config['adminurl'] , $result));
                }
            }
            echo $result;
        }

        exit;
    }

    /**
	 * createSignature 生成签名
	 * 
	 * @return string
	 */
	public function createSignatureAction()
	{
		$auth = new Auth($this->config['qcloud']['appId'], $this->config['qcloud']['secretId'], $this->config['qcloud']['secretKey']);
		$sign = $auth->createReusableSignature(strtotime('+7 day'), $this->config['qcloud']['bucketName']);
		$tmpKey = $this->getTempKeys();
		$this->json([
			'c' => 0,
			'm' => 'success',
			'd' => [
				'config' => [
					'sign'        => $sign,
					'appId'       => $this->config['qcloud']['appId'],
					'bucket'      => $this->config['qcloud']['bucketName'],
					'region'      => $this->config['qcloud']['region'],
					'image_cdn'   => $this->config['qcloud']['image_cdn'],
				],
                'credentials' => $tmpKey['credentials'] ?? (object)[],
                'expiredTime' => $tmpKey['expiredTime'] ?? 0
            ]
		]);
	}

    /**
     * uploadImgction 文件上传
     * 
     * @return string
     */
    public function uploadImgAction()
    {
        $this->fileField = 'file';
        $this->config["pathFormat"] = $this->config['imagePathFormat'];
        $this->config["maxSize"]    = $this->config['imageMaxSize'];
        $this->config["allowFiles"] = $this->config['imageAllowFiles'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();

        if ($ret['state'] == 'SUCCESS') {
            $this->json([
                'c' => 0,
                'm' => 'success',
                'd' => [
                    'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                    'title'    => (string)$ret['title'],
                    'original' => (string)$ret['original'],
                    'type'     => (string)$ret['type'],
                    'size'     => (string)$ret['size'],
                ]
            ]);
        } else {
            $this->json([
                'c' => 10000,
                'm' => $ret['state'],
                'd' => '',
            ]);
        }
    }

	/**
	 * uploadAdminImgAction 后台图片文件上传
	 * 
	 * @return string
	 */
	public function uploadAdminImgAction()
	{
        $this->fileField = 'file';
		$this->config["pathFormat"] = $this->config['imagePathFormat'];
		$this->config["maxSize"]    = $this->config['imageMaxSize'];
		$this->config["allowFiles"] = $this->config['imageAllowFiles'];
		$this->type = 'upload';
		$this->upFile();
		$ret = $this->getFileInfo();

		if ($ret['state'] == 'SUCCESS') {
		    $this->json([
	        'code' => 1,
	        'msg' => 'success',
	        'data' => [
	            'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
	            'title'    => (string)$ret['title'],
	            'original' => (string)$ret['original'],
	            'type'     => (string)$ret['type'],
	            'size'     => (string)$ret['size'],
	        ],
	        'url' => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
	        'wait' => 3,
	    ]);
		} else {
		    $this->json([
		        'code' => 10000,
		        'msg' => $ret['state'],
		        'data' => [],
		    ]);
		}
	}

	/**
	 * uploadAdminGifAction 后台上传GIF图片
	 */
	public function uploadAdminGifAction()
	{
        $this->fileField = 'file';
		$this->config["pathFormat"] = $this->config['imagePathFormat'];
		$this->config["maxSize"]    = $this->config['imageMaxSize'];
		$this->config["allowFiles"] = ['.gif'];
		$this->type = 'upload';
		$this->upFile();
		$ret = $this->getFileInfo();

		if ($ret['state'] == 'SUCCESS') {
		    $this->json([
	        'code' => 1,
	        'msg' => 'success',
	        'data' => [
	            'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
	            'title'    => (string)$ret['title'],
	            'original' => (string)$ret['original'],
	            'type'     => (string)$ret['type'],
	            'size'     => (string)$ret['size'],
	        ],
	        'url' => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
	        'wait' => 3,
	    ]);
		} else {
		    $this->json([
		        'code' => 10000,
		        'msg' => $ret['state'],
		        'data' => [],
		    ]);
		}
	}

	/**
	 * uploadAdminZipAction 后台上传ZIP文件
	 */
	public function uploadAdminZipAction()
	{
        $this->fileField = 'file';
		$this->config["pathFormat"] = $this->config['filePathFormat'];
		$this->config["maxSize"]    = $this->config['fileMaxSize'];
		$this->config["allowFiles"] = ['.zip'];
		$this->type = 'upload';
		$this->upFile();
		$ret = $this->getFileInfo();

		if ($ret['state'] == 'SUCCESS') {
		    $this->json([
	        'code' => 1,
	        'msg' => 'success',
	        'data' => [
	            'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
	            'title'    => (string)$ret['title'],
	            'original' => (string)$ret['original'],
	            'type'     => (string)$ret['type'],
	            'size'     => (string)$ret['size'],
	        ],
	        'url' => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
	        'wait' => 3,
	    ]);
		} else {
		    $this->json([
		        'code' => 10000,
		        'msg' => $ret['state'],
		        'data' => [],
		    ]);
		}
	}


    /**
     * uploadAdminZipAction 后台上传ZIP文件
     */
    public function uploadAdminSvgaAction()
    {
        $this->fileField = 'file';
        $this->config["pathFormat"] = $this->config['filePathFormat'];
        $this->config["maxSize"]    = $this->config['fileMaxSize'];
        $this->config["allowFiles"] = ['.svga'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();

        if ($ret['state'] == 'SUCCESS') {
            $this->json([
                'code' => 1,
                'msg' => 'success',
                'data' => [
                    'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                    'title'    => (string)$ret['title'],
                    'original' => (string)$ret['original'],
                    'type'     => (string)$ret['type'],
                    'size'     => (string)$ret['size'],
                ],
                'url' => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                'wait' => 3,
            ]);
        } else {
            $this->json([
                'code' => 10000,
                'msg' => $ret['state'],
                'data' => [],
            ]);
        }
    }

    /**
     * uploadImageAction 上传图片
     */
    public function uploadImageAction()
    {
        $this->config["pathFormat"] = $this->config['imagePathFormat'];
        $this->config["maxSize"]    = $this->config['imageMaxSize'];
        $this->config["allowFiles"] = $this->config['imageAllowFiles'];
        $this->fileField            = $this->config['imageFieldName'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();
        $ret['url'] = $this->config['qcloud']['image_cdn'].$ret['dstPath'];
        $this->json($ret);
    }

    /**
     * 上传音乐文件
     */
    public function uploadAdminMusicAction()
    {

        $this->fileField = 'file';
        $this->config["pathFormat"] = $this->config['musicPathFormat'];
        $this->config["maxSize"]    = $this->config['musicMaxSize'];
        $this->config["allowFiles"] = $this->config['musicAllowFiles'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();

        if ($ret['state'] == 'SUCCESS') {
            $this->json([
                'code' => 1,
                'msg' => 'success',
                'data' => [
                    'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                    'title'    => (string)$ret['title'],
                    'original' => (string)$ret['original'],
                    'type'     => (string)$ret['type'],
                    'size'     => (string)$ret['size'],
                ],
                'url' => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                'wait' => 3,
            ]);
        } else {
            $this->json([
                'code' => 10000,
                'msg' => $ret['state'],
                'data' => [],
            ]);
        }
    }

    /**
     * 后台上传视频
     */
    public function uploadAdminVideoAction()
    {
        $this->fileField = 'file';
        $this->config["pathFormat"] = $this->config['videoPathFormat'];
        $this->config["maxSize"]    = $this->config['videoMaxSize'];
        $this->config["allowFiles"] = $this->config['videoAllowFiles'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();

        if ($ret['state'] == 'SUCCESS') {
            $this->json([
                'code' => 1,
                'msg' => 'success',
                'data' => [
                    'url'      => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                    'title'    => (string)$ret['title'],
                    'original' => (string)$ret['original'],
                    'type'     => (string)$ret['type'],
                    'size'     => (string)$ret['size'],
                ],
                'url' => $this->config['qcloud']['image_cdn'].$ret['dstPath'],
                'wait' => 3,
            ]);
        } else {
            $this->json([
                'code' => 10000,
                'msg' => $ret['state'],
                'data' => [],
            ]);
        }
    }

    /**
     * uploadVideoAction 上传视频
     */
    public function uploadVideoAction()
    {
        $this->config["pathFormat"] = $this->config['videoPathFormat'];
        $this->config["maxSize"]    = $this->config['videoMaxSize'];
        $this->config["allowFiles"] = $this->config['videoAllowFiles'];
        $this->fileField            = $this->config['videoFieldName'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();
        $this->json($ret);
    }

    /**
     * uploadImageAction 上传Base64图片
     */
    public function uploadScrawlAction()
    {
        $this->config["pathFormat"] = $this->config['scrawlPathFormat'];
        $this->config["maxSize"]    = $this->config['scrawlMaxSize'];
        $this->config["allowFiles"] = $this->config['scrawlAllowFiles'];
        $this->config["oriName"]    = $this->config['scrawl.png'];
        $this->fileField            = $this->config['scrawlFieldName'];
        $this->type = 'base64';
        $this->upBase64();
        $ret = $this->getFileInfo();
        $this->json($ret);
    }

    /**
     * uploadImageAction 上传文件
     */
    public function uploadFileAction()
    {
        $this->config["pathFormat"] = $this->config['filePathFormat'];
        $this->config["maxSize"]    = $this->config['fileMaxSize'];
        $this->config["allowFiles"] = $this->config['fileAllowFiles'];
        $this->fileField            = $this->config['fileFieldName'];
        $this->type = 'upload';
        $this->upFile();
        $ret = $this->getFileInfo();
        $this->json($ret);
    }

	/**
	 * catchimageAction 抓取远程文件
	 */
	public function catchimageAction()
	{
		set_time_limit(0);
		$this->config["pathFormat"] = $this->config['catcherPathFormat'];
		$this->config["maxSize"]    = $this->config['catcherMaxSize'];
		$this->config["allowFiles"] = $this->config['catcherAllowFiles'];
		$this->config["oriName"]    = $this->config['remote.png'];
		$this->type = 'remote';
		$fieldName = $this->config['catcherFieldName'];
		/* 抓取远程图片 */
		$list = array();
		if (isset($_POST[$fieldName])) {
		    $source = $_POST[$fieldName];
		} else {
		    $source = $_GET[$fieldName];
		}

		foreach ($source as $imgUrl) {
			$this->fileField = $imgUrl;
			$this->saveRemote();
		    $info = $this->getFileInfo();
		    array_push($list, array(
				"state"    => $info["state"],
				"url"      => $info["url"],
				"size"     => $info["size"],
				"title"    => htmlspecialchars($info["title"]),
				"original" => htmlspecialchars($info["original"]),
				"source"   => htmlspecialchars($imgUrl)
		    ));
		}

		/* 返回抓取数据 */
		$this->json(array(
		    'state'=> count($list) ? 'SUCCESS':'ERROR',
		    'list'=> $list
		));
	}

// json 转 query string
    function json2str($obj, $notEncode = false) {
        ksort($obj);
        $arr = array();
        foreach ($obj as $key => $val) {
            !$notEncode && ($val = urlencode($val));
            array_push($arr, $key . '=' . $val);
        }
        return join('&', $arr);
    }
// 计算临时密钥用的签名
    private function getSignature($opt, $key, $method) {
        $formatString = $method . $this->config['qcloud']['Domain'] . '/v2/index.php?' . $this->json2str($opt, 1);
        $sign = hash_hmac('sha1', $formatString, $key);
        $sign = base64_encode(hex2bin($sign));
        return $sign;
    }
// 获取临时密钥
    private function getTempKeys() {
        // 判断是否修改了 AllowPrefix
//        $ShortBucketName = substr($config['Bucket'],0, strripos($config['Bucket'], '-'));
//        $AppId = substr($config['Bucket'], 1 + strripos($config['Bucket'], '-'));
        $ShortBucketName = $this->config['qcloud']['bucketName'];
        $AppId = $this->config['qcloud']['appId'];
        $policy = array(
            'version'=> '2.0',
            'statement'=> array(
                array(
                    'action'=> array(
                        // // 这里可以从临时密钥的权限上控制前端允许的操作
//                        'name/cos:*', // 这样写可以包含下面所有权限
                        // // 列出所有允许的操作
                        // // ACL 读写
                        // 'name/cos:GetBucketACL',
                        // 'name/cos:PutBucketACL',
                        // 'name/cos:GetObjectACL',
                        // 'name/cos:PutObjectACL',
                        // // 简单 Bucket 操作
                        // 'name/cos:PutBucket',
                        // 'name/cos:HeadBucket',
                        // 'name/cos:GetBucket',
                        // 'name/cos:DeleteBucket',
                        // 'name/cos:GetBucketLocation',
                        // // Versioning
                        // 'name/cos:PutBucketVersioning',
                        // 'name/cos:GetBucketVersioning',
                        // // CORS
                        // 'name/cos:PutBucketCORS',
                        // 'name/cos:GetBucketCORS',
                        // 'name/cos:DeleteBucketCORS',
                        // // Lifecycle
                        // 'name/cos:PutBucketLifecycle',
                        // 'name/cos:GetBucketLifecycle',
                        // 'name/cos:DeleteBucketLifecycle',
                        // // Replication
                        // 'name/cos:PutBucketReplication',
                        // 'name/cos:GetBucketReplication',
                        // 'name/cos:DeleteBucketReplication',
                        // // 删除文件
                        // 'name/cos:DeleteMultipleObject',
                        // 'name/cos:DeleteObject',
                        // 简单文件操作
                        'name/cos:PutObject',
                        'name/cos:PostObject',
                        'name/cos:AppendObject',
                        'name/cos:GetObject',
                        'name/cos:HeadObject',
                        'name/cos:OptionsObject',
                        'name/cos:PutObjectCopy',
                        'name/cos:PostObjectRestore',
                        // 分片上传操作
                        'name/cos:InitiateMultipartUpload',
                        'name/cos:ListMultipartUploads',
                        'name/cos:ListParts',
                        'name/cos:UploadPart',
                        'name/cos:CompleteMultipartUpload',
                        'name/cos:AbortMultipartUpload',
                    ),
                    'effect'=> 'allow',
                    'principal'=> array('qcs'=> array('*')),
                    'resource'=> array(
                        'qcs::cos:' . $this->config['qcloud']['region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/',
                        'qcs::cos:' . $this->config['qcloud']['region'] . ':uid/' . $AppId . ':prefix//' . $AppId . '/' . $ShortBucketName . '/' . $this->config['qcloud']['AllowPrefix']
                    )
                )
            )
        );
        $policyStr = str_replace('\\/', '/', json_encode($policy));
        $Action = 'GetFederationToken';
        $Nonce = rand(10000, 20000);
        $Timestamp = time() - 1;
        $Method = 'GET';
        $params = array(
            'Action'=> $Action,
            'Nonce'=> $Nonce,
            'Region'=> '',
            'SecretId'=> $this->config['qcloud']['secretId'],
            'Timestamp'=> $Timestamp,
            'durationSeconds'=> 7200,
            'name'=> '',
            'policy'=> $policyStr
        );
        $params['Signature'] = urlencode($this->getSignature($params, $this->config['qcloud']['secretKey'], $Method));
        $url = $this->config['qcloud']['Url'] . '?' . $this->json2str($params, 1);
        $ch = curl_init($url);
        $this->config['qcloud']['Proxy'] && curl_setopt($ch, CURLOPT_PROXY, $this->config['qcloud']['Proxy']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        if(curl_errno($ch)) $result = curl_error($ch);
        curl_close($ch);
        $result = json_decode($result, 1);
        return $result['data'];
    }

}
