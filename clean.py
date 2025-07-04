import json
from openai import OpenAI

def generate_and_clean(system_message, student_text, client):
    # Step 1: call finetuned model
    response_ft = client.chat.completions.create(
        model="ft:gpt-3.5-turbo-0125:agencija-arbeiter::BpAbJwu9",
        messages=[
            {"role": "system", "content": system_message},
            {"role": "user", "content": student_text}
        ],
        temperature=0.2,
    )
    raw_feedback = response_ft.choices[0].message.content

    # Step 2: clean with GPT-4o-mini
    system_message_cleaner = """
You are a JSON expert and text cleaner.
Your task is to receive a raw JSON-like text, fix syntax errors, remove duplicates, 
complete missing fields if possible, and produce valid JSON exactly as specified:

{
  "template_letter": "...",
  "word_count": 0,
  "corrected_text": "...",
  "comments": [],
  "error_counts": {},
  "grades": {
    "Grammatik": {"note": 0, "kommentar": ""},
    "Wortschatz": {"note": 0, "kommentar": ""},
    "Stil": {"note": 0, "kommentar": ""},
    "Inhalt": {"note": 0, "kommentar": ""},
    "Gesamtnote": {"note": 0, "kommentar": "", "ergebnis": ""}
  }
}

Preserve the content and style, but make sure the JSON is fully valid and parsable.
Return only the JSON object.
"""

    response_clean = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=[
            {"role": "system", "content": system_message_cleaner},
            {"role": "user", "content": raw_feedback}
        ],
        temperature=0.0,
    )
    final_clean_json_text = response_clean.choices[0].message.content

    # Parse to dict before returning
    try:
        final_data = json.loads(final_clean_json_text)
    except json.JSONDecodeError:
        # fallback: return raw text as corrected_text only
        final_data = {
            "corrected_text": final_clean_json_text,
            "comments": [],
            "grades": {}
        }
    return final_data
