# import datetime
# import requests
# from fastapi import FastAPI, HTTPException
# from fastapi.responses import StreamingResponse
# from pydantic import BaseModel, Field, field_validator
#
# app = FastAPI(title="Генератор историй API (Ollama)")
#
# # ===== Модель запроса =====
# class StoryRequest(BaseModel):
#     age: int = Field(..., gt=0)
#     language: str
#     genre: str
#     characters: list[str] = Field(..., min_length=1)
#
#     @field_validator("language")
#     @classmethod
#     def language_check(cls, v: str):
#         if v not in ("ru", "kk"):
#             raise ValueError("Язык должен быть 'ru' или 'kk'")
#         return v
#
# # ===== Эндпоинт =====
# @app.post("/generate_story")
# async def generate_story(request: StoryRequest):
#     prompt = (
#         f"Напиши добрую детскую сказку на {request.language} языке "
#         f"для ребёнка {request.age} лет, "
#         f"с персонажами: {', '.join(request.characters)}. Жанр: {request.genre}"
#     )
#
#     def stream():
#         yield f"# Сказка для {request.age}-летнего ребёнка\n"
#         yield f"**Язык:** {'русский' if request.language == 'ru' else 'казахский'}\n"
#         yield f"**Персонажи:** {', '.join(request.characters)}\n\n"
#         yield f"**Жанр:** {request.genre}\n\n"
#
#         # ===== Стриминг с Ollama =====
#         # Ollama должен быть установлен и работать локально
#         # Модель скачана: ollama pull mistral
#         try:
#             response = requests.post(
#                 "http://localhost:11434/api/generate",
#                 json={
#                     "model": "mistral",      # или любая другая локальная модель
#                     "prompt": prompt,
#                     "stream": True
#                 },
#                 stream=True,
#             )
#
#             for line in response.iter_lines():
#                 if line:
#                     data = line.decode("utf-8")
#                     if '"response":"' in data:
#                         # простая обработка стрима
#                         yield data.split('"response":"')[1].rstrip('"}')
#
#         except Exception as e:
#             yield f"\n\n[Ошибка Ollama: {e}]"
#
#         yield f"\n\n---\n_Сказка сгенерирована локально {datetime.datetime.utcnow().isoformat()}Z_"
#
#     try:
#         return StreamingResponse(stream(), media_type="text/markdown")
#     except Exception as e:
#         raise HTTPException(status_code=500, detail=str(e))


import json
import datetime
import requests
from fastapi import FastAPI, HTTPException
from fastapi.responses import StreamingResponse
from pydantic import BaseModel, Field, field_validator

app = FastAPI()


class StoryRequest(BaseModel):
    age: int = Field(..., gt=0)
    language: str
    genre: str
    characters: list[str] = Field(..., min_length=1)

    @field_validator("language")
    @classmethod
    def language_check(cls, v: str):
        if v not in ("ru", "kk"):
            raise ValueError("Язык должен быть 'ru' или 'kk'")
        return v


@app.post("/generate_story")
async def generate_story(request: StoryRequest):
    prompt = (
        f"Напиши добрую детскую сказку на {request.language} языке "
        f"для ребёнка {request.age} лет, "
        f"с персонажами: {', '.join(request.characters)}. "
        f"Жанр: {request.genre}"
    )

    def stream():
        # ===== Markdown header =====
        yield f"# Сказка для {request.age}-летнего ребёнка\n\n"
        yield f"**Язык:** {'русский' if request.language == 'ru' else 'казахский'}\n\n"
        yield f"**Персонажи:** {', '.join(request.characters)}\n\n"
        yield f"**Жанр:** {request.genre}\n\n"
        yield "---\n\n"

        try:
            response = requests.post(
                "http://localhost:11434/api/generate",
                json={
                    "model": "mistral",
                    "prompt": prompt,
                    "stream": True
                },
                stream=True,
            )

            for line in response.iter_lines():
                if not line:
                    continue

                data = json.loads(line.decode("utf-8"))

                # ❗ Берём ТОЛЬКО текст
                if "response" in data:
                    yield data["response"]

                # ❗ Когда генерация закончена — выходим
                if data.get("done"):
                    break

        except Exception as e:
            yield f"\n\n**Ошибка Ollama:** `{e}`"

        yield (
            f"\n\n---\n"
            f"_Сказка сгенерирована {datetime.datetime.utcnow().isoformat()}Z_"
        )

    return StreamingResponse(stream(), media_type="text/markdown; charset=utf-8")


def getCharacterNames():
    arr = ['Заяц', 'Волк', 'Лиса', 'Алдар Көсе', 'Әйел Арстан']
