/**
 * @author Artyom Grigoriev
 */
var api = {
	__queue: new Queue(),
	__cycling: false,
	__number: 1,
	__requested: {},

	TIMEOUT: 250,
	URL: '/api.php',

	__init: function () {
		api.__queue = new Queue();
		api.__process();
	},

	__process: function () {

		if (!api.__queue.empty()) {
			var task = api.__queue.get(),
				doRequest = true;
			debug('[api] task-'+task.number+' processing started');
			if (task.preventRepeating) {
				var key = task.handler + "/" + task.method + "/";
				for (var k in task.data) {
					if (!task.data.hasOwnProperty(k)) continue;
					key += k + "=" + task.data[k];
				}
				if (api.__requested[key]) {
					debug('[api] task-'+task.number+' processing is a repeating of ' + api.__requested[key] + ', so we do not execute it');
					doRequest = false;
				}
				api.__requested[key] = task.number;
			}

			if (doRequest) {
				var ajax = {
					url:api.URL,
					data:{
						handler:task.handler,
						method:task.method
					},
					dataType:task.dataType ? task.dataType : 'json',
					cache:false,
					success:function (response) {
						if ((!response.status || response.status == 'ok') && task.success) {
							debug('[api] task-' + task.number + ' succeeded');
							task.success(response);
						}

						if (response.retry) {
							debug('[api] task-' + task.number + ' retry');
							api.request(task);
						}
					},
					error:task.error ? task.error : function () {
						debug('[api] task-' + task.number + ' error');
					}
				};

				$.extend(ajax.data, task.data);
				$.ajax(ajax);
				debug('[api] task-' + task.number + ' processing finished');
			}
		}

		setTimeout(api.__process, api.TIMEOUT);
		//debug('[api] timeout ' + api.TIMEOUT + 'ms');
		return true;
	},

	request: function (task) {
		if (!task.number)
			task.number = api.__number++;
		
		api.__queue.add(task);
		debug('[api] task-'+task.number+' added: handler=' + task.handler + '&method=' + task.method);

		if (task.beforeSend) {
			task.beforeSend();
		}			
	}
};

$(api.__init());
