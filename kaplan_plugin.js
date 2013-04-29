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

                if(courses instanceof Array === false) {
                    Y.one('.courseloading_image').setHTML('<p class="kaplan_notice">Notice: You do not have access to use this service</p>');
                    return;
                }

                for (i=0, l=courses.length; i < l; ++i) {

                    var url = M.cfg.wwwroot + '/course/view.php?id=' + courses[i].id;
                	html += '<tr>';
                    html += '<td>' + courses[i].id + '</td>';
                    html += '<td><a href="' + url + '">'+ courses[i].fullname + '</a></td>';
                    html += '<td class="kap_table_ue">' + courses[i].users_enrolled + '</td>';
                    html += '</tr>';
                }
                Y.one('.courseloading_image').setStyle('display', 'none');
                Y.one('#' + eid + ' tbody').append(html);
                Y.one('#' + eid).setStyle('display', 'table');
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

                if(users instanceof Array === false) {
                    Y.one('.userloading_image').setHTML('<p class="kaplan_notice">Notice: You do not have access to use this service</p>');
                    return;
                }

                for (i=0, l=users.length; i < l; ++i) {
                    var url = M.cfg.wwwroot + '/user/profile.php?id=' + users[i].id;
                	html += '<tr>'
                    html += '<td>' + users[i].id + '</td>';
                    html += '<td><a href="' + url + '">' + users[i].fullname + '</a></td>';
                    html += '</tr>';
                }
                Y.one('.userloading_image').setStyle('display', 'none');
                Y.one('#' + eid).setStyle('display', 'table');
                Y.one('#' + eid + ' tbody').append(html);
            },
            failure : function (x,o) {
                Y.log("Async call failed!");
            }
        }
    };
	Y.io(url, callback);
}