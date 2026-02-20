import os
from celery import Celery

# Try to fetch REDIS_URL from Env, otherwise fallback securely to local
REDIS_URL = os.environ.get("REDIS_URL", "redis://localhost:6379/0")

# Initialize Celery Distributed Task Queue
# - Broker: Where raw tasks (Job ID + S3 Link) are sent 
# - Backend: Where results (AI Metrics, Cloud Top Height) are stored
celery_app = Celery(
    "ai_core_tasks",
    broker=REDIS_URL,
    backend=REDIS_URL,
    include=["workers.tasks"] # Where the Heavy PyTorch & HPC Logic lives
)

# Optional NASA-Level optimization: Time limits per Earth Observation Tile
celery_app.conf.update(
    task_serializer='json',
    accept_content=['json'], 
    result_serializer='json',
    timezone='UTC',
    enable_utc=True,
    task_time_limit=300, # Max 5 mins per huge 4K satellite tile
    worker_prefetch_multiplier=1 # Don't hoard tasks, let HPC nodes be fair
)
