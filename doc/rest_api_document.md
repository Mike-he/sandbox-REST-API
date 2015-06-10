FORMAT: 1A

# Sandbox resource API

Sandbox API follows the RESTFul standard and provides access to resource in Sandbox


# Group Feeds
Feed related resources of **Feed API**

## Feed Colletcion [/feeds]
### List all feeds [GET]

+ Parameters
    + limit (number, optional)... `limit={limit}`, the maximum number of results to be returned; a number between `1` and `99`, default to `20`
        + Example `limit=66`
    + last (number, optional)... `last={last}`, `id` of the feed to start after, only return feeds whose `id` is larger than `last`
        + Example `last=88`
    + companies (array, optional)... `companies[]={company_id}`, array of companies to filter feeds by companies; each parameter should be the `id` of the company
        + Example `companies[]=66`&`companies[]=88`

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "content": "this is a new post",
                "ownerid": "1000231",
                "parentid": 1,
                "parenttype": "company",
                "creationdate": "1478233230000",
                "comments_count": 0,
                "likes_count": 9,
                "my_like_id": 5,
                "attachments": [
                    {
                        "id": 2,
                        "fid": 1,
                        "content": "url or id",
                        "attachmenttype": "picture",
                        "filename": "bigbrother",
                        "preview": "base64",
                        "size": 2048
                    }
                ]
            },
            {
                "id": 2,
                "content": "this is the second post",
                "ownerid": "1000231",
                "parentid": 1,
                "parenttype": "company",
                "creationdate": "1478233230000",
                "comments_count": 0,
                "likes_count": 9,
                "my_like_id": 3,
                "attachments": [
                    {
                        "id": 7,
                        "fid": 2,
                        "content": "url or id",
                        "attachmenttype": "picture",
                        "filename": "bigbrother",
                        "preview": "base64",
                        "size": 2048
                    }
                ]
            }
        ]

### Create a feed [POST]
+ Request (application/json)

        {
            "content": "this is a second post",
            "parentid": 1,
            "parenttype": "company",
            "attachments": [
                {
                    "content": "url or id",
                    "attachmenttype": "picture",
                    "filename": "bigbrother",
                    "preview": "base64",
                    "size": 2048
                }
            ]
        }

+ Response 201 (application/json)

        { "id" : 168 } 

## Feed [/feeds/{feed_id}]
A single feed object with all its details

+ Parameters
    + feed_id (number, required) ... Numeric `id` of the feed to perform action with

### Retrieve a feed [GET]
+ Response 200 (application/json)

        {
            "id": 2,
            "content": "this is a second post",
            "ownerid": "1000231",
            "parentid": 1,
            "parenttype": "company",
            "creationdate": "1478233230000",
            "comments_count": 2,
            "likes_count": 9,
            "my_like_id": 3,
            "attachments": [
                {
                    "id": 7,
                    "fid": 2,
                    "content": "url or id",
                    "attachmenttype": "picture",
                    "filename": "bigbrother",
                    "preview": "base64",
                    "size": 2048
                }
            ]
        }

### Delete a feed [DELETE]
Feed owner could delete his/her own feed

+ Response 204

## Comment Collection [/feeds/{feed_id}/comments]
Comments of a specific feed

+ Parameters
    + feed_id (number, required) ... Numeric `id` of the feed to perform action with

### List all comments [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "fid": 2,
                "authorid": "100003456",
                "payload": "{\"service\":\"MessageShare\",\"message\":\"comment1\"}",
                "creationdate": "1497732300000"
            },
            {
                "id": 2,
                "fid": 2,
                "authorid": "100003456",
                "payload": "{\"service\":\"MessageShare\",\"message\":\"comment2\"}",
                "creationdate": "1497732300000"
            }
        ]

### Create a comment [POST]
+ Request (application/json)

        {
            "payload": "{\"service\":\"MessageShare\",\"message\":\"comment2\"}"
        }

+ Response 201 (application/json)

        {  
            "id":15,
            "creationdate": "1475269870000"
        }

## Comment [/feeds/{feed_id}/comments/{comment_id}]
A single comment object with all its details

+ Parameters
    + feed_id (number, required) ... Numeric `id` of the feed to perform action with
    + comment_id (number, required) ... Numeric `id` of the comment to perform action with
    
### Delete a comment [DELETE]
Comment author could delete his/her own comment

+ Response 204

## Like Collection [/feeds/{feed_id}/likes]
Likes of a specific feed

+ Parameters
    + feed_id (number, required) ... Numeric `id` of the feed to perform action with

### Like a feed [POST]
+ Response 201 (application/json)

        {  
            "id":15
        }

### Unlike a feed [DELETE]
Like author could delete his/her own like

+ Response 204

## Like [/feeds/{feed_id}/likes/{like_id}]
A single like object with all its details

+ Parameters
    + feed_id (number, required) ... Numeric `id` of the feed to perform action with
    + like_id (number, required) ... Numeric `id` of the like to perform action with
    
### Delete a like with its ID [DELETE]
Like author could delete his/her own like

+ Response 204


# Group Tasks
Task related resources of **Task API**

##  Task Collection [/tasks]
### List all tasks [GET]
Task status: `new`, `inprogress`, `closed`, `cancelled`

+ Parameters
    + limit (number, optional)... `limit={limit}`, the maximum number of results to be returned; a number between `1` and `99`, default to `20`
        + Example `limit=66`
    + last (number, optional)... `last={last}`, `id` of the task to start after, only return tasks whose `id` is larger than `last`
        + Example `last=88`
    + companies (array, optional)... `companies[]={company_id}`, array of companies to filter tasks by companies; each parameter should be the `id` of the company
        + Example `companies[]=66`&`companies[]=88`
    + groups (array, optional)... `groups[]={group_id}`, array of groups to filter tasks by groups; each parameter should be the `id` of the group
        + Example `groups[]=123`&`groups[]=456`
    + roles (array, optional)... `roles[]={role}`, array of roles to filter tasks by roles; each parameter should be the `value` of my role
        + Example `roles[]=owner`&`roles[]=assignee`
    + statuses (array, optional)... `statuses[]={status}`, array of statuses to filter tasks by statuses; each parameter should be the `value` of the task's status
        + Example `statuses[]=new`&`statuses[]=closed`

