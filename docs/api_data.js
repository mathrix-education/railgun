define({ "api": [
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/users/forgot",
    "title": "Forgot password (WIP)",
    "group": "Authentication",
    "name": "AuthenticationForgot",
    "description": "<p>Ask for a password reset email</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>The user with given <code>email</code> was not found</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>The email field is missing</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Unable to save the user</p>"
          }
        ],
        "Error 502": [
          {
            "group": "Error 502",
            "optional": false,
            "field": "email_not_sent",
            "description": "<p>Unable to sent reset email</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/AuthController.php",
    "groupTitle": "Authentication"
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/users/login",
    "title": "Login",
    "group": "Authentication",
    "name": "AuthenticationLogin",
    "description": "<p>Login a user</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "client_id",
            "description": "<p>The oAuth client id; see <code>/database/raws/oauth_clients.json</code></p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "client_secret",
            "description": "<p>The oAuth client secret <code>/database/raws/oauth_clients.json</code></p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>The user <b>email</b></p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": "<p>The user password</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "allowedValues": [
              "password"
            ],
            "optional": false,
            "field": "grant_type",
            "description": ""
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "invalid_credentials",
            "description": "<p>User credentials were invalid</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>The user with given <code>email</code> was not found</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>At least one field is missing</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/AuthController.php",
    "groupTitle": "Authentication",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "allowedValues": [
              "bearer"
            ],
            "optional": false,
            "field": "token_type",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "expires_in",
            "description": "<p>Time before token expiration in seconds</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "access_token",
            "description": "<p>The user access token; should be stored; valid until expiration</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "refresh_token",
            "description": "<p>The user refresh token; should be stored; see <a href=\"#api-Authentication-AuthenticationRefresh\">/users/refresh</a></p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/users/refresh",
    "title": "Refresh",
    "group": "Authentication",
    "name": "AuthenticationRefresh",
    "description": "<p>Refresh the access_token</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "allowedValues": [
              "refresh_token"
            ],
            "optional": false,
            "field": "grant_type",
            "description": ""
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "client_id",
            "description": "<p>The oAuth client id; see <code>/database/raws/oauth_clients.json</code></p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "client_secret",
            "description": "<p>The oAuth client secret <code>/database/raws/oauth_clients.json</code></p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "refresh_token",
            "description": "<p>The refresh token stored on <a href=\"#api-Authentication-AuthenticationLogin\">/users/login</a></p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "invalid_request",
            "description": "<p>Invalid refresh token</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>At least one field is missing</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/AuthController.php",
    "groupTitle": "Authentication",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "string",
            "allowedValues": [
              "bearer"
            ],
            "optional": false,
            "field": "token_type",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "expires_in",
            "description": "<p>Time before token expiration in seconds</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "access_token",
            "description": "<p>The user access token; should be stored; valid until expiration</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "refresh_token",
            "description": "<p>The user refresh token; should be stored; see <a href=\"#api-Authentication-AuthenticationRefresh\">/users/refresh</a></p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/users/reset/{token}",
    "title": "Reset password (WIP)",
    "group": "Authentication",
    "name": "AuthenticationReset",
    "description": "<p>Ask for a password reset email</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "token",
            "description": "<p>The reset token</p>"
          }
        ],
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": "<p>The new password</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>The user with given <code>token</code> was not found</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>The password field is missing</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>The validated password save failed</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/AuthController.php",
    "groupTitle": "Authentication"
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/chapters",
    "title": "Add",
    "group": "Chapters",
    "name": "ChapterAdd",
    "description": "<p>Add a chapter</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "filename": "app/Http/Controllers/ChapterController.php",
    "groupTitle": "Chapters",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The chapter slug</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The chapter class id</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "discipline_id",
            "description": "<p>The chapter discipline id</p>"
          }
        ],
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The chapter class id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "discipline_id",
            "description": "<p>The chapter discipline id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The chapter slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "delete",
    "url": "/chapters/{id}",
    "title": "Delete (NYI)",
    "group": "Chapters",
    "name": "ChapterDelete",
    "description": "<p>Delete a chapter</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The chapter id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ChapterController.php",
    "groupTitle": "Chapters",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "patch",
    "url": "/chapters/{id}",
    "title": "Edit",
    "group": "Chapters",
    "name": "ChapterEdit",
    "description": "<p>Edit a chapter</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The chapter class id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "discipline_id",
            "description": "<p>The chapter discipline id</p>"
          }
        ],
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The chapter slug</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The chapter class id</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "discipline_id",
            "description": "<p>The chapter discipline id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ChapterController.php",
    "groupTitle": "Chapters",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The chapter slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/chapters/{id}",
    "title": "Get",
    "group": "Chapters",
    "name": "ChapterGet",
    "description": "<p>Get a chapter by id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The chapter class id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "discipline_id",
            "description": "<p>The chapter discipline id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ChapterController.php",
    "groupTitle": "Chapters",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The chapter slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/chapters",
    "title": "Index",
    "group": "Chapters",
    "name": "ChaptersIndex",
    "description": "<p>Retrieve chapters</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "filename": "app/Http/Controllers/ChapterController.php",
    "groupTitle": "Chapters",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Chapter[]",
            "optional": false,
            "field": "chapters",
            "description": "<p>Array of chapters</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "chapters.id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "chapters.name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "chapters.slug",
            "description": "<p>The chapter slug</p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "chapters.class_id",
            "description": "<p>The chapter class id</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "chapters.discipline_id",
            "description": "<p>The chapter discipline id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/classes",
    "title": "Add",
    "group": "Classes",
    "name": "ClassAdd",
    "description": "<p>Add class</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "filename": "app/Http/Controllers/ClassController.php",
    "groupTitle": "Classes",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "name_short",
            "defaultValue": "name",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug_short",
            "defaultValue": "slug(name_short)",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "country_id",
            "description": "<p>The class country id</p>"
          },
          {
            "group": "Data",
            "type": "int[]",
            "optional": false,
            "field": "disciplines_ids",
            "description": "<p>The class discipline ids</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "country_id",
            "description": "<p>The class country id</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "delete",
    "url": "/classes/{id}",
    "title": "Delete (NYI)",
    "group": "Classes",
    "name": "ClassDelete",
    "description": "<p>Delete a class</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ClassController.php",
    "groupTitle": "Classes",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "patch",
    "url": "/classes/{id}",
    "title": "Edit",
    "group": "Classes",
    "name": "ClassEdit",
    "description": "<p>Edit a class</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          }
        ],
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "name_short",
            "defaultValue": "name",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug_short",
            "defaultValue": "slug(name_short)",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "country_id",
            "description": "<p>The class country id</p>"
          },
          {
            "group": "Data",
            "type": "int[]",
            "optional": false,
            "field": "disciplines_ids",
            "description": "<p>The class discipline ids</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ClassController.php",
    "groupTitle": "Classes",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "country_id",
            "description": "<p>The class country id</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/classes/{id}",
    "title": "Get",
    "group": "Classes",
    "name": "ClassGet",
    "description": "<p>Get a class by id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ClassController.php",
    "groupTitle": "Classes",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "country_id",
            "description": "<p>The class country id</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/classes",
    "title": "Index",
    "group": "Classes",
    "name": "ClassesIndex",
    "description": "<p>Retrieve classes</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "filename": "app/Http/Controllers/ClassController.php",
    "groupTitle": "Classes",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Class[]",
            "optional": false,
            "field": "classes",
            "description": "<p>Array of classes</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "classes.id",
            "description": "<p>The class id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.name_short",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.slug",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.slug_short",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.country_id",
            "description": "<p>The class country id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/content/countries",
    "title": "Countries",
    "group": "Content",
    "name": "ContentCountries",
    "description": "<p>Retrieve content data by class id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "filename": "app/Http/Controllers/ContentController.php",
    "groupTitle": "Content",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The country short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "Class[]",
            "optional": false,
            "field": "classes",
            "description": "<p>Array of classes</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "classes.id",
            "description": "<p>The class id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.name_short",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.slug",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.slug_short",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "classes.country_id",
            "description": "<p>The class country id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/content/modules/{activityId}",
    "title": "Modules",
    "group": "Content",
    "name": "ContentModules",
    "description": "<p>Retrieve modules data by activity id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The activity id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The activity id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The activity name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The activity slug</p>"
          },
          {
            "group": "Success 200",
            "type": "Module[]",
            "optional": false,
            "field": "modules",
            "description": "<p>The modules</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "modules.id",
            "description": "<p>The module name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "modules.name",
            "description": "<p>The module name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "modules.slug",
            "description": "<p>The module slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "allowedValues": [
              "course",
              "fill",
              "match",
              "mcq",
              "mcq-table",
              "test",
              "video"
            ],
            "optional": false,
            "field": "modules.type",
            "description": "<p>The module type</p>"
          },
          {
            "group": "Success 200",
            "type": "mixed",
            "optional": false,
            "field": "modules.content",
            "description": "<p>[course]    <code>string</code> The course content<br> [fill]      <code>null</code><br> [match]     <code>null</code><br> [mcq]       <code>null</code><br> [mcq-table] <code>null</code><br> [test]      <code>null</code><br> [video]     <code>string</code> The youtube video id (e.g. A7xHki1biKE)</p>"
          },
          {
            "group": "Success 200",
            "type": "mixed",
            "optional": false,
            "field": "modules.questions",
            "description": "<p>[course]    <code>null</code><br> [fill]      <code>null</code><br> [match]     <code>string[]</code> The static propositions<br> [mcq]       <code>string[]</code> The mcq questions<br> [mcq-table] <code>string[]</code> The mcq questions<br> [test]      <code>string[]</code> The test questions<br> [video]     <code>null</code><br></p>"
          },
          {
            "group": "Success 200",
            "type": "mixed",
            "optional": false,
            "field": "modules.choices",
            "description": "<p>[course]    <code>null</code><br> [fill]      <code>string[]</code>   The gap choices<br> [match]     <code>string[]</code>   The draggable propositions<br> [mcq]       <code>string[][]</code> The mcq choices (first dimension for the question number, second dimension for the question choices)<br> [mcq-table] <code>string[]</code>   The common choices for all questions<br> [test]      <code>null</code><br> [video]     <code>null</code><br></p>"
          },
          {
            "group": "Success 200",
            "type": "mixed",
            "optional": false,
            "field": "modules.solutions",
            "description": "<p>[course]    <code>null</code><br> [fill]      <code>null</code><br> [match]     <code>string[]</code>   The solutions for each gap (only one per gap)<br> [mcq]       <code>string[][]</code> The solutions for each question (second dimension array represents the right answers)<br> [mcq-table] <code>string[][]</code> The solutions for each question (second dimension array represents the right answers)<br> [test]      <code>string[]</code>   The solutions for each question<br> [video]     <code>null</code><br></p>"
          },
          {
            "group": "Success 200",
            "type": "mixed",
            "optional": false,
            "field": "modules.corrections",
            "description": "<p>[course]    <code>null</code><br> [fill]      <code>null</code><br> [match]     <code>string[][]</code> The solutions for each question (second dimension array represents a step)<br> [mcq]       <code>string[][]</code> The solutions for each question (second dimension array represents a step)<br> [mcq-table] <code>string[][]</code> The solutions for each question (second dimension array represents a step)<br> [test]      <code>string[][]</code> The solutions for each question (second dimension array represents a step)<br> [video]     <code>null</code><br></p>"
          },
          {
            "group": "Success 200",
            "type": "mixed",
            "optional": false,
            "field": "modules.options",
            "description": "<p>The modules options</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "modules.options.duration",
            "description": "<p>[test] The test duration (in seconds)</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "modules.options.starts",
            "description": "<p>[video] The video starting time (in seconds)</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "modules.options.ends",
            "description": "<p>[video] The video ends time (in seconds)</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ContentController.php",
    "groupTitle": "Content"
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/content/data/{classId}",
    "title": "Program",
    "group": "Content",
    "name": "ContentProgram",
    "description": "<p>Retrieve content data by class id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The class id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The class name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The class short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The class slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The class short slug</p>"
          },
          {
            "group": "Success 200",
            "type": "Discipline[]",
            "optional": false,
            "field": "disciplines",
            "description": "<p>The disciplines</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.id",
            "description": "<p>The discipline id</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.slug",
            "description": "<p>The discipline slug</p>"
          },
          {
            "group": "Success 200",
            "type": "Chapter[]",
            "optional": false,
            "field": "disciplines.chapters",
            "description": "<p>The chapters</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.chapters.id",
            "description": "<p>The chapter id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.name",
            "description": "<p>The chapter name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.slug",
            "description": "<p>The chapter slug</p>"
          },
          {
            "group": "Success 200",
            "type": "Notion[]",
            "optional": false,
            "field": "disciplines.chapters.notions",
            "description": "<p>The notions</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.chapters.notions.id",
            "description": "<p>The notion id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.notions.name",
            "description": "<p>The notion name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.notions.slug",
            "description": "<p>The notion slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.notions.description",
            "description": "<p>The notion description</p>"
          },
          {
            "group": "Success 200",
            "type": "Activity[]",
            "optional": false,
            "field": "disciplines.chapters.notion.activities",
            "description": "<p>The activities</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.chapters.notions.activities.id",
            "description": "<p>The activity id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.notions.activities.name",
            "description": "<p>The activity name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.notions.activities.slug",
            "description": "<p>The activity slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.chapters.notions.activities.description",
            "description": "<p>The activity description</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Class with given id does not exist</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/ContentController.php",
    "groupTitle": "Content"
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/countries",
    "title": "Index",
    "group": "Countries",
    "name": "CountriesIndex",
    "description": "<p>Retrieve countries</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "filename": "app/Http/Controllers/CountryController.php",
    "groupTitle": "Countries",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Country[]",
            "optional": false,
            "field": "countries",
            "description": "<p>Array of countries</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "countries.id",
            "description": "<p>The country id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "countries.name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "countries.name_short",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "countries.slug",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "countries.slug_short",
            "description": "<p>The country short slug</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/countries",
    "title": "Add",
    "group": "Countries",
    "name": "CountryAdd",
    "description": "<p>Add a country</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "filename": "app/Http/Controllers/CountryController.php",
    "groupTitle": "Countries",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "name_short",
            "defaultValue": "name",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug_short",
            "defaultValue": "slug(name_short)",
            "description": "<p>The country short slug</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The country short slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "delete",
    "url": "/countries/{id}",
    "title": "Delete (NYI)",
    "group": "Countries",
    "name": "CountryDelete",
    "description": "<p>Delete a country</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CountryController.php",
    "groupTitle": "Countries",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "patch",
    "url": "/countries/{id}",
    "title": "Edit",
    "group": "Countries",
    "name": "CountryEdit",
    "description": "<p>Edit a country</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          }
        ],
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "name_short",
            "defaultValue": "name",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug_short",
            "defaultValue": "slug(name_short)",
            "description": "<p>The country short slug</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CountryController.php",
    "groupTitle": "Countries",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The country short slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/countries/{id}",
    "title": "Get",
    "group": "Countries",
    "name": "CountryGet",
    "description": "<p>Get a country by id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/CountryController.php",
    "groupTitle": "Countries",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The country id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The country name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name_short",
            "description": "<p>The country short name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The country slug</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug_short",
            "description": "<p>The country short slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/disciplines",
    "title": "Add",
    "group": "Disciplines",
    "name": "DisciplineAdd",
    "description": "<p>Add a discipline</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "filename": "app/Http/Controllers/DisciplineController.php",
    "groupTitle": "Disciplines",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The discipline slug</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The discipline id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The discipline slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "delete",
    "url": "/disciplines/{id}",
    "title": "Delete (NYI)",
    "group": "Disciplines",
    "name": "DisciplineDelete",
    "description": "<p>Delete a discipline</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The discipline id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/DisciplineController.php",
    "groupTitle": "Disciplines",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "patch",
    "url": "/disciplines/{id}",
    "title": "Edit",
    "group": "Disciplines",
    "name": "DisciplineEdit",
    "description": "<p>Edit a discipline</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The discipline id</p>"
          }
        ],
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "slug",
            "defaultValue": "slug(name)",
            "description": "<p>The discipline slug</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/DisciplineController.php",
    "groupTitle": "Disciplines",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The discipline id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The discipline slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>Save failed</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>data failed to pass validation</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/disciplines/{id}",
    "title": "Get",
    "group": "Disciplines",
    "name": "DisciplineGet",
    "description": "<p>Get a discipline by id</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The discipline id</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/DisciplineController.php",
    "groupTitle": "Disciplines",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The discipline id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "slug",
            "description": "<p>The discipline slug</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>Model matching given data does not exist</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/disciplines",
    "title": "Index",
    "group": "Disciplines",
    "name": "DisciplinesIndex",
    "description": "<p>Retrieve disciplines</p>",
    "permission": [
      {
        "name": "public"
      }
    ],
    "filename": "app/Http/Controllers/DisciplineController.php",
    "groupTitle": "Disciplines",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "Discipline[]",
            "optional": false,
            "field": "disciplines",
            "description": "<p>Array of disciplines</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "disciplines.id",
            "description": "<p>The discipline id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.name",
            "description": "<p>The discipline name</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "disciplines.slug",
            "description": "<p>The discipline slug</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "delete",
    "url": "/users/{id}",
    "title": "Delete (WIP)",
    "group": "Users",
    "name": "UserDelete",
    "description": "<p>Delete a user</p>",
    "permission": [
      {
        "name": "admin"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The user id</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>User does not have enough privileges</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UserController.php",
    "groupTitle": "Users",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "patch",
    "url": "/users/{id}",
    "title": "Edit",
    "group": "Users",
    "name": "UserEdit",
    "description": "<p>Edit a user</p>",
    "permission": [
      {
        "name": "registered for self get, at least admin for strangers"
      }
    ],
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "firstname",
            "defaultValue": "null",
            "description": "<p>The user firstname</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "lastname",
            "defaultValue": "null",
            "description": "<p>The user lastname</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": true,
            "field": "username",
            "defaultValue": "null",
            "description": "<p>The user username</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "allowedValues": [
              "registered",
              "premium",
              "moderator",
              "admin"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>The user type</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": "<p>The user password</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": true,
            "field": "class_id",
            "defaultValue": "null",
            "description": "<p>The class id; should be <code>null</code> for <code>moderator</code> and <code>admin</code></p>"
          },
          {
            "group": "Data",
            "type": "array",
            "optional": false,
            "field": "settings",
            "description": "<p>The user options</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": false,
            "field": "settings.daily_goal",
            "description": "<p>The user daily goal points</p>"
          },
          {
            "group": "Data",
            "type": "bool",
            "optional": false,
            "field": "settings.sound",
            "description": "<p>If the user has enabled sounds</p>"
          },
          {
            "group": "Data",
            "type": "bool",
            "optional": false,
            "field": "settings.email_newsletter",
            "description": "<p>If the user should receive the newsletter</p>"
          },
          {
            "group": "Data",
            "type": "bool",
            "optional": false,
            "field": "settings.email_comment",
            "description": "<p>If the user should receive an comment email</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>The user does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>The user with given <code>id</code> was not found</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UserController.php",
    "groupTitle": "Users",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The user id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>The user username</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email address</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "firstname",
            "description": "<p>The user firstname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "lastname",
            "description": "<p>The user lastname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "facebook_token",
            "description": "<p>The facebook token</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "points",
            "description": "<p>The user points</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "settings",
            "description": "<p>The user settings</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "settings.daily_goal",
            "description": "<p>The user daily goal points</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.sound",
            "description": "<p>If the user has enabled sounds</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_newsletter",
            "description": "<p>If the user should receive the newsletter</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_comment",
            "description": "<p>If the user should receive an comment email</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "updated_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The user class_id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/users/{id}",
    "title": "Get",
    "group": "Users",
    "name": "UserGet",
    "description": "<p>Get a user by id</p>",
    "permission": [
      {
        "name": "registered for self get, at least moderator for strangers"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": true,
            "field": "id",
            "description": "<p>The user id</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>The user does not have enough privileges</p>"
          }
        ],
        "Error 404": [
          {
            "group": "Error 404",
            "optional": false,
            "field": "model_not_found",
            "description": "<p>The user with given <code>id</code> was not found</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UserController.php",
    "groupTitle": "Users",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The user id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>The user username</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email address</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "firstname",
            "description": "<p>The user firstname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "lastname",
            "description": "<p>The user lastname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "facebook_token",
            "description": "<p>The facebook token</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "points",
            "description": "<p>The user points</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "settings",
            "description": "<p>The user settings</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "settings.daily_goal",
            "description": "<p>The user daily goal points</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.sound",
            "description": "<p>If the user has enabled sounds</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_newsletter",
            "description": "<p>If the user should receive the newsletter</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_comment",
            "description": "<p>If the user should receive an comment email</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "updated_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The user class_id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/users/me",
    "title": "Me",
    "group": "Users",
    "name": "UserMe",
    "description": "<p>Get the current user</p>",
    "permission": [
      {
        "name": "student"
      }
    ],
    "filename": "app/Http/Controllers/UserController.php",
    "groupTitle": "Users",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The user id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>The user username</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email address</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "firstname",
            "description": "<p>The user firstname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "lastname",
            "description": "<p>The user lastname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "facebook_token",
            "description": "<p>The facebook token</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "points",
            "description": "<p>The user points</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "settings",
            "description": "<p>The user settings</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "settings.daily_goal",
            "description": "<p>The user daily goal points</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.sound",
            "description": "<p>If the user has enabled sounds</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_newsletter",
            "description": "<p>If the user should receive the newsletter</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_comment",
            "description": "<p>If the user should receive an comment email</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "updated_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The user class_id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "post",
    "url": "/users/register",
    "title": "Register",
    "group": "Users",
    "name": "UserRegister",
    "description": "<p>Register a user</p>",
    "permission": [
      {
        "name": "public for registering regular user, admin otherwise"
      }
    ],
    "parameter": {
      "fields": {
        "Data": [
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "optional": false,
            "field": "password",
            "description": "<p>The user password</p>"
          },
          {
            "group": "Data",
            "type": "string",
            "allowedValues": [
              "registered",
              "premium",
              "moderator",
              "admin"
            ],
            "optional": false,
            "field": "type",
            "description": "<p>The user type</p>"
          },
          {
            "group": "Data",
            "type": "int",
            "optional": true,
            "field": "class_id",
            "defaultValue": "null",
            "description": "<p>The class id; should be <code>null</code> for <code>moderator</code> and <code>admin</code></p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 409": [
          {
            "group": "Error 409",
            "optional": false,
            "field": "conflict",
            "description": "<p>The given data failed to pass validation</p>"
          }
        ],
        "Error 422": [
          {
            "group": "Error 422",
            "optional": false,
            "field": "validation",
            "description": "<p>The given email already exists</p>"
          },
          {
            "group": "Error 422",
            "optional": false,
            "field": "save",
            "description": "<p>The validated user save failed</p>"
          }
        ],
        "Error 502": [
          {
            "group": "Error 502",
            "optional": false,
            "field": "email_not_sent",
            "description": "<p>Unable to sent reset email</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UserController.php",
    "groupTitle": "Users",
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The user id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>The user username</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email address</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "firstname",
            "description": "<p>The user firstname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "lastname",
            "description": "<p>The user lastname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "facebook_token",
            "description": "<p>The facebook token</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "points",
            "description": "<p>The user points</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "settings",
            "description": "<p>The user settings</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "settings.daily_goal",
            "description": "<p>The user daily goal points</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.sound",
            "description": "<p>If the user has enabled sounds</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_newsletter",
            "description": "<p>If the user should receive the newsletter</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_comment",
            "description": "<p>If the user should receive an comment email</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "updated_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The user class_id</p>"
          }
        ]
      }
    }
  },
  {
    "version": "1.0.0",
    "type": "get",
    "url": "/users/{?page}/{?perPage}",
    "title": "Index",
    "group": "Users",
    "name": "UsersIndex",
    "description": "<p>Get a user by id</p>",
    "permission": [
      {
        "name": "teacher"
      }
    ],
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "int",
            "optional": true,
            "field": "page",
            "defaultValue": "1",
            "description": "<p>The page number</p>"
          },
          {
            "group": "Parameter",
            "type": "int",
            "optional": true,
            "field": "perPage",
            "defaultValue": "10",
            "description": "<p>The user count per page</p>"
          }
        ]
      }
    },
    "error": {
      "fields": {
        "Error 400": [
          {
            "group": "Error 400",
            "optional": false,
            "field": "invalid_request",
            "description": "<p>page and perPage have to be strictly positive</p>"
          }
        ],
        "Error 401": [
          {
            "group": "Error 401",
            "optional": false,
            "field": "unauthorized",
            "description": "<p>The user does not have enough privileges</p>"
          }
        ]
      }
    },
    "filename": "app/Http/Controllers/UserController.php",
    "groupTitle": "Users",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Authorization",
            "description": "<p>Bearer <code>access_token</code></p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "id",
            "description": "<p>The user id</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "username",
            "description": "<p>The user username</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "email",
            "description": "<p>The user email address</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "firstname",
            "description": "<p>The user firstname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "lastname",
            "description": "<p>The user lastname</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "facebook_token",
            "description": "<p>The facebook token</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "points",
            "description": "<p>The user points</p>"
          },
          {
            "group": "Success 200",
            "type": "array",
            "optional": false,
            "field": "settings",
            "description": "<p>The user settings</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "settings.daily_goal",
            "description": "<p>The user daily goal points</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.sound",
            "description": "<p>If the user has enabled sounds</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_newsletter",
            "description": "<p>If the user should receive the newsletter</p>"
          },
          {
            "group": "Success 200",
            "type": "bool",
            "optional": false,
            "field": "settings.email_comment",
            "description": "<p>If the user should receive an comment email</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "created_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "string",
            "optional": false,
            "field": "updated_at",
            "description": "<p>The user creation date; Y-m-d H:i:s</p>"
          },
          {
            "group": "Success 200",
            "type": "int",
            "optional": false,
            "field": "class_id",
            "description": "<p>The user class_id</p>"
          }
        ]
      }
    }
  }
] });
