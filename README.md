# Github https://github.com/qwerty06081995/fairy.git
Необходимые иструменты
1) git
2) composer
3) php 8.4
4) yii2
5) postgres 17
6) ollama
7) python 3.2

**Я использовал Windows. Linux дистрибутивах могут отличатся некоторые команды**

## 1. Загрузите проект с помощью git.

```git
    git clone https://github.com/qwerty06081995/fairy.git
```

## 2. В папке frontend находится yii2 проект

### 2.1. Зайдите в папку и выполните команду 
```composer
    composer install
```
### 2.2. Настроите конфиги для БД(база данных). Поставьте свои данные
>frontend/config/db.php
```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=test_db',
    'username' => 'test_username',
    'password' => 'test_password',
    'charset' => 'utf8',
];
```

### 2.3. После выполните команду
```php
    php yii migrate
```

### 2.4. После запустите проект
```php
    php yii serve
```

## 3. В папке story_api находится Python проект. Зайдите в проект story_api

### 3.1. Выполните команду Перед выполнение убедитесь что у вас версия питон 3.2.
```python
    pip install -r requirements.txt
```

### 3.2. После выполните в консоле команду. В Linux дистрибутивах отличается команда 
```python
    .\venv\Scripts\activate 
```

### 3.3. Запускаем сервис 
```python
    uvicorn main:app --reload
```

## 4. Вместо Openai api я использовал Ollama

### 4.1. Скачиваем ollama по ссылке (Для Windows). Установливаем

https://ollama.com/

### 4.2. В консоле выполняем команду 

```
    ollama pull mistral
```

Это все, теперь переходим по ссылке http://localhost:8080/ и тестируем. 
Проблемы который могут возникнут
1) Проверьте подключение к базе данных. Я использовал Postgres. 
2) Проверьте в каком папке находитеть, для yii2 -> frontend, python -> story_api
3) Версий. Проверьте версий php, python.