+ Response 200 (application/json)

        [
            {
                "assigneeid": "90266271",
                "comments_count": 1,
                "creationdate": "1413524405876",
                "description": "lets have fun with this tools",
                "duedate": "1413524304288",
                "has_attachment": true,
                "id": 22,
                "modificationdate": "1413524425133",
                "ownerid": "90266271",
                "parentid": 1,
                "parenttype": "company",
                "priority": "normal",
                "service": "TaskShare",
                "status": "inprogress",
                "title": "This is not a test environment "
            },
            {
                "assigneeid": "90266271",
                "comments_count": 1,
                "creationdate": "1413524405876",
                "description": "lets have fun with this tools",
                "duedate": "1413524304288",
                "has_attachment": true,
                "id": 23,
                "modificationdate": "1413524425133",
                "ownerid": "90266271",
                "parentid": 2,
                "parenttype": "group",
                "priority": "normal",
                "service": "TaskShare",
                "status": "inprogress",
                "title": "This is not a test environment "
            }
        ]
        
### Create a task [POST]
+ Request (application/json)

        {
            "title":"task title",
            "description":"task desc",
            "priority":"normal",
            "duedate":"1452698",
            "ownerid":"45409951",
            "assigneeid":"24628810",
            "parentid":2,
            "parenttype":"group or company",
            "observers":[
                {"userid":"45409951"},
                {"userid":"55414551"}
            ],
            "attachments":[
                 {
                    "content": "url_or_id",
                    "attachmenttype": "picture",
                    "filename": "bigView",
                    "preview": "base64",
                    "size": 1028
                },
                 {
                    "content": "url_or_id",
                    "attachmenttype": "picture",
                    "filename": "bigView",
                    "preview": "base64",
                    "size": 2048
                }
            ]
        }

+ Response 201 (application/json)

        { "id" : 3301 } 


## Task [/tasks/{task_id}]
A single task object with all its details

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    
### Retrieve a task [GET]
+ Response 200 (application/json)

        {
            "assigneeid": "90266271",
            "comments_count": 1,
            "creationdate": "1413524405876",
            "description": "lets have fun with this tools",
            "duedate": "1413524304288",
            "has_attachment": true,
            "id": 1,
            "modificationdate": "1413524425133",
            "ownerid": "90266271",
            "parentid": 4,
            "parenttype": "company",
            "priority": "normal",
            "service": "TaskShare",
            "status": "inprogress",
            "title": "This is not a test environment "
        }


### Update a task [PATCH]
+ Request (application/json)

        [
            {
                "op": "add",
                "path": "/status",
                "value": "inprogress"
            },
            {
                "op": "add",
                "path": "/title",
                "value": "hello php"
            }
        ]

+ Response 200 (application/json)

        {
            "assigneeid": "90266271",
            "comments_count": 1,
            "creationdate": "1413524405876",
            "description": "lets have fun with this tools",
            "duedate": "1413524304288",
            "has_attachment": true,
            "id": 1,
            "modificationdate": "1413524425133",
            "ownerid": "90266271",
            "parentid": 4,
            "parenttype": "company",
            "priority": "normal",
            "service": "TaskShare",
            "status": "inprogress",
            "title": "This is not a test environment "
        }

## Task attachment Collection [/tasks/{task_id}/attachments]
Attachments of a specific task
Attachment type: image, video, file

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    
+ Model (text/plain)

        For geo location, the `content` should be formated in JSON
        {
            "service":"LocationShare",
            "latitude":"121.123345",
            "longitude":"91.123693",
            "altitude":"23",
            "title":"ZhongShan Gong Yuan",
            "message":"Join me in the cinema!"
        }
    
### List all attachments [GET]
+ Response 200 (application/json)

        [
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
                "tid": 1
            },
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
                "tid": 2
            }
        ]

### Add an attachment [POST]
+ Request (application/json)

        [
            {
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028
            },
            {
                "content": "url_or_id",
                "attachmenttype": "video",
                "filename": "bigView",
                "preview": "base64",
                "size": 2048
            }
        ]

+ Response 200 (application/json)

        [
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
                "tid": 2
            },
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "video",
                "filename": "bigView",
                "preview": "base64",
                "size": 2048,
                "tid": 2
            }
        ]


## Task attachment collection for delete [/tasks/{task_id}/attachments?id[]={attachment_id}]
Attachments of a specific task
Attachment type: image, video, file

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    + attachment_id (required) ... Numeric `id`s of the attachments, only needed when deleting multiple attachments in a batch mode; e.g.: `id[]=3&id[]=4` operation on attachment with `id` 3 and 4

### Delete multiple attachments [DELETE]

+ Response 204

## Attachment [/tasks/{task_id}/attachments/{attachment_id}]
A single attachment

+ Parameters
    + task_id (required) ... Numeric `id` of the task which the attachment belongs to
    + attachment_id (required) ... Numeric `id` of the attachment to perform action with
    
### Delete an attachment [DELETE]
+ Response 204


## Comment Collection [/tasks/{task_id}/comments]
Comments of a specific task

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    
### List all comments [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "tid": 1,
                "authorid": "65555287",
                "payload": "{\"service\":\"MessageShare\",\"message\":\"comment1\"}",
                "creationdate": "14752698"
            },
            {
                "id": 2,
                "tid": 2,
                "authorid": "65555287",
                "payload": "{\"service\":\"MessageShare\",\"message\":\"comment2\"}",
                "creationdate": "14752698"
            }
        ]

### Create a comment [POST]
+ Request (application/json)

        {
            "payload": "{\"service\":\"MessageShare\",\"message\":\"comment2\"}"
        }

