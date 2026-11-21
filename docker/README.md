# Docker-стек MajorDoMo ( amd64 / arm64 )

## Зачем так

В репозитории раньше не было Docker-файлов. Вместо пяти почти одинаковых
`Dockerfile` под каждую платформу используется **один multi-arch `Dockerfile`**:
официальный образ `php:<version>-apache-bookworm` публикуется для `linux/amd64`
и `linux/arm64`, поэтому `docker buildx` сам выбирает нужную архитектуру.
Платформенные отличия вынесены в оверлеи `docker-compose.*.yml` (тип платформы
и лимиты ресурсов).
Это меньше дублирования и одинаковый результат на Ubuntu/Windows/RPi/OrangePC.

Состав стека:

- `db` — MariaDB 10.11, при первом старте импортируется `db_terminal.sql`;
- `web` — Apache + PHP, веб-интерфейс;
- `cycle` — тот же образ, но `php cycle.php` (фоновые циклы из README).

Оба PHP-контейнера используют один образ и одни и те же тома.

## Быстрый старт

```bash
cd docker
cp .env.example .env      # обязательно поменяйте пароли
docker compose -f docker-compose.yml -f docker-compose.ubuntu.yml up -d --build
```

Для других платформ последняя строка:

| Платформа | Команда |
|---|---|
| Ubuntu / Debian x86_64 | `docker compose -f docker-compose.yml -f docker-compose.ubuntu.yml up -d --build` |
| Windows 10/11 + Docker Desktop (WSL2) | `docker compose -f docker-compose.yml -f docker-compose.windows.yml up -d --build` |
| Raspberry Pi 4 (64-bit OS) | `docker compose -f docker-compose.yml -f docker-compose.rpi4.yml up -d --build` |
| Raspberry Pi 5 (64-bit OS) | `docker compose -f docker-compose.yml -f docker-compose.rpi5.yml up -d --build` |
| OrangePi / OrangePC (64-bit OS) | `docker compose -f docker-compose.yml -f docker-compose.orange.yml up -d --build` |
| Локальная разработка | `docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build` |

После старта: `http://<host>:8080/admin.php`.

Образ можно собрать и вручную под нужную архитектуру:

```bash
docker buildx build --platform linux/arm64 -f docker/Dockerfile -t majordomo-core:local ..
```

## Переменные окружения

См. `.env.example`. Основные: `WEB_PORT`, `BASE_URL`, `DB_NAME`, `DB_USER`,
`DB_PASSWORD`, `MARIADB_ROOT_PASSWORD`, `PHP_VERSION`, `TAG`, `GENERATE_CONFIG`.
`config.php` генерируется из `config.php.sample` при каждом старте; чтобы
управлять файлом вручную, выставьте `GENERATE_CONFIG=0`.

## Недостатки и ограничения

1. **Только 64-bit ОС.** 32-bit Raspberry Pi OS (`linux/arm/v7`) не поддерживается
   официальным PHP-образом. На таком хосте нужен собственный образ или родная
   установка.
2. **PHP 7.3 в Docker не поддерживается.** Базовый тег bookworm существует только
   для PHP 8.x. Пользователи PHP 7.3 продолжают использовать обычную установку —
   код ядра от этого PR не меняется, обратная совместимость сохранена.
3. **Тома с кодом.** `modules`, `templates`, `languages`, `objects`, `rc` смонтированы
   в именованные тома. При первом старте Docker копирует туда содержимое образа,
   но **последующие обновления образа в эти тома не попадут**. Обновление ядра
   требует миграции/синхронизации томов (или временного удаления томов с потерей
   пользовательских модулей). Это цена за сохранение модулей, установленных
   через маркет. Для разработки есть `docker-compose.dev.yml` с bind-mount
   репозитория (в продакшене его использовать нельзя).
4. **Пароли БД.** Изменение `DB_PASSWORD` в `.env` после создания тома `mj-db`
   не меняет пароль уже созданного пользователя MariaDB — нужно менять вручную
   внутри БД.
5. **Нет доступа к железу.** В контейнер не проброшены `/dev/snd`, `/dev/tty*`,
   BLE и GPIO: TTS, последовательные порты и радиомодули не заработают без
   дополнительных `devices:`/privileged-настроек. Это сделано сознательно —
   контейнер не получает лишних привилегий.
6. **Процессы-cycle.** `cycle.php` создаёт дочерние процессы; лимиты CPU/RAM в
   оверлеях могут их убивать. При падении `cycle` перезапускается весь контейнер.
7. **Windows.** Именованные тома скрыты внутри Docker Desktop; бэкап требует
   `docker compose exec db mysqldump ...`. Bind-mount не используется намеренно,
   чтобы избежать проблем с CRLF и правами.
8. **Секреты по умолчанию.** Значения в `.env.example` — заглушки. Перед
   публикацией хоста в сеть нужны свои пароли и reverse proxy с TLS; порт 8080
   не стоит открывать наружу. `.env` и `config.php` исключены из образа через
   `.dockerignore`.
9. **Незафиксированные теги.** `php:8.3-apache-bookworm` и `mariadb:10.11`
   указаны по тегу, а не по digest: повторная сборка может подтянуть другую
   версию пакетов. Для продакшена фиксируйте digest в `Dockerfile`/compose.

## Что не входит

Изменений в PHP-коде ядра нет. PR добавляет только каталог `docker/`,
`.dockerignore` и ссылку в README.
