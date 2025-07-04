import requests

API_KEY = "sk-proj......"
TRAINING_FILE_PATH = "training_data.jsonl"

def upload_training_file(filepath):
    headers = {
        "Authorization": f"Bearer {API_KEY}"
    }
    with open(filepath, "rb") as f:
        files = {
            "file": (filepath, f)
        }
        data = {
            "purpose": "fine-tune"
        }
        print("Uploading training file...")
        response = requests.post(
            "https://api.openai.com/v1/files",
            headers=headers,
            files=files,
            data=data
        )
    if response.status_code != 200:
        raise Exception(f"File upload failed: {response.status_code} {response.text}")
    file_id = response.json()["id"]
    print(f"File uploaded successfully. File ID: {file_id}")
    return file_id

def create_fine_tune_job(file_id, model="gpt-3.5-turbo"):
    headers = {
        "Authorization": f"Bearer {API_KEY}",
        "Content-Type": "application/json"
    }
    json_data = {
        "training_file": file_id,
        "model": model
    }
    print("Creating fine-tune job...")
    response = requests.post(
        "https://api.openai.com/v1/fine_tuning/jobs",
        headers=headers,
        json=json_data
    )
    if response.status_code != 200:
        raise Exception(f"Fine-tune creation failed: {response.status_code} {response.text}")
    fine_tune_info = response.json()
    print("Fine-tune job created:")
    print(fine_tune_info)
    return fine_tune_info

if __name__ == "__main__":
    try:
        file_id = upload_training_file(TRAINING_FILE_PATH)
        create_fine_tune_job(file_id)
    except Exception as e:
        print("Error:", e)
