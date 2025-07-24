# Telegram Bot Laravel Service

Проект представляет собой Telegram-бота на Laravel, который:
- Обрабатывает команды `/start` и `/stop` от пользователей через Telegram Webhook.
- Сохраняет подписчиков в базу данных.
- По artisan команде получает список задач из внешнего API.
- Форматирует список задач и рассылает уведомления всем подписанным пользователям.

---
## 🧰 Используемые технологии

- PHP 8.2
- Laravel 12
- MySQL 8
- Docker & Docker Compose
- Ngrok (для публичного доступа к Telegram Webhook)
- Guzzle (для работы с Telegram Bot API)
- Laravel Queue Jobs
- Laravel Cache
- PHPUnit

---

## 🚀 Быстрый старт (через Docker)

### 1. Клонируйте проект и перейдите в папку

```bash
git clone <repo_url>
cd telegram-bot
cp .env.example .env //Важно добавить пароль для бд
```
### 2. Запуск через Docker

```bash
docker-compose nginx up -d
docker-compose run composer i
docker-compose run artisan key:generate
docker-compose run artisan migrate --force
docker-compose up ngrok -d
```

### 3. Привязка Telegram к нашему проекту
Открой http://localhost:4040/ если поменял ngrok в Docker url будет другой

Скопируй url пример: https://YOUR_URL.ngrok-free.app/

Удаление веб хука
```bash
 curl -X GET "https://api.telegram.org/botTELEGRAM_BOT_KEY/deleteWebhook"
```
Ответ должен быть такого формата
```json
{
    "ok":true,
    "result":true,
    "description":"Webhook was deleted"
}
```
Привязка веб хука
```bash
 curl -X GET "https://api.telegram.org/botTELEGRAM_BOT_KEY/setWebhook?url=https://YOUR_URL.ngrok-free.app/api/telegram/webhook"
```
Ответ должен быть такого формата
```json
{
    "ok":true,
    "result":true,
    "description":"Webhook was set"
}
```

### 4. Телеграм бот 
🤖 О самом боте
@VladyslavAntonovBot

При команде /start бот отвечает:
```text
Юзер успешно начал пользоваться ботом ;)
```

При команде /stop бот отвечает:
```text
Юзер покинул бота (
```

После /start пользователь сохраняется в таблицу users с флагом subscribed = true.
После /stop — с флагом subscribed = false.

### 5. Рассылка списка задач
```bash
docker-compose run artisan app:notify-tasks
```
#### 1. Команда запрашивает задачи по API:
```json
{
    "userId": 1,
    "id": 1,
    "title": "delectus aut autem",
    "completed": false
}
```
#### 2.Фильтрует только незавершённые задачи (completed: false) с userId <= 5.
#### 3.Форматирует через TaskMessageFormatter в такой вид:
```text
📋 *Список новых задач:*

👤 Пользователь #1 — delectus aut autem
👤 Пользователь #1 — quis ut nam facilis et officia qui
…
```
#### 4. Отправляет каждому подписчику через очередь SendTelegramMessageJob.

### На этом весь функционал завершился, доп инфо

### 1. Структура таблицы `users`

| Поле         | Тип           | Атрибуты         |
|--------------|---------------|------------------|
| id           | BIGINT (AI)   | primary key      |
| name         | VARCHAR(255)  | not null         |
| telegram_id  | BIGINT        | unique, not null |
| subscribed   | BOOLEAN       | not null         |

### 2. Тесты
Запуск тестов
```bash
docker-compose run artisan test
```