+ Response 201 (application/json)

        {  
            "id":15,
            "creationdate": "14752698"
        }

## Comment [/tasks/{task_id}/comments/{comment_id}]
A single comment object with all its details

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    + comment_id (required) ... Numeric `id` of the comment to perform action with
    
### Delete a comment [DELETE]
Comment author could delete his/her own comment

+ Response 204

## Observer Collection [/tasks/{task_id}/observers]
Observers of a specific task

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    
### List all observers [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "userid": "65555287",
                "tid": 2
            },
            {
                "id": 2,
                "userid": "14845852",
                "tid": 2
            }
        ]

### Add an observer [POST]
+ Request (application/json)

        [
            {
                "userid": "65552"
            },
            {
                "userid": "65553"
            }
        ]


+ Response 200 (application/json)

        { "ids": [1001, 1002] }
        
## Observer collection for delete [/tasks/{task_id}/observers?id[]={observer_id}]
Observers of a specific task

+ Parameters
    + task_id (required) ... Numeric `id` of the task to perform action with
    + observer_id (required) ... Numeric `id`s of the observers, only needed when deleting multiple observers in a batch mode; e.g.: `id[]=3&id[]=4` operation on observers with `id` 3 and 4

### Delete multiple observers [DELETE]

+ Response 204

## Observer [/tasks/{task_id}/observers/{observer_id}]
A single observer

+ Parameters 
    + task_id (required) ... Numeric `id` of the task which observer belongs to
    + observer_id (required) ... Numeric `id` of the observer to perform action with
    
### Delete an observer [DELETE]
+ Response 204

# Group Approvals
Approval related resources of  **Approval API**

##  Approval Collection [/approvals]
### List all approvals [GET]
Approval status: `new`, `rejected`, `approved`, `cancelled`

+ Parameters
    + limit (number, optional)... `limit={limit}`, the maximum number of results to be returned; a number between `1` and `99`, default to `20`
        + Example `limit=66`
    + last (number, optional)... `last={last}`, `id` of the approval to start after, only return approvals whose `id` is larger than `last`
        + Example `limit=88`
    + companies (array, optional)... `companies[]={company_id}`, array of companies to filter approvals by companies; each parameter should be the `id` of the company
        + Example `companies[]=66`&`companies[]=88`
    + roles (array, optional)... `roles[]={role}`, array of roles to filter approvals by roles; each parameter should be the `value` of my role
        + Example `roles[]=owner`&`roles[]=assignee`
    + statuses (array, optional)... `statuses[]={status}`, array of statuses to filter approvals by statuses; each parameter should be the `value` of the approval's status
        + Example `statuses[]=new`&`statuses[]=approved`

+ Response 200 (application/json)

        [
            {
                "assigneeid": "90266271",
                "comments_count": 1,
                "creationdate": "1413524405876",
                "description": "lets have fun with this tools",
                "duedate": "1413524304288",
                "has_attachment": true,
                "id": 22,
                "modificationdate": "1413524425133",
                "ownerid": "90266271",
                "parentid": 1,
                "parenttype": "company",
                "priority": "normal",
                "service": "ApprovalShare",
                "status": "new",
                "title": "This is not a test environment "
            },
            {
                "assigneeid": "90266271",
                "comments_count": 1,
                "creationdate": "1413524405876",
                "description": "lets have fun with this tools",
                "duedate": "1413524304288",
                "has_attachment": true,
                "id": 23,
                "modificationdate": "1413524425133",
                "ownerid": "90266271",
                "parentid": 2,
                "parenttype": "group",
                "priority": "normal",
                "service": "ApprovalShare",
                "status": "approved",
                "title": "This is not a test environment "
            }
        ]
        
### Create a approval [POST]
+ Request (application/json)

        {
            "title":"approval title",
            "description":"approval desc",
            "priority":"normal",
            "duedate":"1452698",
            "ownerid":"45409951",
            "assigneeid":"24628810",
            "parentid":2,
            "parenttype":"group or company",
            "observers":[
                {"userid":"45409951"},
                {"userid":"55414551"}
            ],
            "attachments":[
                 {
                    "content": "url_or_id",
                    "attachmenttype": "picture",
                    "filename": "bigView",
                    "preview": "base64",
                    "size": 1028
                },
                 {
                    "content": "url_or_id",
                    "attachmenttype": "picture",
                    "filename": "bigView",
                    "preview": "base64",
                    "size": 2048
                }
            ]
        }

+ Response 201 (application/json)

        { "id":3301 } 


## Approval [/approvals/{approval_id}]
A single approval object with all its details

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with
    
### Retrieve a approval [GET]
+ Response 200 (application/json)

        {
            "assigneeid": "90266271",
            "comments_count": 1,
            "creationdate": "1413524405876",
            "description": "lets have fun with this tools",
            "duedate": "1413524304288",
            "has_attachment": true,
            "id": 1,
            "modificationdate": "1413524425133",
            "ownerid": "90266271",
            "parentid": 4,
            "parenttype": "company",
            "priority": "normal",
            "service": "ApprovalShare",
            "status": "rejected",
            "title": "This is not a test environment "
        }

### Update a approval [PATCH]
+ Request (application/json)

        [
            {
                "op": "add",
                "path": "/status",
                "value": "approved"
            },
            {
                "op": "add",
                "path": "/title",
                "value": "hello php"
            }
        ]

+ Response 200 (application/json)

        {
            "assigneeid": "90266271",
            "comments_count": 1,
            "creationdate": "1413524405876",
            "description": "lets have fun with this tools",
            "duedate": "1413524304288",
            "has_attachment": true,
            "id": 1,
            "modificationdate": "1413524425133",
            "ownerid": "90266271",
            "parentid": 4,
            "parenttype": "company",
            "priority": "normal",
            "service": "ApprovalShare",
            "status": "approved",
            "title": "This is not a test environment "
        }

##Approval attachment Collection [/approvals/{approval_id}/attachments]
Attachments of a specific approval
Attachment type: image, video, file

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with

