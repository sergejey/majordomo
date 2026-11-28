# Локальные подсказки

В каталогах `ru`, `en`, `uk`, `es`, `ro`, `de` лежат подсказки для всех идентификаторов, используемых в шаблонах через `LANG_HCB`/`LANG_HCE` и в настройках (`settings*...`).

## Источники

Основные формулировки взяты из облачных подсказок MajorDoMo и дополнены пояснениями и примерами для новичков:

- https://ru.majordomohome.com/Hints/code?skin=hint
- https://ru.majordomohome.com/Hints/access_control?skin=hint
- https://ru.majordomohome.com/Hints/menu_item_type?skin=hint
- https://ru.majordomohome.com/Hints/element_type?skin=hint
- https://ru.majordomohome.com/Hints/settingsSITELANGUAGE?skin=hint
- https://ru.majordomohome.com/Hints/onchange_code?skin=hint

Для идентификаторов, которых нет в облаке, добавлено нейтральное описание с подсказкой открыть настройки соответствующего модуля.

## Правило

Endpoint `modules/hints/hint.php` ищет файл сначала для текущего языка, затем `en`, затем `default`. Сетевой запрос выполняется только если локального файла нет.