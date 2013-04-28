function kaplan_loadCourseTable(eid, url) {

	var callback = {
        timeout : 5000,
        on : {
            success : function (x,o) {
            	var html = '';
                // Process the JSON data returned from the server
                try {
                    courses = Y.JSON.parse(o.responseText);
                }
                catch (e) {
                    Y.log("JSON Parse failed!");
                    return;
                }
                for (i=0, l=courses.length; i < l; ++i) {
                	html += '<tr>';
                    html += '<td>' + courses[i].id + '</td>';
                    html += '<td>' + courses[i].fullname + '</td>';
                    html += '<td>' + courses[i].users_enrolled + '</td>';
                    html += '</tr>';
                }
                Y.one('.courseloading_image').setStyle('display', 'none');
                Y.one('#' + eid).append(html).setStyle('display', 'table');
            },
            failure : function (x,o) {
                Y.log("Async call failed!");
            }
        }
    };
	Y.io(url, callback);
}

function kaplan_loadUserTable(eid, url) {

	var callback = {
        timeout : 5000,
        on : {
            success : function (x,o) {
            	var html = '';
                // Process the JSON data returned from the server
                try {
                    users = Y.JSON.parse(o.responseText);
                }
                catch (e) {
                    Y.log("JSON Parse failed!");
                    return;
                }
                for (i=0, l=users.length; i < l; ++i) {
                	html += '<tr>'
                    html += '<td>' + users[i].id + '</td><td>' +
                            users[i].fullname + '</td></tr>';
                }
                Y.one('.userloading_image').setStyle('display', 'none');
                Y.one('#' + eid).append(html).setStyle('display', 'table');
            },
            failure : function (x,o) {
                Y.log("Async call failed!");
            }
        }
    };
	Y.io(url, callback);
}