+ Model (text/plain)

        For geo location, the `content` should be formated in JSON
        {
            "service":"LocationShare",
            "latitude":"121.123345",
            "longitude":"91.123693",
            "altitude":"23",
            "title":"ZhongShan Gong Yuan",
            "message":"Join me in the cinema!"
        }
    
### List all attachments [GET]
+ Response 200 (application/json)

        [
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
                "aid": 1
            },
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
                "aid": 2
            }
        ]

### Add an attachment [POST]
+ Request (application/json)

        [
             {
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
            },
            {
                "content": "url_or_id",
                "attachmenttype": "video",
                "filename": "bigView",
                "preview": "base64",
                "size": 2048,
            }                
        ]

+ Response 200 (application/json)

        [
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "picture",
                "filename": "bigView",
                "preview": "base64",
                "size": 1028,
                "aid": 2
            },
            {
                "id":1
                "content": "url_or_id",
                "attachmenttype": "video",
                "filename": "bigView",
                "preview": "base64",
                "size": 2048,
                "aid": 2
            }
        ]
        
        
##Approval attachment collection for delete [/approvals/{approval_id}/attachments?id[]={attachment_id}]
Attachments of a specific approval
Attachment type: image, video, file

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with
    + attachment_id (required) ... Numeric `id`s of the attachments, only needed when deleting multiple attachments in a batch mode; e.g.: `id[]=3&id[]=4` operation on attachment with `id` 3 and 4
    
###Delete multiple attachments [DELETE]

+ Response 204

## Attachment [/approvals/{approval_id}/attachments/{attachment_id}]
A single attachment

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval which the attachment belongs to
    + attachment_id (required) ... Numeric `id` of the attachment to perform action with
    
### Delete an attachment [DELETE]
+ Response 204


## Comment Collection [/approvals/{approval_id}/comments]
Comments of a specific approval

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with
    
### List all comments [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "aid": 1,
                "authorid": "65555287",
                "payload": "{\"service\":\"MessageShare\",\"message\":\"comment1\"}",
                "creationdate": "14752698"
            },
            {
                "id": 2,
                "aid": 2,
                "authorid": "65555287",
                "payload": "{\"service\":\"MessageShare\",\"message\":\"comment2\"}",
                "creationdate": "14752698"
            }
        ]

### Create a comment [POST]
+ Request (application/json)

        {
            "payload": "{\"service\":\"MessageShare\",\"message\":\"comment2\"}"
        }

+ Response 201 (application/json)

        {  "id":15 }

## Comment [/approvals/{approval_id}/comments/{comment_id}]
A single comment object with all its details

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with
    + comment_id (required) ... Numeric `id` of the comment to perform action with
    
### Delete a comment [DELETE]
Comment author could delete his/her own comment

+ Response 204

## Observer Collection [/approvals/{approval_id}/observers]
Observers of a specific approval

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with
    
### List all observers [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "userid": "65555287",
                "aid": 2
            },
            {
                "id": 2,
                "userid": "14845852",
                "aid": 2
            }
        ]

### Add an observer [POST]
+ Request (application/json)

        [
            {
                "userid": "655526"
            },
            {
                "userid": "655527"
            }        
        ]


+ Response 200 (application/json)

        { "ids": [1001, 1002] }

## Observer collection for delete [/approvals/{approval_id}/observers?id[]={observer_id}]
Observers of a specific task

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval to perform action with
    + observer_id (required) ... Numeric `id`s of the observers, only needed when deleting multiple observers in a batch mode; e.g.: `id[]=3&id[]=4` operation on observers with `id` 3 and 4
    
### Delete multiple observers [DELETE]

+ Response 204

## Observer [/approvals/{approval_id}/observers/{observer_id}]
A single observer

+ Parameters 
    + approval_id (required) ... Numeric `id` of the approval which observer belongs to
    + observer_id (required) ... Numeric `id` of the observer to perform action with
    
### Delete an observer [DELETE]
+ Response 204

## History [/approvals/{approval_id}/history]
History of an approval's status change

+ Parameters
    + approval_id (required) ... Numeric `id` of the approval which the history belongs to

### Retrieve the history [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "aid": 22,
                "actorid": "65555287",
                "oldstatus": "new",
                "newstatus": "approved",
                "actiondate": "147852369"
            },
            {
                "id": 1,
                "aid": 22,
                "actorid": "14385852",
                "oldstatus": "approved",
                "newstatus": "new",
                "actiondate": "147852369"
            }
        ]


# Group Companies
Company related resources of **Company API**

## Company collection [/companies]
### List all companies user belongs to [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "name": "Company name",
                "address": "Company address",
                "website": "Company website",
                "phone": "Company phone",
                "fax": "Company fax",
                "description": "Company description",
                "creatorid": "454546",
                "creationdate": "1413524405876",
                "modificationdate": "1413524405876"
            },
            {
                "id": 2,
                "name": "Company name",
                "address": "Company address",
                "website": "Company website",
                "phone": "Company phone",
                "fax": "Company fax",
                "description": "Company description",
                "creatorid": "478879",
                "creationdate": "1413524405876",
                "modificationdate": "1413524405876"
            }
        ]
        
### Create a company [POST]
+ Request (application/json)

        {
            "name": "name",
            "address": "address",
            "website": "website",
            "phone": "phone",
            "fax": "fax",
            "description": "description"
        }
        
+ Response 201 (application/json)

        { "id": 3389 }
        
## Company [/companies/{company_id}]
A single company and all its details

+ Parameters
    + company_id (required) ... `id` of the company whom these actions will be performed on
    
### Retrieve a company [GET]
+ Response 200 (application/json)

        {
            "id": 2,
            "name": "Company name",
            "address": "Company address",
            "website": "Company website",
            "phone": "Company phone",
            "fax": "Company fax",
            "description": "Company description",
            "creatorid": "313439",
            "creationdate": "1413524405876",
            "modificationdate": "1413524405876"
        }

