import os

from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException
from fastapi.responses import StreamingResponse
from openai import OpenAI
from pydantic import BaseModel, Field, field_validator
import datetime

load_dotenv()

client = OpenAI(api_key=os.getenv("OPENAI_API_KEY"))

app = FastAPI(title="Story Generator API")


class StoryRequest(BaseModel):
    age: int = Field(..., gt=0)
    language: str
    characters: list[str] = Field(..., min_length=1)

    @field_validator("language")
    def language_check(cls, v):
        if v not in ("ru", "kk"):
            raise ValueError("Language must be 'ru' or 'kk'")
        return v


@app.post("/generate_story")
async def generate_story(request: StoryRequest):
    prompt = f"Напиши сказку на {request.language} языке для ребёнка {request.age} лет, с персонажами: {', '.join(request.characters)}."

    try:
        def stream():
            yield f"# Сказка для {request.age}-летнего ребёнка\n"
            yield f"**Язык:** {'русский' if request.language == 'ru' else 'казахский'}\n"
            yield f"**Персонажи:** {', '.join(request.characters)}\n\n"

            response = client.responses.stream(
                model="gpt-4.1-mini",
                input=prompt,
            )

            for event in response:
                if event.type == "response.output_text.delta":
                    yield event.delta

            yield f"\n\n---\n_Сказка сгенерирована: {datetime.datetime.utcnow().isoformat()}Z_"

        return StreamingResponse(stream(), media_type="text/markdown")
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
