# Аудит 3rdparty (2026-11-26)

Отчёт по состоянию на ветке `alpha`. Ограничения: только локальные ассеты, PHP 7.3+, без CDN и новых рантайм-зависимостей.

## Приоритет 0 — безопасно сейчас

| Что | Состояние | Рекомендация |
|---|---|---|
| jQuery в `diagnostic.php` и `load_settings.php` | подключался с `code.jquery.com` | **исправлено в этом PR**: локальный `3rdparty/jquery/jquery-3.3.1.min.js` |
| jQuery 3.3.1 | CVE-2019-11358 (prototype pollution), CVE-2020-11022/11023 (DOM XSS) | обновить до **3.7.1** отдельным PR; jQuery 4 ломает legacy-плагины |
| jQuery Migrate 3.0.0 | шум в консоли, совместимость с 1.x/2.x не нужна | удалить после проверки `.live/.bind/.size/$.browser` |
| Bootstrap 3.0.0 JS и локальный CSS | CVE-2019-8331; локальный CSS пропатчен | обновить до **3.4.1**, перенеся локальные 패тчи и `!important`-правила тёмной темы |
| PHPMailer 6.0.1 | CVE-2018-19296 (phar object injection → RCE) | обновить до актуальной 6.x |
| elFinder 2.1.61 | CVE-2023-35840 (path traversal), коннектор без авторизации | обновить до 2.1.70+, добавить проверку сессии админки |
| three.js для 3D-сцен | каталог `3rdparty/threejs/` отсутствует, шаблон даёт 404 | добавить локальную сборку или убрать 3D-ветку из `templates/scenes/action_usual.html` |

## Приоритет 1 — обновить

| Библиотека | Сейчас | Рекомендация |
|---|---|---|
| Moment.js | 2.20.1 | 2.31.0 (datetimepicker 4.17.47 совместим) |
| Select2 | 4.0.6-rc.1 | 4.0.13; оставить только min-сборку и нужные локали |
| fancyBox | 3.5.7, но cache-buster `?v=3.3.5` | **исправлено в этом PR**: `?v=3.5.7` |
| CodeMirror | 5.46.0 ядро + 4.1.1 во freeboard | обновление до 5.65.16 и отказ от CM4 сделаны в отдельном PR (#1422) |
| Bootstrap dark | Bootswatch 3.3.2 | привести к 3.4.1 вместе с основной веткой |

## Приоритет 2 — убрать дубли и мёртвые файлы

- `3rdparty/bootstrap/js/_bootstrap.min.js`, `js/npm.js`, `css/_bootstrap.min.css` (Bootstrap 2.x).
- Локальные `css/bootstrap-theme.*` использовать только если убрать CDN-подключение на error-страницах.
- `3rdparty/select2/js/select2.js`, `select2.full.*`,большинство `js/i18n/*`, `css/select2_dark.min.css` — не загружаются.
- `3rdparty/swiper/js/swiper.js`, `swiper.esm*.js`, `.map`, `css/swiper.css` — используется только min-сборка.
- Grunt-артефакты freeboard: `js/freeboard*.js`, `freeboard.thirdparty*`, `majordomo_plugins.js.php` (page грузит `lib/js`).
- `3rdparty/elFinder/elfinder.legacy.html`.
- Build/dev-деревья Blockly: `appengine/`, `externs/`, `i18n/`, `package/`, `theme_scripts/`, `typings/`, `gulpfile.js`, неиспользуемые `*_compressed.js`.
- Демо-файлы CodeMirror `mode/*/index.html`, `test.js`.

## Приоритет 3 — оставить с планом миграции

- Blockly 1.9.1 — сильная кастомизация Majordomo; обновление требует миграции `Blockly.inject`/`mainWorkspace` и генераторов.
- freeboard — ядро дашбордов; старый jQuery 2.0.3 внутри не трогать без переписки jqXHR `.success()/.error()`.
- jQuery Mobile 1.4.5, pChart/jpGraph, amCharts 4 — рабочие, но устаревшие; миграция отдельными задачами.
- Font Awesome 7 уже есть локально в `3rdparty/fontawesome`; проверять, что все иконки берутся отсюда.

## Что сделано в этом PR

- `diagnostic.php` и `load_settings.php` больше не обращаются к CDN за jQuery.
- Исправлен cache-buster fancyBox на фактическую версию 3.5.7.
- Документированы приоритеты обновлений, удалений и добавлений (этот файл).
