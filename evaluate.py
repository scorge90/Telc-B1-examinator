# evaluate.py
import json
import sys
import io
import traceback
from openai import OpenAI
from prompts import get_system_message
from ortographie import count_orthographic_errors  # your spellchecker util

# Wrap stdout to ensure UTF-8 encoding (important for German characters and PHP compatibility)
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

def get_ergebnis(gesamt_note, any_score_zero):
    if any_score_zero or gesamt_note < 25:
        return "❌ NICHT BESTANDEN"
    else:
        return "✅ BESTANDEN"

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No input file specified"}, ensure_ascii=False))
        return

    input_file = sys.argv[1]

    try:
        with open(input_file, encoding='utf-8') as f:
            raw = f.read()
    except Exception as e:
        print(json.dumps({"error": f"Failed to open input file: {e}"}, ensure_ascii=False))
        return

    if not raw.strip():
        print(json.dumps({"error": "Input JSON file is empty"}, ensure_ascii=False))
        return

    try:
        data = json.loads(raw)
    except json.JSONDecodeError as e:
        debug = raw.replace('\n', '\\n')
        print(json.dumps({
            "error": f"JSON parsing error: {e.msg}",
            "raw": debug
        }, ensure_ascii=False))
        return

    student_text = data.get("student_text", "").strip()
    template_letter = data.get("template_letter", "").strip()

    if not student_text:
        print(json.dumps({"error": "No student_text provided"}, ensure_ascii=False))
        return

    # Calculate orthographic errors ratio
    total_words = len(student_text.split())
    orthographic_errors = count_orthographic_errors(student_text)
    error_ratio = orthographic_errors / total_words if total_words > 0 else 0

    try:
        system_message = get_system_message(template_letter)
        client = OpenAI(api_key="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")

        response = client.chat.completions.create(
            model="ft:gpt-3.5-turbo-0125:agencija-arbeiter::Bp3LTe8m",
            # model="gpt-4o",
            messages=[
                {"role": "system", "content": system_message},
                {"role": "user", "content": student_text}
            ],
            temperature=0.2,
        )

        result_text = response.choices[0].message.content

        try:
            result_json = json.loads(result_text)
        except json.JSONDecodeError:
            result_json = {
                "corrected_text": result_text,
                "comments": [],
                "grades": {}
            }

        # Apply orthographic failure override if needed
        if error_ratio > 0.25:
            result_json['grades'] = {
                "Grammatik": {"note": 0, "kommentar": "Zu viele orthografische Fehler."},
                "Wortschatz": {"note": 0, "kommentar": "Zu viele orthografische Fehler."},
                "Stil": {"note": 0, "kommentar": "Zu viele orthografische Fehler."},
                "Inhalt": {"note": 0, "kommentar": "Zu viele orthografische Fehler."},
                "Gesamtnote": {
                    "note": 0,
                    "kommentar": "Der Brief wurde wegen zu vieler Fehler nicht bestanden.",
                    "ergebnis": "❌ NICHT BESTANDEN"
                }
            }
        else:
            if 'grades' in result_json and isinstance(result_json['grades'], dict):
                grades = result_json['grades']
                try:
                    note_g = grades.get("Grammatik", {}).get("note", 0)
                    note_w = grades.get("Wortschatz", {}).get("note", 0)
                    note_s = grades.get("Stil", {}).get("note", 0)
                    note_i = grades.get("Inhalt", {}).get("note", 0)

                    any_score_zero = 0 in [note_g, note_w, note_s, note_i]
                    gesamt_note = 0 if any_score_zero else (note_g + note_w + note_s + note_i)

                    kommentar = grades.get("Gesamtnote", {}).get("kommentar", "")
                    ergebnis = get_ergebnis(gesamt_note, any_score_zero)

                    grades["Gesamtnote"] = {
                        "note": gesamt_note,
                        "kommentar": kommentar,
                        "ergebnis": ergebnis
                    }

                except Exception:
                    grades["Gesamtnote"] = {
                        "note": 0,
                        "kommentar": "Fehler bei der Notenverarbeitung.",
                        "ergebnis": "❌ NICHT BESTANDEN"
                    }

        print(json.dumps(result_json, ensure_ascii=False, indent=2))

    except Exception:
        print(json.dumps({
            "error": "Python script crashed",
            "details": traceback.format_exc()
        }, ensure_ascii=False))

if __name__ == "__main__":
    main()