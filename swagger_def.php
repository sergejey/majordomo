<?php
$swagger = [
  "openapi" => "3.0.1",
  "info" => [
    "title" => "Majordomo REST API",
    "version" => "1.0",
    "description" => "Автоматизированное описание API Majordomo (сгенерировано на основе api.php)",
    "contact" => [
      "name" => "Majordomo Support",
      "url" => "https://github.com/sergejey/majordomo"
    ]
  ],
  "servers" => [
    ["url" => "http://{host}/api.php", "variables" => [
      "host" => ["default" => "192.168.1.201"]
    ]]
  ],
  "paths" => [
    "/devices" => [
      "get" => [
        "summary" => "Список всех устройств",
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "devices" => [
                      "type" => "array",
                      "items" => [
                        "type" => "object",
                        "properties" => [
                          "id" => ["type" => "integer"],
                          "title" => ["type" => "string"],
                          "type" => ["type" => "string"],
                          "room" => ["type" => "string"],
                          "linked_object" => ["type" => "string"]
                        ]
                      ]
                    ]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ],
    "/devices/{id}" => [
      "get" => [
        "summary" => "Получить данные устройства по ID",
        "parameters" => [[
          "name" => "id", 
          "in" => "path", 
          "required" => true, 
          "schema" => ["type" => "integer"],
          "description" => "ID устройства"
        ]],
        "responses" => [
          "200" => [
            "description" => "Информация об устройстве",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "device" => [
                      "type" => "object",
                      "properties" => [
                        "id" => ["type" => "integer"],
                        "title" => ["type" => "string"],
                        "type" => ["type" => "string"],
                        "room" => ["type" => "string"],
                        "linked_object" => ["type" => "string"],
                        "system_device" => ["type" => "boolean"],
                        "subDevices" => [
                          "type" => "array",
                          "items" => ["type" => "object"]
                        ],
                        "linksTotal" => ["type" => "integer"],
                        "scheduleTotal" => ["type" => "integer"]
                      ]
                    ]
                  ]
                ]
              ]
            ]
          ],
          "404" => ["description" => "Устройство не найдено"]
        ]
      ],
      "post" => [
        "summary" => "Обновить устройство",
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object",
            "properties" => [
              "title" => ["type" => "string"],
              "room" => ["type" => "string"],
              "load_type" => ["type" => "string"]
            ]
          ]]]
        ],
        "responses" => ["200" => ["description" => "Результат обновления"]]
      ],
      "delete" => [
        "summary" => "Удалить устройство",
        "parameters" => [
          [
            "name" => "id",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "integer"],
            "description" => "ID устройства"
          ]
        ],
        "responses" => [
          "200" => ["description" => "Удалено"],
          "404" => ["description" => "Устройство не найдено"]
        ]
      ]
    ],
    "/devices/{id}/links" => [
      "get" => [
        "summary" => "Получить связи устройства",
        "parameters" => [
          [
            "name" => "id",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "integer"],
            "description" => "ID устройства"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "Список связей",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "links" => [
                      "type" => "array",
                      "items" => [
                        "type" => "object",
                        "properties" => [
                          "ID" => ["type" => "integer"],
                          "DEVICE1_ID" => ["type" => "integer"],
                          "DEVICE2_ID" => ["type" => "integer"],
                          "LINK_TYPE" => ["type" => "string"],
                          "LINK_SETTINGS" => ["type" => "object"],
                          "IS_ACTIVE" => ["type" => "boolean"],
                          "DEVICE1_TITLE" => ["type" => "string"],
                          "DEVICE2_TITLE" => ["type" => "string"]
                        ]
                      ]
                    ],
                    "available_links" => [
                      "type" => "array",
                      "items" => [
                        "type" => "object",
                        "properties" => [
                          "TARGET_CLASS" => ["type" => "string"],
                          "LINK_TYPE" => ["type" => "string"],
                          "TARGET_DEVICES" => [
                            "type" => "array",
                            "items" => ["type" => "object"]
                          ]
                        ]
                      ]
                    ]
                  ]
                ]
              ]
            ]
          ],
          "404" => ["description" => "Устройство не найдено"]
        ]
      ],
      "post" => [
        "summary" => "Добавить или обновить связь",
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object",
            "properties" => [
              "link_id" => ["type" => "integer"],
              "device1_id" => ["type" => "integer"],
              "device2_id" => ["type" => "integer"],
              "link_type" => ["type" => "string"],
              "link_settings" => ["type" => "object"],
              "active" => ["type" => "boolean"]
            ]
          ]]]
        ],
        "responses" => ["200" => ["description" => "ОК"]]
      ],
      "delete" => [
        "summary" => "Удалить связь",
        "parameters" => [[
          "name" => "link_id", "in" => "query", "required" => true, "schema" => ["type" => "integer"]
        ]],
        "responses" => ["200" => ["description" => "Удалено"]]
      ]
    ],
    "/devices/{id}/schedule" => [
      "get" => [
        "summary" => "Получить расписание устройства",
        "parameters" => [
          [
            "name" => "id",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "integer"],
            "description" => "ID устройства"
          ]
        ],
        "responses" => ["200" => ["description" => "Расписание"]]
      ],
      "post" => [
        "summary" => "Добавить или изменить точку расписания",
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object",
            "properties" => [
              "point_id" => ["type" => "integer"],
              "linked_method" => ["type" => "string"],
              "value" => ["type" => "string"],
              "set_time" => ["type" => "string"],
              "set_days" => ["type" => "string"]
            ]
          ]]]
        ],
        "responses" => ["200" => ["description" => "ОК"]]
      ],
      "delete" => [
        "summary" => "Удалить точку расписания",
        "parameters" => [[
          "name" => "point_id", "in" => "query", "required" => true, "schema" => ["type" => "integer"]
        ]],
        "responses" => ["200" => ["description" => "Удалено"]]
      ]
    ],
    "/rooms" => [
      "get" => ["summary" => "Список всех комнат", "responses" => ["200" => ["description" => "OK"]]],
      "post" => [
        "summary" => "Создать или обновить комнату",
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object",
            "properties" => [
              "id" => ["type" => "integer"],
              "title" => ["type" => "string"],
              "priority" => ["type" => "integer"]
            ]
          ]]]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ]
    ],
    "/rooms/{id}" => [
      "get" => [
        "summary" => "Информация о комнате",
        "parameters" => [
          [
            "name" => "id",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "integer"],
            "description" => "ID комнаты"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "room" => [
                      "type" => "object",
                      "properties" => [
                        "id" => ["type" => "integer"],
                        "title" => ["type" => "string"],
                        "object" => ["type" => "string"],
                        "roomIcon" => ["type" => "string"],
                        "devices" => [
                          "type" => "array",
                          "items" => [
                            "type" => "object",
                            "properties" => [
                              "id" => ["type" => "integer"],
                              "title" => ["type" => "string"],
                              "object" => ["type" => "string"],
                              "type" => ["type" => "string"],
                              "system_device" => ["type" => "boolean"]
                            ]
                          ]
                        ]
                      ]
                    ]
                  ]
                ]
              ]
            ]
          ],
          "404" => ["description" => "Комната не найдена"]
        ]
      ],
      "post" => ["summary" => "Обновить комнату", "responses" => ["200" => ["description" => "OK"]]],
      "delete" => ["summary" => "Удалить комнату", "responses" => ["200" => ["description" => "Удалено"]]]
    ],
    "/data" => [
      "post" => [
        "summary" => "Получить значения свойств (bulk)",
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object",
            "properties" => ["properties" => ["type" => "array", "items" => ["type" => "string"]]]
          ]]]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ]
    ],
    "/data/{object}" => [
      "get" => [
        "summary" => "Все свойства объекта",
        "parameters" => [
          [
            "name" => "object",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя объекта"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "data" => ["type" => "object"],
                    "result" => ["type" => "boolean"]
                  ]
                ]
              ]
            ]
          ]
        ]
      ],
      "post" => [
        "summary" => "Установить значение свойства объекта",
        "parameters" => [
          [
            "name" => "object",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя объекта"
          ]
        ],
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object", "properties" => ["data" => ["type" => "string"]]
          ]]]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ]
    ],
    "/data/{object}.{property}" => [
      "get" => [
        "summary" => "Получить значение свойства",
        "parameters" => [
          [
            "name" => "object",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя объекта"
          ],
          [
            "name" => "property",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя свойства"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "data" => ["type" => "string"],
                    "result" => ["type" => "string"]
                  ]
                ]
              ]
            ]
          ]
        ]
      ],
      "post" => [
        "summary" => "Установить значение свойства",
        "requestBody" => [
          "content" => ["application/json" => ["schema" => [
            "type" => "object", "properties" => ["data" => ["type" => "string"]]
          ]]]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ]
    ],
    "/objects" => [
      "get" => ["summary" => "Список всех объектов", "responses" => ["200" => ["description" => "OK"]]]
    ],
    "/objects/{class}" => [
      "get" => [
        "summary" => "Список объектов класса",
        "parameters" => [
          [
            "name" => "class",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя класса"
          ]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ]
    ],
    "/module/{name}" => [
      "get" => [
        "summary" => "Вызвать api() модуля",
        "parameters" => [
          [
            "name" => "name",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя модуля"
          ]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ],
      "post" => [
        "summary" => "Вызвать api() модуля (POST)",
        "parameters" => [
          [
            "name" => "name",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя модуля"
          ]
        ],
        "responses" => ["200" => ["description" => "OK"]]
      ]
    ],
    "/events/{event}" => [
      "get" => [
        "summary" => "Сгенерировать событие",
        "parameters" => [
          [
            "name" => "event",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя события"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "event_name" => ["type" => "string"],
                    "event_id" => ["type" => "integer"],
                    "params" => ["type" => "object"],
                    "result" => ["type" => "boolean"]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ],
    "/rooms/setOrder" => [
      "post" => [
        "summary" => "Установить порядок комнат",
        "requestBody" => [
          "required" => true,
          "content" => [
            "application/json" => [
              "schema" => [
                "type" => "array",
                "items" => [
                  "type" => "object",
                  "properties" => [
                    "id" => ["type" => "integer"],
                    "priority" => ["type" => "integer"]
                  ],
                  "required" => ["id", "priority"]
                ]
              ]
            ]
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "result" => ["type" => "boolean"]
                  ]
                ]
              ]
            ]
          ],
          "400" => ["description" => "Incorrect input data"]
        ]
      ]
    ],
    "/modulepropertyset/{name}" => [
      "get" => [
        "summary" => "Установить свойство модуля",
        "parameters" => [
          [
            "name" => "name",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя модуля"
          ],
          [
            "name" => "object",
            "in" => "query",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя объекта"
          ],
          [
            "name" => "property",
            "in" => "query",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя свойства"
          ],
          [
            "name" => "value",
            "in" => "query",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Значение свойства"
          ]
        ],
        "responses" => [
          "200" => ["description" => "OK"]
        ]
      ]
    ],
    "/history/{property}" => [
      "get" => [
        "summary" => "Получить историю значений свойства",
        "parameters" => [
          [
            "name" => "property",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя свойства"
          ],
          [
            "name" => "period",
            "in" => "query",
            "required" => false,
            "schema" => [
              "type" => "string",
              "enum" => ["day", "week", "month", "year"],
              "default" => "day"
            ],
            "description" => "Период (day, week, month, year или число часов)"
          ],
          [
            "name" => "action",
            "in" => "query",
            "required" => false,
            "schema" => [
              "type" => "string",
              "enum" => ["max", "min", "count", "sum", "avg"]
            ],
            "description" => "Тип агрегации данных"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "result" => ["type" => "array", "items" => ["type" => "object"]]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ],
    "/method/{name}" => [
      "get" => [
        "summary" => "Вызвать метод",
        "parameters" => [
          [
            "name" => "name",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя метода"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "result" => ["type" => "string"]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ],
    "/script/{name}" => [
      "get" => [
        "summary" => "Выполнить скрипт",
        "parameters" => [
          [
            "name" => "name",
            "in" => "path",
            "required" => true,
            "schema" => ["type" => "string"],
            "description" => "Имя скрипта"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "result" => ["type" => "string"]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ],
    "/messages" => [
      "get" => [
        "summary" => "Получить список сообщений",
        "parameters" => [
          [
            "name" => "limit",
            "in" => "query",
            "required" => false,
            "schema" => ["type" => "integer"],
            "description" => "Ограничение количества сообщений"
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "Список сообщений",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "messages" => [
                      "type" => "array",
                      "items" => [
                        "type" => "object",
                        "properties" => [
                          "ID" => ["type" => "integer"],
                          "USER_ID" => ["type" => "integer"],
                          "ADDED" => ["type" => "string", "format" => "date-time"],
                          "MESSAGE" => ["type" => "string"],
                          "NAME" => ["type" => "string"]
                        ]
                      ]
                    ],
                    "user" => [
                      "type" => "object",
                      "properties" => [
                        "ID" => ["type" => "string"],
                        "NAME" => ["type" => "string"]
                      ]
                    ]
                  ]
                ]
              ]
            ]
          ]
        ]
      ],
      "post" => [
        "summary" => "Отправить сообщение",
        "requestBody" => [
          "required" => true,
          "content" => [
            "application/json" => [
              "schema" => [
                "type" => "object",
                "properties" => [
                  "message" => [
                    "type" => "string",
                    "description" => "Текст сообщения"
                  ]
                ],
                "required" => ["message"]
              ]
            ]
          ]
        ],
        "responses" => [
          "200" => [
            "description" => "OK",
            "content" => [
              "application/json" => [
                "schema" => [
                  "type" => "object",
                  "properties" => [
                    "result" => ["type" => "string", "enum" => ["OK", "Error"]],
                    "error" => ["type" => "string"]
                  ]
                ]
              ]
            ]
          ]
        ]
      ]
    ]
  ]
];

?>