### Update company detail [PATCH]
+ Request (application/json)

        [
            {
                "op": "add",
                "path": "/name",
                "value": "My new company name"
            },
            {
                "op": "add",
                "path": "/address",
                "value": "My new company address"
            }
        ]

+ Response 200 (application/json)

        {
            "id": 2,
            "name": "Company name",
            "address": "Company address",
            "website": "Company website",
            "phone": "Company phone",
            "fax": "Company fax",
            "description": "Company description",
            "creatorid": "313439",
            "creationdate": "1413524405876",
            "modificationdate": "1413524405876"
        }
        
###Delete a company [DELETE]
Only the company owner could call this API

+ Response 204

## Company member collection [/companies/{company_id}/members]
Members of a specific company

+ Parameters
    + company_id (required) ... `id` of the company to perform action with

### List all members [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1
                "companyid": 11,
                "userid": "3390",
                "name": "John Doe", 
                "email": "jd@gobeta.com.cn", 
                "phone": "(+86)13512345678"
            },
            {
                "id": 2
                "companyid": 11,
                "userid": "3391",
                "name": "Sam Doe", 
                "email": "sd@gobeta.com.cn", 
                "phone": "(+86)13098765432"
            }
        ]
        
### Add a new member [POST]

+ Request (application/json)
    
        [
            { 
                "userid": "45785656" 
            }, 
            {
                "userid": "45785657" 
            }
        ]
        
+ Response 200 (application/json)

        {  "ids": [1301, 1302] }
        

## Company member collection for delete [/companies/{company_id}/members?id[]={member_id}]
Members of a specific company, for delete only

+ Parameters
    + company_id (required) ... `id` of the company to perform action with
    + member_id (required) ... Numeric `id`s of the members, only needed when deleting multiple members in a batch mode; e.g.: `id[]=3&id[]=4` operation on members with `id` 3 and 4
    
### Delete multiple members [DELETE]
+ Response 204
        
## Member [/companies/{company_id}/members/{member_id}]
A single member of a specific company

+ Parameters
    + company_id (required) ... `id` of the company to perform action with
    + member_id (required) ... `id` of the member to perform action with
    
### Delete a member [DELETE]
+ Response 204

##Guest tag collection [/companies/{company_id}/guesttags]
Guest tags of a company

+ Parameters
    + company_id (required) ... `id` of the company to perform action with
    
###List guest tags [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "value": "Outsourcing"
            },
            {
                "id": 3,
                "value": "Supplier"
            },
            {
                "id": 8,
                "value": "Venture capital"
            }
        ]

###Create a guest tag [POST]
+ Request (application/json)

        {
            "value":"Auditing"
        }

+ Response 201 (application/json)

        {
            "id": 9
        }

##Guest tag [/companies/{company_id}/guesttags/{guesttag_id}]
A specific guest tag

+ Parameters
    + company_id (required) ... `id` of the company to perform action with
    + guesttag_id (required) ... `id` of the guest tag to perform action with
    
###Retrieve guest tag [GET]
+ Response 200 (application/json)

        {
            "id": 8,
            "value": "Venture capital"
        }


##Invitation collection [/companies/{company_id}/invitations]
All invitations sent by the company

+ Parameters
    + company_id (number, required) ... `id` of the company to perform action with
    + role (string, optional) ... `role` of the invited user, the possible values: 'user', 'guest'
        + Example `role=user`
    
###List invitations [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "userid": "32747282",
                "name": "John Doe",
                "email": "text@example.com",
                "countrycode": "86",
                "phone": "18888888888",
                "companyid": 1245646,        
                "role": "guest",
                "tagid": 3,
                "status": "pending",
                "invitedby": "66666666",
                "creationdate": "1410774186122",
                "modificationdate": "1410774186122",
                "exist_in_company": true
            },
            {
                "id": 8,
                "userid": "83737172",
                "name": "Sam Doe",
                "email": "test@example.com.cn",
                "countrycode": "86",
                "phone": "18888888889",
                "companyid": 1245646,
                "role": "user",
                "tagid": 0,
                "status": "pending",
                "invitedby": "88888888",
                "creationdate": "1410774186122",
                "modificationdate": "1410774186122",
                "exist_in_company": false
            }
        ]


###Create batch invitation [POST]
+ Request (application/json)

        [
            {
                "userid": "126323131",
                "name": "John Doe",
                "countrycode": "86",
                "phone": "18666655522",
                "companyid": 123,
                "role": "guest",
                "tagid": 2
            },
            {
                "name": "Matt Doe",
                "email": "test@example.com",
                "companyid": 18,
                "role": "user"
            }
        ]
        
        
        Required fields:
        - companyid
        - role
        - either email or phone
        
+ Response 200 (application/json)

        {
            "success": [
                {
                    "name": "Test Ten",
                    "email": "test2@plop.com"
                },
                {
                    "name": "Test Eleven",
                    "phone": "(+86)13987654321"
                }
            ],
            "fail": [
                {
                    "name": "Test One",
                    "email": "12345plop.com"
                },
                {
                    "name": "Test Five",
                    "phone": "(+86)1398"
                }
            ]
        }


##Invitations [/companies/{company_id}/invitations?id[]={invitation_id}]
Invitation collection, for batch removal

+ Parameters
    + invitation_id (required) ... `id` of the invitation
    
### Delete multiple invitations [DELETE]
+ Response 204


# Group Groups
Group related resource of **Group API**

## Group collection [/groups]
### List groups [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "name": "Group name",
                "description": "Group description",
                "companyid": 2,
                "joinable": true,
                "searchable": false,
                "creatorid": "45346123",
                "creationdate": "1413524405876",
                "modificationdate": "1413524405876"
            },
            {
                "id": 2,
                "name": "Group name",
                "description": "Group description",
                "companyid": 22,
                "joinable": false,
                "searchable": true,
                "creatorid": "45454545",
                "creationdate": "1413524405876",
                "modificationdate": "1413524405876"
            }
        ]

