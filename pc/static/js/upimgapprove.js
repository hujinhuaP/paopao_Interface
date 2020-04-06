// 目标盒子
	var result = document.getElementById("result"); 
	var rightPic = document.getElementById("rightPic");
	var leftPic = document.getElementById("leftPic");
	//img对象
    var imgobj=$(".uploadimg img");
	// 判断浏览器是否支持FileReader方法
	if(typeof FileReader==='undefined'){ 
	    result.innerHTML = "抱歉，你的浏览器不支持 FileReader";
	    //使远程控件不可以操作  
	    rightPic.setAttribute('disabled','disabled'); 
	    leftPic.setAttribute('disabled','disabled'); 
	}else{ 
		/*input.onchange=function(){
			readFile(imgobj);
		}*/
	    rightPic.addEventListener('change',readrightPic,false); 
	    leftPic.addEventListener('change',readleftPic,false); 
	};
	function readrightPic(){ 
	    var file = '';
	    var image = new Image(),
	    canvas = document.createElement("canvas"),
	    ctx = canvas.getContext('2d');
	    for(var i=0;i<this.files['length'];i++){
	       file = this.files[i];
	       // console.log(file); 
	       if(!/image\/\w+/.test(file['type'])){ 
	          alert("文件必须为图片！"); 
	          return false; 
	        } 
	        var reader = new FileReader();
	        //将文件以Data URL形式读入页面  
	        reader.readAsDataURL(file);
	        // console.log(reader);
	        reader.onload = function(e){
	          var url = reader.result;//读取到的文件内容.这个属性只在读取操作完成之后才有效,并且数据的格式取决于读取操作是由哪个方法发起的.所以必须使用reader.onload，
	          image.src=url;//reader读取的文件内容是base64,利用这个url就能实现上传前预览图片 
	          $(".rightSide img").attr("src",this.result);
	        }
	        image.onload = function() {
	           var w = 100,
	            h = 100;
	            canvas.width = w;
	            canvas.height = h;
	            ctx.drawImage(image, 0, 0, w, h, 0, 0, w, h);
	            fileUpload();
	        };
	        function fileUpload() {
	           var data = canvas.toDataURL("image/jpeg",0.8);
	           //dataURL 的格式为 “data:image/png;base64,****”,逗号之前都是一些说明性的文字，我们只需要逗号之后的就行了
	           data = data.split(',')[1];
	           data = window.atob(data);
	           var ia = new Uint8Array(data.length);
	           for (var i = 0; i < data.length; i++) {
	                ia[i] = data.charCodeAt(i);
	           };
	           //canvas.toDataURL 返回的默认格式就是 image/png
	           var blob = new Blob([ia], {
	           type: "image/jpeg"
	           });
	           var fd = new FormData();
	              fd.append('myFile', blob);
	        }       
	    }
	};
	function readleftPic(){ 
	    var file = '';
	    var image = new Image(),
	    canvas = document.createElement("canvas"),
	    ctx = canvas.getContext('2d');
	    for(var i=0;i<this.files['length'];i++){
	       file = this.files[i];
	       // console.log(file); 
	       if(!/image\/\w+/.test(file['type'])){ 
	          alert("文件必须为图片！"); 
	          return false; 
	        } 
	        var reader = new FileReader();
	        //将文件以Data URL形式读入页面  
	        reader.readAsDataURL(file);
	        // console.log(reader);
	        reader.onload = function(e){
	          var url = reader.result;//读取到的文件内容.这个属性只在读取操作完成之后才有效,并且数据的格式取决于读取操作是由哪个方法发起的.所以必须使用reader.onload，
	          image.src=url;//reader读取的文件内容是base64,利用这个url就能实现上传前预览图片 
	          $(".leftSide img").attr("src",this.result);
	        }
	        image.onload = function() {
	           var w = 100,
	            h = 100;
	            canvas.width = w;
	            canvas.height = h;
	            ctx.drawImage(image, 0, 0, w, h, 0, 0, w, h);
	            fileUpload();
	        };
	        function fileUpload() {
	           var data = canvas.toDataURL("image/jpeg",0.8);
	           //dataURL 的格式为 “data:image/png;base64,****”,逗号之前都是一些说明性的文字，我们只需要逗号之后的就行了
	           data = data.split(',')[1];
	           data = window.atob(data);
	           var ia = new Uint8Array(data.length);
	           for (var i = 0; i < data.length; i++) {
	                ia[i] = data.charCodeAt(i);
	           };
	           //canvas.toDataURL 返回的默认格式就是 image/png
	           var blob = new Blob([ia], {
	           type: "image/jpeg"
	           });
	           var fd = new FormData();
	              fd.append('myFile', blob);
	        }       
	    }
	};