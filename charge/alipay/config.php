<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2018102461815486",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDOkDp6mAhsMbAcT2iioBxCt3U0oyG89VdSHfAigoDxoV3L156Mya4csP6wHCMmdGOSBHSqXBFMjscNLJHj/OSNn45wvDJYZP8OtJp/hpjmkB91PiBSmJrDVVgHOfdJlLCcDeIMeefo3WcF5OrXsiRosmbFSXzUnZksH4MJu7frXfdM3+0xfcvEkmgot/yE/o6JQVzB57Gq2tDxs/WbTye4OcNRlpFF8jEzpFneXLiAykNQilI9f3P5xqxThkF/A85jT/DfMiEeJU2C6JhCoXMz70cf6X5tLrn9xWYSSrvY//j7l5GB2b3EkUbOZUYXqz9QgaIoT8Vw8w/NW2I/hpwxAgMBAAECggEBAMf10sZuemjSSNt++5nCSNlE408LRFO5ZMh3dsjRcKV4QmZb2n4LlmLr7ADrnBNTxDfL3Gw2KADmjkZwiOIdI9r9RFRZuprbWhUQPCeLUmSPzAQhGgUa+WZyLX8BXCN8ruLChbryH8/K1DpeegBH0PsRCG+fTho8XdTaxG0drVNHq6euvmxMs3jBkoz7vweBoDKxpQbhNh5r98B2FPUph855JbUGDEejFSwSXFZHOB1g9+Y9tupk8W4upkOXlYO6ItsXgSdEIbX7v4t1+qgoLuRIgT+yUwXBvn1IX6g8E9ddlMMyjW52oCrwQ0P2N3MvF7YMY9ITzIaU5Tvyij6kGikCgYEA8X8TqmZoX1TkrRaC1+H2l/IDYir2VV/9Q2ypcEk6x1rvEosuJF2qZrXreOhZ51UEnDGc8V+KpX0/FxN124opTSKWnrPKyexi6scZjLP/eW7jz3ef2XiWoWqhOP5bfd1UAw6vg6wB7mQEp/yMNmAKN5VMhkiljhNMYSQsVVNlR/MCgYEA2vgRoBD8EUJshFXu0WLQBUswCM63VKCIMR7QgVphzLFhj6tyIPl9MHarqnmyaRO30KdydDo1jDlmZ2jcQE2MWWFZ1up0zlxjoBiF5RffudF5wDWJchygy3uGIV+WUpKldqcTjP72Fmf6+UV9f1dGVwSbZKiNzmB+hE3gaX4lWEsCgYEAmBFhHMfnqUAXzzaBpddQJFXs84ACJbiQDkj6WQ6DyMzmBlNF9vhUOOENKdGF6zmJ8aD8JrH26EZ519oVOO1DHKNPHRgx9fy4PQaqfANMN/cv1JCLQ7G/iF1QsEba7eLU6CfzNYK2pJquo+lPkV3gkSeeTGCqf1B/pBvXHtOozykCgYBBNLYq8GPfz+P41I41lDNWIDnBpa06akOkPQTiQEP3bKsc2XU3FJSPJgeg0HSsjc6jN/oBWoQvqbgw+yz7iRxOUYsrUM5P1XtlZWgZ/K4G67ZR4p93d8b6UWJz9b8R/9F+L+rGhfZKXdSC/oqMrTSpHRoZM4hm+J00UOyO/Z2pWQKBgQDr80ihXq1QSq5quvtS+QmTwL600L6jZrLcKySKLFE+YRUhZFKbp2x3rudFdhQ4qBu1RNJhrocWHHh27HgGE0AbOKyW8j8hNfWNPIffs/H1d7Tcrsjjk6aUfwckSS07+fZbHnFwWEwTFjbS/S9I5y4tRIqjG/NEEQ4wUnUcFHEFPw==",
		
		//异步通知地址
		'notify_url' => "http://charge.860051.cn/alipay/alipay_notify.php",

        //VIP异步通知地址
        'notify_vip_url' => "http://charge.860051.cn/alipay/alipay_notify_vip.php",
		
		//同步跳转
		'return_url' => "http://charge.860051.cn/pay_v2.php",

        //VIP充值跳转
        'return_vip_url' => "http://charge.860051.cn/vip_v2.php",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnGCFncJqrxlBheiieR2uQeAppOnRiNcNgGLbRfQ1ULaIU4hxf7rwclQh+fBDgyFfLPCdZSd40n8hONT6jP47EUL15immaimhf4So9NH7VsTekouUd9aDUFt9jjnV1+7tBWfHiguK6c80dTZv8mFBLXjcoq5MkGzmWAZO18XuAap9WAytcH+LqfxTiEwRMVHy1KOCND7bjsbBNsYa16FREIpf+eTRqs40KcNgZdQBtQ9rYI+6JkhikX6uRc+6zag0KY6w0OMFRHhOvGa1gu4Lvsyszykh5v7uyT+s4K9JKWCI4IWIXFSX5X1btmKXAm96oYEIyV0tnWcs5itVLpnO8wIDAQAB",

);