### Create a group [POST]
+ Request (application/json)

        {
            "name": "Group name",
            "description": "Group description",
            "companyid": 22,
            "joinable": true,
            "searchable": false,
            "members" : [
                { "userid": "78989898989" },
                { "userid": "43913943433" }
            ]
        }

+ Response 201 (application/json)

        { "id":  9088 }

## Group [/groups/{group_id}]
A single group and all its resources

+ Parameters 
    + group_id (required) ... `id` of the group to perform action with

### Retrieve a group [GET]
+ Response 200 (application/json)

        {
            "id": 2,
            "name": "Group name",
            "description": "Group description",
            "companyid": 22,
            "joinable": true,
            "searchable": true,
            "creatorid": "154456",
            "creationdate": "1413524405876",
            "modificationdate": "1413524405876"
        }

### Update group info [PATCH]
To change group owner, call this API with new value at path `/creatorid`

+ Request (application/json)

        [
            {
                "op": "add",
                "path": "/name",
                "value": "My new group name"
            },
            {
                "op": "add",
                "path": "/description",
                "value": "My new group description"
            }
        ]


+ Response 200 (application/json)

        {
            "id": 2,
            "name": "This is group name",
            "description": "This is group description",
            "companyid": 22,
            "joinable": true,
            "searchable": true,
            "creatorid": "154456",
            "creationdate": "1413524405876",
            "modificationdate": "1413524405876"
        }

###Delete group [DELETE]
Only group owner could call this API

+ Response 204

## Group member collection [/groups/{group_id}/members]
Member of a specific group

+ Parameters
    + group_id (required) ... `id` of the group to perform action with
    
### List all members [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1
                "groupid": 11,
                "userid": "3901388843"
            },
            {
                "id": 2
                "groupid": 11,
                "userid": "1394039043"
            }
        ]

### Add a new member [POST]
+ Request (application/json)

        [
            { 
                "userid": "43913943433" 
            },
            { 
                "userid": "43913943434" 
            }        
        ]

+ Response 200 (application/json)

        { 
            "ids": [ 9039333, 9039334]
        }

## Group member collection for delete [/groups/{groups_id}/members?id[]={member_id}]
Members of a specific groups, for delete only

+ Parameters
    + groups_id (required) ... `id` of the groups to perform action with
    + member_id (required) ... Numeric `id`s of the members, only needed when deleting multiple members in a batch mode; e.g.: `id[]=3&id[]=4` operation on members with `id` 3 and 4
    
### Delete multiple members [DELETE]
+ Response 204

## Member [/groups/{group_id}/members/{member_id}]
A single member of a group

+ Parameters
    + group_id (required) ... `id` of the group to perform action with
    + member_id (required) ... `id` of the member to perform action with
    
### Delete a member [DELETE]
Group owner could delete everyone but himself; group member could only delete himself, ak leaving the group

+ Response 204

# Group VCards
VCard related resources of **VCard API**

##VCard collection [/vcards]

### Create a VCard [POST]
+ Request (application/json)

        {
            "userid": "454655623",
            "gender" : "male",
            "companyid": 3,
            "name": "My name",
            "email": "test@example.com",
            "phone": "(+86)18616888888",
            "aboutme": "I want my what's up back! - About me",
            "hobbies": "I want my what's up back! - My hobbies",
            "skills": "I want my what's up back! - My skills",
            "location": "{\"language\": \"zh-cn\", \"country\": {\"code\":\"USA\",\"name\":\"美国\"}, \"province\": {\"code\":\"CA\",\"name\":\"加利福利亚\"}, \"city\": {\"code\":\"LAX\",\"name\":\"洛杉矶\"}}"
        }
        
+ Response 201 (application/json)

        {  
            "id": 13233 
        }

##VCard REST [/vcards/{vcard_id}]
A single VCard

+ Parameters
    + vcard_id (required) ... `id` of the vcard to perform action with

### Retrieve a VCard [GET]
+ Response 200 (application/json)

        {
            "id": 1,
            "userid": "7856562",
            "gender", "female",
            "companyid": 3,
            "name": "My name",
            "email": "a@b.c",
            "phone": "(+86)18616888888",
            "aboutme": "I want my what's up back! - About me",
            "hobbies": "I want my what's up back! - My hobbies",
            "skills": "I want my what's up back! - My skills",
            "location": "{\"language\": \"zh-cn\", \"country\": {\"code\":\"USA\",\"name\":\"美国\"}, \"province\": {\"code\":\"CA\",\"name\":\"加利福利亚\"}, \"city\": {\"code\":\"LAX\",\"name\":\"洛杉矶\"}}"
        }
        
### Update a VCard [PATCH]
+ Request (application/json)

        [
            {
                "op": "add",
                "path": "/name",
                "value": "My new VCard name"
            },
            {
                "op": "add",
                "path": "/gender",
                "value": "other"
            }
        ]

+ Response 200 (application/json)

        {
            "id": 1,
            "userid": "7856562",
            "gender":"other",
            "companyid": 3,
            "name": "My name",
            "email": "test@example.com",
            "phone": "(+86)18616888888",
            "aboutme": "I want my what's up back! - About me",
            "hobbies": "I want my what's up back! - My hobbies",
            "skills": "I want my what's up back! - My skills",
            "location": "{\"language\": \"zh-cn\", \"country\": {\"code\":\"USA\",\"name\":\"美国\"}, \"province\": {\"code\":\"CA\",\"name\":\"加利福利亚\"}, \"city\": {\"code\":\"LAX\",\"name\":\"洛杉矶\"}}"
        }

##VCard Alternative [/vcards?userid={user_id}&companyid={company_id}]
An alternative way to access a single VCard

+ Parameters
    + user_id (required) ... `id` of the user whose VCard to be retrieved
    + company_id (required) ... `id` of the company under which the VCard is created

