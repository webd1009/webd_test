$.msg = function(data){
	layer.closeAll();
	var time = typeof(data.time) != 'undefined' ? data.time : 1;
	var msg = {content:data.msg, skin:'msg', time:time};
	if (typeof(data.url) != 'undefined') msg['end'] = function(){
		window.location.href = data.url;
	}
	if (typeof(data.reload) != 'undefined') msg['end'] = function(){
		window.location.reload();
	}
	layer.open(msg);
}

function countDown(endtime){
	var now = new Date();
	var leftTime = endtime * 1000 - now.getTime();
	var str = '';
	if (leftTime > 0) {
		var dd = parseInt(leftTime / 1000 / 60 / 60 / 24, 10);
		var hh = parseInt(leftTime / 1000 / 60 / 60 % 24, 10);
		var mm = parseInt(leftTime / 1000 / 60 % 60, 10);
		var ss = parseInt(leftTime / 1000 % 60, 10);
		if (dd) str += '<em>'+dd+'</em>天';
		if (dd || hh) str += '<em>'+hh+'</em>时';
		if (dd || hh || mm) str += '<em>'+mm+'</em>分';
		if (dd || hh || mm || ss) str += '<em>'+ss+'</em>秒';
	}
	return str;
}

$(function(){
	$('form input').keyup(function(){
		var disabled = false;
		var button = $("form button[type='submit']");
		$('form input').each(function(){
			if ($(this).val() == '') {
				disabled = true;
				return;
			}
		});
		disabled ? button.attr('disabled', true) : button.removeAttr('disabled');
	});
	$('.hxd input').not('.on').focus(function(){
		$(this).prev().addClass('on');
	});
	$('.hxd input').blur(function(){
		if ($(this).val() == '') $(this).prev().removeClass('on');
	});
	$('.hxd .pwd .iconfont').click(function(){
		var input = $(this).prev('input');
		if (input.attr('type') == 'password') {
			input.attr('type', 'text');
			$(this).removeClass('icon-pwd-hide').addClass('icon-pwd-show');
		} else {
			input.attr('type', 'password');
			$(this).removeClass('icon-pwd-show').addClass('icon-pwd-hide');
		}
	});
	$('.hxd form').submit(function(){
		layer.open({type:2});
		$.post(window.location.href, $(this).serialize()).then(function(data){
			$.msg(data);
			if (data.status == 0) $('.vcode img').attr('src', '/user/vcode?'+Math.random());
		}, function(){
			$.msg({msg:'通讯错误'});
		});
		return false;
	});

	$('header .menu').click(function(){
		if ($(this).find('.icon-menu').length) {
			$('body').css('overflow', 'hidden');
			$(this).find('i').removeClass('icon-menu').addClass('icon-close');
			$('.nav').css({'-webkit-transform':'translateX(0)', 'transform':'translateX(0)'});
		} else {
			$('body').removeAttr('style');
			$(this).find('i').removeClass('icon-close').addClass('icon-menu');
			$('.nav').css({'-webkit-transform':'translateX(100%)', 'transform':'translateX(100%)'});
		}
	});

	$('.item-box a.button').click(function(){
		layer.open({type:2});
		$.get($(this).attr('href')).then(function(data){
			$.msg(data);
		}, function(){
			$.msg({msg:'通讯错误'});
		});
		return false;
	});

// 	$('#pay a.button').click(function(){
// 		layer.open({type:2});
// 		$.post(window.location.href,$('form').serialize()).then(function(data){
// 			if (data.status == 2) {
// 				layer.closeAll();
// 				layer.open({
// 					type: 1,
// 					content: '<img style="width:90%;padding:0 5%;" src="/themes/uzi/assets/img/browser.png">',
// 					style: 'position:fixed;top:0;left:0;width:100%;height:100%;border:none;background:rgba(0,0,0,.5);'
// 				});
// 				return false;
// 			}
// 			$('.pay-form').html(data.form);
// 			$.msg(data);
// 			if (data.status == 1) {
// 				setTimeout(function(){
// 					$('#pay .pay-form form').submit();
// 				}, 1000);
// 			}
// 		}, function(){
// 			$.msg({msg:'通讯错误'});
// 		});
// 		return false;
// 	});

	$('#pay').on('click', '.layui-m-layerchild', function(){
		layer.closeAll();
	});

	$('#logout').click(function(){
		layer.open({type:2});
		$.get('/user/logout').then(function(data){
			$.msg(data);
		}, function(){
			$.msg({msg:'通讯错误'});
		});
	});

	$('#user-password form').submit(function(){
		layer.open({type:2});
		$.post(window.location.href, $(this).serialize()).then(function(data){
			$.msg(data);
		}, function(){
			$.msg({msg:'通讯错误'});
		});
		return false;
	});

	$('#user-cash .money input').keyup(function(){
		var money = $(this).val();
		if (!$.isNumeric(money)) {
			$(this).val('');
			$('#user-cash .real').text(0);
		} else {
			var real = money * (1 - cash_fee / 100);
			$('#user-cash .real').text(real.toFixed(2));
		}
	});
	$('#user-cash a.button').click(function(){
		var money = $('#user-cash .money input').val();
		if (!$.isNumeric(money)) {
			$.msg({msg:'提现金额输入错误'});
		} else {
			if (money > used) $.msg({msg:'账户余额不足'});
			if (money < cash_min) $.msg({msg:'提现金额不得小于'+cash_min});
			if (cash_max > 0 && money > cash_max) $.msg({msg:'提现金额不得大于'+cash_max});
			layer.open({type:2});
			$.post(window.location.href, {money: money}).then(function(data){
				$.msg(data);
			}, function(){
				$.msg({msg:'通讯错误', reload:1});
			});
		}
		return false;
	});
	$('#user-cash .list').on('click', 'a.cancel', function(){
		layer.open({type:2});
		$.post('/user/cash_cancel', {id:$(this).data('id')}).then(function(data){
			$.msg(data);
		}, function(){
			$.msg({msg:'通讯错误'});
		});
		return false;
	});
	if ($('#user-cash').length) $('#user-cash').dropload({
		scrollArea: window,
		loadDownFn: function(me){
			page++;
			$.post('/user/cash_ajaxlist', {page:page,type:$('#cash_type').val(),address:$('#address').val()}).then(function(data){
				$('#user-cash .list').append(data.html);
				if (data.status == 1) {
					me.lock();
					me.noData();
				}
				me.resetload();
			}, function(){
				$.msg({msg:'通讯错误'});
				me.resetload();
			});
		}
	});
	if ($('#user-order').length) $('#user-order').dropload({
		scrollArea: window,
		loadDownFn: function(me){
			page++;
			$.post(window.location.href, {page:page}).then(function(data){
				$('#user-order .list').append(data.html);
				if (data.status == 1) {
					me.lock();
					me.noData();
				}
				me.resetload();
				$('#user-order .countdown:not(.lock)').each(function(i,n){
					var endtime = $(n).data('endtime');
					$(n).addClass('lock').html(countDown(endtime)+' 后结算');
					setInterval(function(){
						$(n).html(countDown(endtime)+' 后结算');
					}, 1000);
				});
			}, function(){
				$.msg({msg:'通讯错误'});
				me.resetload();
			});
		}
	});
	if ($('#user-distrib').length) $('#user-distrib').dropload({
		scrollArea: window,
		loadDownFn: function(me){
			page++;
			$.post(window.location.href, {page:page}).then(function(data){
				$('#user-distrib .list').append(data.html);
				if (data.status == 1) {
					me.lock();
					me.noData();
				}
				me.resetload();
			}, function(){
				$.msg({msg:'通讯错误'});
				me.resetload();
			});
		}
	});
	if ($('#user-distrib-profit').length) $('#user-distrib-profit').dropload({
		scrollArea: window,
		loadDownFn: function(me){
			page++;
			$.post(window.location.href, {page:page}).then(function(data){
				$('#user-distrib-profit .list').append(data.html);
				if (data.status == 1) {
					me.lock();
					me.noData();
				}
				me.resetload();
			}, function(){
				$.msg({msg:'通讯错误'});
				me.resetload();
			});
		}
	});
	if ($('#user-bonus').length) {
		var obj = $('#user-bonus .countdown');
		var endtime = obj.data('endtime');
		obj.html(countDown(endtime));
		setInterval(function(){
			obj.html(countDown(endtime));
		}, 1000);

		$('#user-bonus').dropload({
			scrollArea: window,
			loadDownFn: function(me){
				page++;
				$.post(window.location.href, {page:page}).then(function(data){
					$('#user-bonus .list').append(data.html);
					if (data.status == 1) {
						me.lock();
						me.noData();
					}
					me.resetload();
				}, function(){
					$.msg({msg:'通讯错误'});
					me.resetload();
				});
			}
		});
	}
});
