/*
 * create by ycl
 * 2019-11-15 上午
 * 文件上传类
 */
class uploadFile{
	constructor(initInfo){
		var that = this;
		this.fileObject = {};
		this.fileName = initInfo.fileName; //input标签name属性名称
		this.fileIdName = initInfo.fileIdName; //input标签id属性名称
		this.saveFileName = initInfo.hasOwnProperty('saveFileName') ? initInfo.saveFileName : '';//服务端保存文件名
		this.saveFileExtention = initInfo.hasOwnProperty('saveFileExtention') ? initInfo.saveFileExtention : '';//服务端保存文件后缀名
		this.saveFileType = '';//服务端保存文件类型
		this.uploadFileObj = document.getElementById(initInfo.fileIdName); //获取文件对象
		this.chunkSize  = initInfo.chunkSize; //分片文件大小
		this.uploadChunkNum = 0; //计算需要上传多少次，方便显示进度
		this.uploadTimes = 0; //用于进度显示，当前属于哪一次
		this.httpRequestUrl = initInfo.httpRequestUrl;// 服务端api
		this.fileStart = 0;// 分片进度
		
		this.uploadFileObj.addEventListener("change",function (event) {
			//获取到选中的文件
			var file = event.target.files[0];
			//多次在同一个input上选择文件，当取消时，会出现file为undefined
			if(typeof(file) == 'undefined') return ;

			that.fileObject = file;
			that.uploadChunkNum = Math.ceil(file.size/that.chunkSize);
			var index = file.name.lastIndexOf('.');
			that.saveFileExtention = file.name.substring(index + 1);//获取文件后缀
			that.saveFileType = file.type;//获取文件类型
			//通报.开始上传
			that.uploadStart({
				'fileName' : file.name,
				'fileSize' : file.size,
				'fileType' : file.type
			});
			//进行文件上传
			that.execUpload();
		});
	}
	//完成时，部分变量初始化
	init(){
		this.uploadTimes = 0;
		this.fileStart = 0;
		this.saveFileName = '';
		this.saveFileExtention = '';
		this.saveFileType = '';
	}
	//具体上传方法
	execUpload(){
		var that = this;
		//先声明一个异步请求对象
		var xmlHttpReg = null;
		if (window.ActiveXObject) {
			xmlHttpReg = new ActiveXObject("Microsoft.XMLHTTP");
		} else if (window.XMLHttpRequest) {
			xmlHttpReg = new XMLHttpRequest(); 
		}
		if(this.fileStart < this.fileObject.size){
			//切片
			var blob = this.fileObject.slice(this.fileStart, this.fileStart + this.chunkSize);
			this.fileStart = this.fileStart + blob.size;
			//创建formdata对象
			var formData = new FormData();
			formData.append(this.fileName,blob);
			formData.append('get_file_name',this.fileName);
			formData.append('save_file_name',this.saveFileName);
			formData.append('save_file_extention',this.saveFileExtention);
			formData.append('save_file_type',this.saveFileType);
			//如果实例化成功,就调用open()方法,就开始准备向服务器发送请求
			if (xmlHttpReg != null) {
				xmlHttpReg.open('post', this.httpRequestUrl, true);
				//xmlHttpReg.setRequestHeader("Content-type","application/x-www-form-urlencoded");
				xmlHttpReg.send(formData);//传参
				xmlHttpReg.onreadystatechange =  function () {   
					if (xmlHttpReg.readyState == 4 && xmlHttpReg.status == 200) { 
							var data = JSON.parse(xmlHttpReg.response);//对返回数据做对象转换
							if(data.state == 1){
								//通报.上传进度
								that.progress({
									'size' : that.fileObject.size,
									'needUploadNum' : that.uploadChunkNum,
									'thisTurn' : ++that.uploadTimes,
									'havefinished' : (that.fileStart/that.fileObject.size) * 100
								});
								
								that.saveFileName = data.saveFileName;
								if(that.fileStart >= that.fileObject.size){
									//通报.上传完成
									that.uploadSuccess(data);
								}
								//递归，持续上传
								that.execUpload();
							}
					 }
				}
			}
		}else{
			this.init();
		}
	}
	//开始上传
	uploadStart(data){
		//
	}
	//进度调用
	progress(data){
		//
	}
	//上传完成
	uploadSuccess(data){
		//
	}
}