### Retrieve a VCard [GET]    
+ Response 200 (application/json)

        {
            "id": 1,
            "userid": "27855566",
            "gender":"male",
            "companyid": 3,
            "name": "My name",
            "email": "a@b.c",
            "phone": "(+86)18616888888",
            "aboutme": "I want my what's up back! - About me",
            "hobbies": "I want my what's up back! - My hobbies",
            "skills": "I want my what's up back! - My skills",
            "location": "{\"language\": \"zh-cn\", \"country\": {\"code\":\"USA\",\"name\":\"美国\"}, \"province\": {\"code\":\"CA\",\"name\":\"加利福利亚\"}, \"city\": {\"code\":\"LAX\",\"name\":\"洛杉矶\"}}"
        }

#Group Favorites
Favorite related resource of **Favorite API**

##Favorite collection [/favorites]

### List favorites [GET]
+ Response 200 (application/json)

        [
                {
                    "id" : 1091,
                    "companymemberid":93,
                    "jid": "90266271@im01.ezlinx.cn",
                    "userid":"2930434",
                    "companyid": 3,
                    "name":"John Doe", 
                    "email": "jd@gobeta.com.cn", 
                    "phone": "(+86)13512345678"
                },
                {
                    "id": 1092,
                    "companymemberid":94
                    "jid": "90266272@im01.ezlinx.cn",
                    "userid":"2930434",
                    "companyid": 3,
                    "name":"Sam Doe", 
                    "email": "sd@gobeta.com.cn", 
                    "phone": "(+86)13098765432
                }
        ]

### Add a favorite [POST]
`companymemberid` is the resource id retrieved from directory

+ Request (application/json)

        {
            "companymemberid":1099
        }

+ Response 201 (application/json)

        {
            "id" : 9099
        }

##Favorite [/favorites/{favorite_id}]
A specific favorite

+ Parameters
    + favorite_id (required) ... `id` of the favorite to perform action with
    
###Remove a favorite [DELETE]
+ Response 204 

#Group Invitations
Invitation related resource of **Invitation API**

##Invitation collection [/invitations]

+ Model (application/json)

        {
            "id": <integer>,
            "userid": <string for now, user's id, optional>,
            "name": <string, invitee's name>,
            "email": <string>,
            "countrycode": <string>,
            "phone": <string>,
            "companyid": <integer>,
            "companyname": <string>,
            "role": <string, choose from "user"/"guest">,
            "tagid": <integer, optional>,
            "status": <string, choose from "pending"/"accepted"/"rejected">,
            "invitedby": <string for now>,
            "creationdate": <string>,
            "modificationdate": <string>,
            "exist_in_company": <boolean>
        }

### List invitations [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "userid": "32747282",
                "name": "John Doe",
                "email": "test@example.com",
                "countrycode": "86",
                "phone": "18888888888",
                "companyid": 1245646,
                "companyname": "EZLinx LLC",
                 "role": "guest",
                "tagid": 3,
                "status": "pending",
                "invitedby": "12452356",
                "creationdate": "1410774186122",
                "modificationdate": "1410774186122",
                "exist_in_company": true
            },
            {
                "id": 8,
                "userid": "",
                "name": "Sam Doe",
                "email": "test@example.com",
                "countrycode": "86,
                "phone": "13800138000",
                "companyid": 78454654,
                "companyname": "BST",
                "role": "user",
                "tagid": 0,
                "status": "pending",
                "invitedby": "88888888",
                "creationdate": "1410774186122",
                "modificationdate": "1410774186122",
                "exist_in_company": false
            }
        ]
     
###Create invitation [POST]
+ Request (application/json)

        {
            "userid": "126323131",
            "name": "John Doe",
            "email": "test@example.com",
            "countrycode": "86",
            "phone": "18666655522",
            "companyid": 123,
            "role": "guest",
            "tagid": 2
        }
        
        
        Required fields:
        - companyid
        - role
        - either email or phone
        
+ Response 201 (application/json)

        { 
            "id": 1001
        }


##Invitation [/invitations/{invitation_id}]
A specific invitation

+ Parameters
    + invitation_id (required) ... `id` of the invitation to perform action with
    
###Retrieve an invitation [GET]
+ Response 200 (application/json)

        {
            "id": 1,
            "userid": "32747282",
            "name": "Sam Doe",
            "email": "test@example.com",
            "countrycode": "86",
            "phone": "18888888888",   
            "companyid": 39943,
            "companyname": "EZLinx LLC",
            "role": "guest",
            "tagid": 3,
            "status": "pending",
            "invitedby": "90384343",
            "creationdate": "1410774186122",
            "modificationdate": "1410774186122",
            "exist_in_company": true
        }

###Update an invitation [PATCH]
+ Request (application/json)

        [
            {
                "op": "add",
                "path": "/status",
                "value": "accepted"
            }
        ]

+ Response 200 (application/json)

        {
            "status":"accepted",
            "guestservice": {
                "id": 9988
            }
        }

###Delete an invitation [DELETE]
+ Response 204

#Group Guest Services
Guest service related resource of **Guest service API**

##Guest service collection [/guestservices]

###List guest services [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "name": "BST Customer Service",
                "companyid": 1234567890,
                "tagid": 1,
                "tagvalue": "Customer Service",
                "guestuserid": "4398943",
                "guestname": "John Doe",
                "organizer": "849384",  
                "inactive": false,
                "groupchatid": 11,
                "groupchatjid": "1412066646254@conference.im01.ezlinx.cn",
                "creationdate": "1414036929390",
                "modificationdate": "1414036929390"
            },
            {
                "id": 3,
                "name": "EZLinx Suppliers",
                "companyid": 1234567890,
                "tagid": 22,
                "tagvalue": "Supplier",
                "guestuserid": "998383",
                "guestname": "Sam Doe",
                "organizer": "899988",
                "inactive": false,
                "groupchatid": "11",
                "groupchatjid": "1412066646254@conference.im01.ezlinx.cn",
                "creationdate": "1414036929390",
                "modificationdate": "1414036929390"
            }
        ]


##Guest service [/guestservices/{guestservice_id}]
A specific guest service

