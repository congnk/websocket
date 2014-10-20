<!DOCTYPE html>
<html>
<head>
<script src="/js/jquery-1.8.3.min.js"></script>
<script src="/js/jquery.cookie.js"></script>

<meta charset=utf-8 />
<style type="text/css">

</style>
<link rel="stylesheet" href="style.css">
<title>WebSockets Client</title>

</head>
<body>
  <div id="wrapper">
  
  	<div id="container">
		<div class="main">
    	<h1>WebSockets Client</h1>
        
        <div id="chatLog" class="chatPane">
			<a id="clear_scr" href="javascript:;" class="qp">清屏</a>
        
        </div>
        <p id="examples">e.g. try 'hi', 'name', 'age', 'today'</p>
        
		<form id="rename_form" >
			<input id="text" type="text" />
			<button id="sd" type="submit">改名</button><br/>
		</form>
        <button id="disconnect">断开</button>
		<button id="reconnect" disabled>重新连接</button>
		</div>
		<div id="userList" class="roll_right list">
			<ul>用户列表
			</ul>
		</div>
	</div>
  </div>
<script type="text/javascript">
$(function() {
		$("#clear_scr").on("click",function(){
			$(this).find("~").remove();
		});
		
		$("#text").focus();
});

//包括从其他会话中传递进来的消息
function addToUserList( arr ){
	if(!arr) return ;
	$.each(arr,function(k,v){
		var $oldLi = $("#userList ul li#"+k);
		if($oldLi.length == 0){
			var $li = $("<li>"+v + "</li>");
			$li.attr("id",k).addClass("connected");
			$("#userList ul").append($li);
		}else{
			$oldLi.text(v);
			$oldLi.addClass("connected").removeClass("disconnected");
		}
	});
}
</script>
<script type="text/javascript">
var socket;
$(document).ready(function() {

	$("#rename_form").submit(function(){
		send(socket);
		$.cookie("name",$("#text").text());
		return false;
	});
	
	webSocketConnect();


	function webSocketConnect(){
		if(!("WebSocket" in window)){
			$('#chatLog, input, button, #examples').fadeOut("fast");	
			$('<p>Oh no, you need a browser that supports WebSockets. How about <a href="http://www.google.com/chrome">Google Chrome</a>?</p>').appendTo('#container');		
		}else{
			//The user has WebSockets
			connect();
		}//End connect()
	}
	
	function connect(){
		var host = "ws://localhost:8000";

		try{
			socket = new WebSocket(host);
			message('Socket Status: '+socket.readyState,'event');
			socket.onopen = function(){
				message('Socket Status: '+socket.readyState+' (open)' ,'event');	
				$("#disconnect").attr("disabled",false);
				$("#reconnect").attr("disabled",true);
			}
			
			socket.onmessage = function(msg){
				console.log(msg.data);
				parseMessage(msg);
		
			}
			
			socket.onclose = function(){
				message('Socket Status: '+socket.readyState+' (Closed)' ,'event');
				$("#reconnect").attr("disabled",false);
				$("#disconnect").attr("disabled",true);
				$("#userList li").addClass("disconnected").removeClass("connected");
			}			
				
		} catch(exception){
			message(exception,"error");
		}

		
		$('#disconnect').on("click",function(){
			socket.close();
		});
		
		$('#reconnect').on("click",function(){
			webSocketConnect();
			reconnect();
		});

	}	
});

function send(socket){
	console.log("send is called");
	var text = $('#text').val();
	if(text==""){
		//message('Please enter a message','warning');
		return ;	
	}
	try{
		socket.send('type=add&ming='+text);
		message('Sent: '+text,'event')
	} catch(exception){
		message('Warning!','warning');
	}
	$('#text').val("");
}

function parseMessage(msg){
	var jdata = jQuery.parseJSON(msg.data);
	if(jdata.add){
		addToUserList(jdata.users);
		message(jdata.nrong,"event");
		return ;
	}
	if(jdata.remove){
		var rid=jdata.removekey ;
		$("#userList li#" + rid).css({"color":"grey"});
		message(jdata.nrong,'warning');
	}
	
}

function reconnect(){
	if($.cookie('name')){
		socket.send('type=add&ming='+$.cookie('name'));
	}
	
}
			
	function message(msg , style_class){
		var $aa = $("<p></p>").addClass(style_class).text(msg);
		$('#chatLog').append( $aa );
	}//End message()
</script>
</body>
</html>​