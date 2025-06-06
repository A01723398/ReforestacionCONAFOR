from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import subprocess

app = FastAPI()

class AlgorithmParams(BaseModel):
    fecha_inicio: str
    tiempo_jornada: int
    denvio: int
    dreposo: int
    dplantable: int
    velocidad_promedio: float
    tiempo_plantacion: float
    tiempo_minimo_permitido: int
    plant_survival_rate: float

from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

@app.post("/run-algorithm")
def run_algorithm(params: AlgorithmParams):
    try:
        command = [
            "python", "algoritmo.py",
            params.fecha_inicio,
            str(params.tiempo_jornada),
            str(params.denvio),
            str(params.dreposo),
            str(params.dplantable),
            str(params.velocidad_promedio),
            str(params.tiempo_plantacion),
            str(params.tiempo_minimo_permitido),
            str(params.plant_survival_rate)
        ]

        result = subprocess.run(command, capture_output=True, text=True)

        return {
            "stdout": result.stdout,
            "stderr": result.stderr,
            "exit_code": result.returncode
        }
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))