+ Parameters
    + guestservice_id (required) ... `id` of the guest service to perform action with
    
###Retrieve a guest service [GET]
+ Response 200 (application/json)

        {
            "id": 1,
            "name": "BST Customer Service",
            "companyid": 1234567890,
            "tagid": 1,
            "tagvalue": "Customer Service",
            "guestuserid": "4398943",
            "guestname": "John Doe",
            "organizer": "849384",  
            "inactive": false,
            "groupchatid": 11,
            "groupchatjid": "1412066646254@conference.im01.ezlinx.cn",
            "creationdate": "1414036929390",
            "modificationdate": "1414036929390"
        }
        
###Delete a guest service [DELETE]
Only owner could perform this action

+ Response 204

##Guest service member collection [/guestservices/{guestservice_id}/members]
Members of a specific guest service

+ Parameters
    + guestservice_id (required) ... `id` of the guest service to perform action with
    
+ Model (text/plain)

        {
            "id": <integer>,
            "guestserviceid": <integer>,
            "userid": <string for now>,
            "name":<string>,
            "role": <string, choose from "organizer"/"member"/"guest">
        },
    
###List members [GET]
+ Response 200 (application/json)

        [
            {
                "id": 1,
                "guestserviceid": 1,
                "userid": "1456544",
                "name":"John Doe",
                "role": "organizer"
            },
            {
                "id": 2,
                "guestserviceid": "1",
                "userid": "1244555",
                "name":"Sam Doe",
                "role": "guest"
            },
            {
                "id": 3,
                "guestserviceid": "1",
                "userid": "1247897",
                "name":"Jack Doe",
                "role": "member"
            }
        ]
        

###Add member [POST]
+ Request (application/json)

        [
            {
                "userid": "12453256"
            },
            {
                "userid": "12453257"
            }                
        ]


+ Response 200 (application/json)

        {
            "ids": [1001, 1002]
        }

## Guest service member collection for delete [/guestservices/{guestservice_id}/members?id[]={member_id}]
Members of a specific guest service, for delete only

+ Parameters
    + guestservice_id (required) ... `id` of the guest service to perform action with
    + member_id (required) ... Numeric `id`s of the members, only needed when deleting multiple members in a batch mode; e.g.: `id[]=3&id[]=4` operation on members with `id` 3 and 4
    
### Delete multiple members [DELETE]
+ Response 204

##Member [/guestservices/{guestservice_id}/members/{member_id}]
A specific member of the given guest service

+ Parameters
    + guestservice_id (required) ... `id` of the guest service to perform action with
    + member_id (required) ... `id` of the member to perform action with
    
###Delete a member [DELETE]
Member could delete only himself, ak leaving the guest service; owner could delete everyone but himself;

+ Response 204


#Group Directories
Directory related resource of **Directory API**

## Directory collection [/directories]
###List directories [GET]

+ Response 200 (application/json)

        [
            {
                "id": 1,
                "companyid": 48948943,
                "jid": "90266271@im01.ezlinx.cn", 
                "name": "Michael Ngo", 
                "userid": "90266271", 
                "email": "michael.ngo@easylinks.com.cn", 
                "phone": "(+86)18688886666"
            }, 
            {
                "id": 2,
                "companyid": 48398943,
                "jid": "92334238@im01.ezlinx.cn", 
                "name": "Thomas Barthelemy", 
                "userid": "92334238", 
                "email": "tb@gobeta.com.cn", 
                "phone": "(+86)13756781234"
            }, 
            {
                "id": 3,
                "companyid": 898493434,
                "jid": "48398943@im01.ezlinx.cn", 
                "name": "Administrator", 
                "userid": "48398943"
            }
        ]


#Group Groupchats
Groupchat related resource of **Groupchat API**

##Groupchat collection [/groupchats]
Only groupchats

###List groupchats [GET]
+ Response 200 (application/json)

        [
            {
                "id": 2,
                "jid": "1410848987105@service.domain.extension",
                "name": "This is group name",
                "topic": "This is group topic",
                "owner": "4898943@im.ezlinx.cn",
                "parentid": 150270546913,
                "parenttype": "company"
            },
            {
                "id": 8,
                "jid": "1410956888954@service.domain.extension",
                "name": "This is group name",
                "topic": "This is group topic",
                "owner": "4898943@im.ezlinx.cn",
                "parentid": 150270546913,
                "parenttype": "company"
            }
        ]

##Groupchat [/groupchats/{groupchat_id}]
A specific groupchat

+ Parameters
    + groupchat_id (required) ... `id` of the groupchat to perform action with
    
###Retrieve a groupchat [GET]
+ Response 200 (application/json)

        {
            "id": 8,
            "jid": "1410956888954@service.domain.extension",
            "name": "This is group name",
            "topic": "This is group topic",
            "owner": "4898943@im.ezlinx.cn",
            "parentid": 150270546913,
            "parenttype": "company"
        }

###Delete a group chat [DELETE]
Only the group chat owner could call this API

+ Response 204

##Groupchat member collection [/groupchats/{groupchat_id}/members]
Member of a specific groupchat

+ Parameters
    + groupchat_id (required) ... `id` of the groupchat to perform action with

+ Model (text/plain)
    
        {
            "groupchatid": <an integer>, 
            "jid": <a string>, 
            "role": <a string, can be "owner" or "member">, 
            "name": <a string>,
            "email": <a string>,
            "phone": <a string>
        }

###List members [GET]
+ Response 200 (application/json)

        [
            {
                "groupchatid": 1, 
                "jid": "30629449@im01.ezlinx.cn", 
                "role": "member", 
                "name": "John Doe",
                "email": "sd@gobeta.com.cn", 
                "phone": "(+86)13098765432"
            }, 
            {
                "groupchatid": 1, 
                "jid": "40882969@im01.ezlinx.cn", 
                "role": "owner", 
                "name": "Sam Doe",
                "email": "jd@gobeta.com.cn", 
                "phone": "(+86)13212345678"
            }
        ]
