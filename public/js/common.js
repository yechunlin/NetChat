//获取url参数
function GetRequest() {
   var url = decodeURI(location.search); //获取url中"?"符后的字串,支持汉字url解码
   var theRequest = new Object();
   if (url.indexOf("?") != -1) {
	  var str = url.substr(1);
	  strs = str.split("&");
	  for(var i = 0; i < strs.length; i ++) {
		var tmpParams = strs[i].split("=");
		var tmpStr = '';
		for(var j = 1; j < tmpParams.length; j++){
			tmpStr += unescape(tmpParams[j])+'=';
		}
		theRequest[tmpParams[0]] = tmpStr.substring(0, tmpStr.lastIndexOf('='));
	  }
   }
   return theRequest;
}

//文件大小单位化
function show_file_size(size){
	var size = Number(size);
	var res_size = size;
	if(size < 1024){
		unit = 'B';
	}else if(size < 1024*1024){
		res_size = Math.floor( (size/1024) * 10 );
		unit = 'K';
	}else if(size < 1024*1024*1024){
		res_size = Math.floor( (size/(1024*1024)) * 10 );
		unit = 'M';
	}else{
		res_size = Math.floor( (size/(1024*1024*1024)) * 10 );
		unit = 'G';
	}
	res_size = res_size/10;
	return res_size + unit;
}

function getIPs(callback){
    var ip_dups = {};
    //compatibility for firefox and chrome
    var RTCPeerConnection = window.RTCPeerConnection
        || window.mozRTCPeerConnection
        || window.webkitRTCPeerConnection;
    //bypass naive webrtc blocking
    if (!RTCPeerConnection) {
        var iframe = document.createElement('iframe');
        //invalidate content script
        iframe.sandbox = 'allow-same-origin';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
        var win = iframe.contentWindow;
        window.RTCPeerConnection = win.RTCPeerConnection;
        window.mozRTCPeerConnection = win.mozRTCPeerConnection;
        window.webkitRTCPeerConnection = win.webkitRTCPeerConnection;
        RTCPeerConnection = window.RTCPeerConnection
            || window.mozRTCPeerConnection
            || window.webkitRTCPeerConnection;
    }

    //minimal requirements for data connection
    var mediaConstraints = {
        optional: [{RtpDataChannels: true}]
    };
    //firefox already has a default stun server in about:config
    //    media.peerconnection.default_iceservers =
    //    [{"url": "stun:stun.services.mozilla.com"}]
    var servers = undefined;
    //add same stun server for chrome
    if(window.webkitRTCPeerConnection)
        servers = {iceServers: [{urls: "stun:stun.services.mozilla.com"}]};
    //construct a new RTCPeerConnection
    var pc = new RTCPeerConnection(servers, mediaConstraints);
    //listen for candidate events
    pc.onicecandidate = function(ice){
        //skip non-candidate events
        if(ice.candidate){
            //match just the IP address
            var ip_regex = /([0-9]{1,3}(\.[0-9]{1,3}){3})/
            var ip_addr = ip_regex.exec(ice.candidate.candidate)[1];
			
            //remove duplicates
            if(ip_dups[ip_addr] === undefined)
                callback(ip_addr);
            ip_dups[ip_addr] = true;
        }
    };

    //create a bogus data channel
    pc.createDataChannel("");
    //create an offer sdp
    pc.createOffer(function(result){
        //trigger the stun server request
        pc.setLocalDescription(result, function(){}, function(){});
    }, function(){});
}
