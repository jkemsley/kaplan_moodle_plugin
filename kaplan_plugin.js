var cpage = 0;
var upage = 0;
var courseurl = '';
var userurl = '';


function kaplan_loadCourseTable(eid, url, page) {
    courseurl = url;
    url += '&page=' + page;
    cpage = page;

    Y.one('.courseloading_image').setHTML('<img src="' + M.cfg.wwwroot + '/pix/i/loading_small.gif"/>');
    //Set the callback for yui ajax call
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
                    Y.one('.courseloading_image').setHTML('<p class="kaplan_error">Notice: Failed to parse json</p>');
                    return;
                }

                // Has moodle given us an unexpected response
                if(courses instanceof Array === false) {
                    Y.one('.courseloading_image').setHTML('<p class="kaplan_notice">Notice: You do not have access to use this service</p>');
                    return;
                }

                //Have we courses?
                if(courses.length === 0) {
                    Y.one('.courseloading_image').setHTML('<p class="kaplan_empty">No courses</p>');
                    return;
                }

                var clength = courses.length === 6 ? 5 : courses.length;

                // Loop through courses and create html
                for (i=0, l=clength; i < l; ++i) {
                    var url = M.cfg.wwwroot + '/course/view.php?id=' + courses[i].id;
                	html += '<tr>';
                    html += '<td>' + courses[i].id + '</td>';
                    html += '<td><a href="' + url + '">'+ courses[i].fullname + '</a></td>';
                    html += '<td class="kap_table_ue">' + courses[i].users_enrolled + '</td>';
                    html += '</tr>';
                }

                // Hide loading gif and append html to table
                Y.one('.courseloading_image').setStyle('display', 'none');
                if(page > 0) {
                    Y.one('#kaplan_courses_prev').setStyle('display', 'block');
                }
                if(courses.length === 6) {
                    Y.one('#kaplan_courses_next').setStyle('display', 'block');
                }
                Y.one('#' + eid + ' tbody').append(html);
                Y.one('#' + eid).setStyle('display', 'table');
            },
            failure : function (x,o) {
                Y.log("Async call failed!");
                Y.one('.courseloading_image').setHTML('<p class="kaplan_error">Notice: Service call failed</p>');
            }
        }
    };
	Y.io(url, callback);
}

function kaplan_loadUserTable(eid, url, page) {
    upage = page;
    userurl = url;
    url += '&page=' + page;

    Y.one('.userloading_image').setHTML('<img src="' + M.cfg.wwwroot + '/pix/i/loading_small.gif"/>');

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
                    Y.one('.userloading_image').setHTML('<p class="kaplan_error">Notice: Failed to parse json</p>');
                    return;
                }

                // Has moodle given us an unexpected response
                if(users instanceof Array === false) {
                    Y.one('.userloading_image').setHTML('<p class="kaplan_notice">Notice: You do not have access to use this service</p>');
                    return;
                }

                //Have we users?
                if(users.length === 0) {
                    Y.one('.userloading_image').setHTML('<p class="kaplan_empty">No users</p>');
                    return;
                }


                var ulength = users.length === 11 ? 10 : users.length;

                // Loop through users and create html
                for (i=0, l=ulength; i < l; ++i) {
                    var url = M.cfg.wwwroot + '/user/profile.php?id=' + users[i].id;
                	html += '<tr>'
                    html += '<td>' + users[i].id + '</td>';
                    html += '<td><a href="' + url + '">' + users[i].fullname + '</a></td>';
                    html += '</tr>';
                }

                // Hide loading gif and append html to table
                Y.one('.userloading_image').setStyle('display', 'none');

                if(page > 0) {
                    Y.one('#kaplan_users_prev').setStyle('display', 'block');
                }
                if(users.length === 11) {
                    Y.one('#kaplan_users_next').setStyle('display', 'block');
                }
                Y.one('#' + eid).setStyle('display', 'table');
                Y.one('#' + eid + ' tbody').append(html);
            },
            failure : function (x,o) {
                Y.log("Async call failed!");
                Y.one('.userloading_image').setHTML('<p class="kaplan_error">Notice: Service call failed</p>');
            }
        }
    };
	Y.io(url, callback);
}

function kaplan_register_btns(ceid, ueid) {
    Y.one('#kaplan_courses_next').on('click',  function(e) {
        Y.one('#' + ceid + ' tbody').setHTML('');
        Y.one('.courseloading_image').setStyle('display', 'block');
        Y.all('.kaplan_courses_btn').setStyle('display', 'none');
        kaplan_loadCourseTable(ceid, courseurl, cpage+1);
    });

    Y.one('#kaplan_courses_prev').on('click',  function(e) {
        Y.one('#' + ceid + ' tbody').setHTML('');
        Y.one('.courseloading_image').setStyle('display', 'block');
        Y.all('.kaplan_courses_btn').setStyle('display', 'none');
        kaplan_loadCourseTable(ceid, courseurl, cpage-1);
    });

    Y.one('#kaplan_users_next').on('click',  function(e) {
        Y.one('#' + ueid + ' tbody').setHTML('');
        Y.one('.userloading_image').setStyle('display', 'block');
        Y.all('.kaplan_users_btn').setStyle('display', 'none');
        kaplan_loadUserTable(ueid, userurl, upage+1);
    });

    Y.one('#kaplan_users_prev').on('click',  function(e) {
        Y.one('#' + ueid + ' tbody').setHTML('');
        Y.one('.userloading_image').setStyle('display', 'block');
        Y.all('.kaplan_users_btn').setStyle('display', 'none');
        kaplan_loadUserTable(ueid, userurl, upage-1);
    });
}